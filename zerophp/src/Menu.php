<?php 
namespace ZeroPHP\ZeroPHP;

use ZeroPHP\ZeroPHP\Entity;
use ZeroPHP\ZeroPHP\EntityInterface;

class Menu extends Entity implements  EntityInterface {
    public function __config() {
        return array(
            '#id' => 'menu_id',
            '#name' => 'menu',
            '#class' => '\ZeroPHP\ZeroPHP\Menu',
            '#title' => zerophp_lang('Menu'),
            '#fields' => array(
                'menu_id' => array(
                    '#name' => 'menu_id',
                    '#title' => zerophp_lang('ID'),
                    '#type' => 'hidden',
                ),
                'title' => array(
                    '#name' => 'title',
                    '#title' => zerophp_lang('Title'),
                    '#type' => 'text',
                ),
                'cache' => array(
                    '#name' => 'cache_type',
                    '#title' => zerophp_lang('Cache'),
                    '#type' => 'text',
                ),
                'path' => array(
                    '#name' => 'path',
                    '#title' => zerophp_lang('Path'),
                    '#type' => 'text',
                    '#validate' => 'required',
                ),
                'class' => array(
                    '#name' => 'class',
                    '#title' => zerophp_lang('Class'),
                    '#type' => 'text',
                    '#list_hidden' => 1,
                ),
                'method' => array(
                    '#name' => 'method',
                    '#title' => zerophp_lang('Method'),
                    '#type' => 'text',
                    '#list_hidden' => 1,
                ),
                'arguments' => array(
                    '#name' => 'arguments',
                    '#title' => zerophp_lang('Arguments'),
                    '#type' => 'text',
                    '#list_hidden' => 1,
                ),
                'access' => array(
                    '#name' => 'access',
                    '#title' => zerophp_lang('Access'),
                    '#type' => 'textarea',
                    '#list_hidden' => 1,
                ),
                'weight' => array(
                    '#name' => 'weight',
                    '#title' => zerophp_lang('Weight'),
                    '#type' => 'select',
                    '#options' => form_options_make_weight(),
                    '#validate' => 'required|numeric|between:-99,99',
                    '#fast_edit' => 1,
                ),
                'active' => array(
                    '#name' => 'active',
                    '#title' => zerophp_lang('Active'),
                    '#type' => 'radios',
                    '#options' => array(
                        1 => zerophp_lang('Enable'),
                        0 => zerophp_lang('Disable'),
                    ),
                    '#validate' => 'required|numeric|between:0,1'
                ),
            ),
        );
    }

    function loadEntityAll($attributes = array()) {
        if ($cache = \Cache::get(__METHOD__)) {
            return $cache;
        }

        $menus = parent::loadEntityAll();
        $result = array();
        foreach ($menus as $value) {
            $result[$value->path] = $value;
        }

        \Cache::forever(__METHOD__, $result);
        return $result;
    }

    function loadEntityByPath($path) {
        $cache_name = __METHOD__ . md5($path);
        if ($cache = \Cache::get($cache_name)) {
            return $cache;
        }

        $attributes = array(
            'where' => array(
                'path' => $path,
            )
        );
        $menu = $this->loadEntityExecutive(null, $attributes);

        if (count($menu)) {
            $menu = reset($menu);
        }

        \Cache::forever($cache_name, $menu);
        return $menu;
    }
}