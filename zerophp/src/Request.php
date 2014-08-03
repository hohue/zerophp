<?php
namespace ZeroPHP\ZeroPHP;

class Request {
    private $path_prefix = array(
        'ajax',
        'json',
        'esi',
        'admin',
        'up',
    );

    public $data = array(
        'alias' => '',
        'prefix' => 'normal',
        'url' => '',
        'segment' => array(),
        'filter' => array(),
        'query' => array(),
    );

    public function __construct() {
        $this->_parseURI();
    }

    //@todo 3 Get controller from DB
    public function getController() {
        $menus = new \ZeroPHP\ZeroPHP\Menu;
        $menus = $menus->loadEntityAll();
        $ancestors = $this->_getMenuAncestors($this->segment());
        
        foreach ($ancestors as $value) {
            if (isset($menus[$value]) && isset($menus[$value]->class) && isset($menus[$value]->method)) {
                return $menus[$value];
            }
        }

        \App::abort(404);
    }

    public function addFilter($key, $value) {
        return $this->data['filter'][$key] = $value;
    }

    public function url() {
        return $this->data['url'];
    }

    public function prefix() {
        return $this->data['prefix'];
    }

    public function segment($index = 'all') {
        return $this->_getDataIndex('segment', $index);
    }

    public function filter($index = 'all') {
        return $this->_getDataIndex('filter', $index);
    }

    public function query($index = 'all') {
        return $this->_getDataIndex('query', $index);
    }

    private function _parseURI() {
        $uri = \Request::path();

        // Get URL real from url alias
        $alias = new \ZeroPHP\ZeroPHP\UrlAlias;
        $alias = $alias->loadEntityByAlias($uri);
        if (isset($alias->url_real)) {
            $this->data['alias'] = $uri;
            $uri = $alias->url_real;
        }

        $uri  = explode('/', strtolower($uri));

        // Get Prefix
        if (in_array($uri[0], $this->path_prefix)) {
            $this->data['prefix'] = array_shift($uri);
        }

        $this->data['segment'] = $uri;
        $this->data['url'] = implode('/', $uri);
        $this->data['url'] = $this->data['url'] ? $this->data['url'] : '/';

        $filter = \Request::query();
        if (isset($filter['f'])) {
            $this->data['filter'] = $this->_parseFilter($filter['f']);
            unset($filter['f']);
        }

        $this->data['query'] = $this->_parseQuery($filter);
    }

    private function _parseFilter($filter) {
        $result = array();
        $filter = explode('-', $filter);
        if (count($filter)) {
            foreach ($filter as $value) {
                $value = explode('.', $value);
                if (isset($value[0]) && isset($value[1])) {
                    $result[zerophp_uri_validate($value[0])] = zerophp_uri_validate($value[1]);
                }
            }
        }

        return $result;
    }

    private function _parseQuery($query) {
        foreach ($query as $key => $value) {
            $query[$key] = zerophp_uri_validate(trim($value, '/'));
        }
        return $query;
    }

    private function _getDataIndex($type, $index = 'all') {
        if ($index == 'all') {
            return $this->data[$type];
        }
        elseif (isset($this->data[$type][$index])) {
            return $this->data[$type][$index];
        }

        return false;
    }

    /**
     * From Drupal 7
     *
     * Returns the ancestors (and relevant placeholders) for any given path.
     *
     * For example, the ancestors of node/12345/edit are:
     * - node/12345/edit
     * - node/12345/%
     * - node/%/edit
     * - node/%/%
     * - node/12345
     * - node/%
     * - node
     *
     * To generate these, we will use binary numbers. Each bit represents a
     * part of the path. If the bit is 1, then it represents the original
     * value while 0 means wildcard. If the path is node/12/edit/foo
     * then the 1011 bitstring represents node/%/edit/foo where % means that
     * any argument matches that part. We limit ourselves to using binary
     * numbers that correspond the patterns of wildcards of router items that
     * actually exists. This list of 'masks' is built in menu_rebuild().
     *
     * @param $parts
     *   An array of path parts; for the above example, 
     *   array('node', '12345', 'edit').
     *
     * @return
     *   An array which contains the ancestors and placeholders. Placeholders
     *   simply contain as many '%s' as the ancestors.
     */
    function _getMenuAncestors($parts) {
      $number_parts = count($parts);
      $ancestors = array();
      $length =  $number_parts - 1;
      $end = (1 << $number_parts) - 1;
      //$masks = variable_get('menu_masks');
      // If the optimized menu_masks array is not available use brute force to get
      // the correct $ancestors and $placeholders returned. Do not use this as the
      // default value of the menu_masks variable to avoid building such a big
      // array.
      //if (!$masks) {
        $masks = range(511, 1);
      //}
      // Only examine patterns that actually exist as router items (the masks).
      foreach ($masks as $i) {
        if ($i > $end) {
          // Only look at masks that are not longer than the path of interest.
          continue;
        }
        elseif ($i < (1 << $length)) {
          // We have exhausted the masks of a given length, so decrease the length.
          --$length;
        }
        $current = '';
        for ($j = $length; $j >= 0; $j--) {
          // Check the bit on the $j offset.
          if ($i & (1 << $j)) {
            // Bit one means the original value.
            $current .= $parts[$length - $j];
          }
          else {
            // Bit zero means means wildcard.
            $current .= '%';
          }
          // Unless we are at offset 0, add a slash.
          if ($j) {
            $current .= '/';
          }
        }
        $ancestors[] = $current;
      }
      return $ancestors;
    }
}