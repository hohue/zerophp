<?php
namespace ZeroPHP\ZeroPHP;

use ZeroPHP\ZeroPHP\UrlAlias;
use ZeroPHP\ZeroPHP\Menu;

class Request {
    private $path_prefix = array(
        'modal',
        'ajax',
        'json',
        'esi',
        'admin',
        'up',
    );

    public $data = array(
        'alias' => '',
        'prefix' => '',
        'url' => '',
        'segment' => array(),
        'filter' => array(),
        'query' => array(),
    );

    public function __construct() {
        $this->_parseURI();
    }

    public function getController() {
        $menus = new Menu;
        $menus = $menus->loadEntityAll();
        $ancestors = zerophp_menu_ancestors($this->segment());
        
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
        $filter = \Request::query();

        // Get URL real from url alias
        $alias = new UrlAlias;
        $alias = $alias->loadEntityByAlias($uri);
        if (isset($alias->real)) {
            $this->data['alias'] = $uri;
            $uri = $alias->real;
        }

        if (strpos('?', $uri)) {
          $uri = explode('?', $uri);
          $filter = isset($uri[1]) ? $uri[1] + $filter : $filter;
          $uri = $uri[0];
        }
        $uri  = explode('/', strtolower($uri));

        // Get Prefix
        if (in_array($uri[0], $this->path_prefix)) {
            $this->data['prefix'] = array_shift($uri);
        }

        $this->data['segment'] = $uri;
        $this->data['segment'] = count($this->data['segment']) ? $this->data['segment'] : array('', '');

        $this->data['url'] = implode('/', $uri);
        $this->data['url'] = $this->data['url'] ? $this->data['url'] : '/';
        if (isset($filter['f'])) {
            $this->data['filter'] = $this->_parseFilter($filter['f']);
            unset($filter['f']);
        }

        $this->data['query'] = $this->_parseQuery($filter);
    }

    private function _parseFilter($filter) {
        $result = array();
        $filter = explode('|', $filter);
        if (count($filter)) {
            foreach ($filter as $value) {
                $value = explode('~', $value);
                if (isset($value[0]) && isset($value[1])) {
                    $result[zerophp_uri_validate($value[0])] = $value[1];
                }
            }
        }

        return $result;
    }

    private function _parseQuery($query) {
        foreach ($query as $key => $value) {
            if (is_string($value)) {
              $value = zerophp_uri_validate(trim($value, '/'));
            }
            $query[$key] = $value;
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
}