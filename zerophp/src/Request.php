<?php
namespace ZeroPHP\ZeroPHP;

use ZeroPHP\ZeroPHP\Entity;

class Request {
    private $path_prefix = array(
        'ajax',
        'json',
        'esi',
        'admin',
        'up',
    );

    public $data = array(
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
        $menu = Entity::loadEntityObject('ZeroPHP\ZeroPHP\Menu');

        zerophp_devel_print($this->url());

        return array(
            'class' => 'DashboardController',
            'method' => 'index',
        );
    }

    public function url() {
        return $this->data['url'];
    }

    public function prefix() {
        return $this->data['prefix'];
    }

    public function segment($index = null) {
        return $this->_getDataIndex('segment', $index);
    }

    public function filter($index = null) {
        return $this->_getDataIndex('filter', $index);
    }

    public function query($index = null) {
        return $this->_getDataIndex('query', $index);
    }

    private function _parseURI() {
        $uri  = explode('/', strtolower(\Request::path()));

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
                    $result[$value[0]] = $value[1];
                }
            }
        }

        return $result;
    }

    private function _parseQuery($query) {
        foreach ($query as $key => $value) {
            $query[$key] = trim($value, '/');
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