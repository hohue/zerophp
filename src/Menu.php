<?php 
namespace ZeroPHP\ZeroPHP;

use ZeroPHP\ZeroPHP\Entity;

class Menu extends Entity {

    function __construct() {
        $this->setStructure(array(
            'id' => 'block_id',
            'name' => 'block',
            'title' => zerophp_lang('Block'),
            'fields' => array(
                'block_id' => array(
                    'name' => 'block_id',
                    'title' => zerophp_lang('ID'),
                    'type' => 'hidden',
                ),
                'title' => array(
                    'name' => 'title',
                    'title' => zerophp_lang('Title'),
                    'type' => 'input',
                ),
                'cache' => array(
                    'name' => 'cache_type',
                    'title' => zerophp_lang('Cache'),
                    'type' => 'input',
                ),
                'path' => array(
                    'name' => 'path',
                    'title' => zerophp_lang('Path'),
                    'type' => 'input',
                    'validate' => 'required',
                ),
                'class' => array(
                    'name' => 'class',
                    'title' => zerophp_lang('Class'),
                    'type' => 'input',
                    'display_hidden' => 1,
                ),
                'method' => array(
                    'name' => 'method',
                    'title' => zerophp_lang('Method'),
                    'type' => 'input',
                    'display_hidden' => 1,
                ),
                'access' => array(
                    'name' => 'access',
                    'title' => zerophp_lang('Access'),
                    'type' => 'input',
                    'display_hidden' => 1,
                ),
                'weight' => array(
                    'name' => 'weight',
                    'title' => zerophp_lang('Weight'),
                    'type' => 'dropdown_build',
                    'options' => form_options_make_weight(),
                    'validate' => 'required|numeric|greater_than[-100]|less_than[100]',
                    'fast_edit' => 1,
                ),
                'active' => array(
                    'name' => 'active',
                    'title' => zerophp_lang('Active'),
                    'type' => 'radio_build',
                    'options' => array(
                        1 => zerophp_lang('Enable'),
                        0 => zerophp_lang('Disable'),
                    ),
                    'validate' => 'required|numeric|greater_than[-1]|less_than[2]'
                ),
            ),
        ));
    }

    function loadEntityAll($attributes = array()) {
        if ($cache = \Cache::get(__METHOD__)) {
            return $cache;
        }

        $menus = parent::loadEntityAll();

        \Cache::forever(__METHOD__, $menus);
        return $menus;
    }
}