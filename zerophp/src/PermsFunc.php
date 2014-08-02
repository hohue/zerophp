<?php 
namespace ZeroPHP\ZeroPHP;

use ZeroPHP\ZeroPHP\Entity;
use ZeroPHP\ZeroPHP\EntityInterface;

class PermsFunc extends Entity implements  EntityInterface {
    public function __config() {
        return array(
            '#id' => 'perms_func_id',
            '#name' => 'perms_func',
            '#class' => 'ZeroPHP\ZeroPHP\PermsFunc',
            '#title' => zerophp_lang('Permissions function'),
            '#fields' => array(
                'perm_id' => array(
                    '#name' => 'perms_func_id',
                    '#title' => zerophp_lang('ID'),
                    '#type' => 'hidden',
                ),
                'access_key' => array(
                    '#name' => 'access_key',
                    '#title' => zerophp_lang('Access key'),
                    '#type' => 'textarea',
                    '#validate' => 'required',
                ),
                'library' => array(
                    '#name' => 'library',
                    '#title' => zerophp_lang('Class'),
                    '#type' => 'text',
                ),
                'method' => array(
                    '#name' => 'function',
                    '#title' => zerophp_lang('Method'),
                    '#type' => 'text',
                    '#display_hidden' => 1,
                ),
                'active' => array(
                    '#name' => 'active',
                    '#title' => zerophp_lang('Active'),
                    '#type' => 'radios',
                    '#options' => array(
                        1 => zerophp_lang('Enable'),
                        0 => zerophp_lang('Disable'),
                    ),
                    '#default' => 1,
                    '#validate' => 'required|numeric|greater_than[-1]|less_than[2]',
                ),
            ),
        );
    }

    function loadEntity_from_access_key($access_key, $attributes = array()) {
        $cache_name = "Perms_func-loadEntity_from_access_key-$access_key";
        if ($cache = \Cache::get($cache_name)) {
            return $cache;
        }

        $attributes['load_all'] = false;
        $attributes['where']['access_key'] = $access_key;
        $result = reset($this->loadEntityExecutive(null, $attributes));

        \Cache::forever($cache_name, $result);
        return $result;
    }
}