<?php 

namespace ZeroPHP\ZeroPHP;

use ZeroPHP\ZeroPHP\Entity;
use ZeroPHP\ZeroPHP\EntityInterface;

class Perms extends Entity implements  EntityInterface {
    public function __config() {
        return array(
            '#id' => 'perm_id',
            '#name' => 'perms',
            '#class' => 'ZeroPHP\ZeroPHP\Perms',
            '#title' => zerophp_lang('Permissions'),
            '#fields' => array(
                'perm_id' => array(
                    '#name' => 'perm_id',
                    '#title' => zerophp_lang('ID'),
                    '#type' => 'hidden',
                ),
                'path' => array(
                    '#name' => 'path',
                    '#title' => zerophp_lang('Path'),
                    '#type' => 'text',
                    '#validate' => 'required|max_length[255]',
                ),
                'access_key' => array(
                    '#name' => 'access_key',
                    '#title' => zerophp_lang('Access key'),
                    '#type' => 'textarea',
                    '#validate' => 'required',
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
                    '#validate' => 'required|numeric|between:0,1',
                ),
                'weight' => array(
                    '#name' => 'weight',
                    '#title' => zerophp_lang('Weight'),
                    '#type' => 'select',
                    '#options' => form_#options_make_weight(),
                    '#validate' => 'required|numeric|between:-99,99',
                    '#fast_edit' => 1,
                ),
            ),
        );
    }

    function loadEntity_from_path($path, $attributes = array()) {
        $cache_name = "Perms-loadEntity_from_path-" . md5($path);
        if ($cache = \Cache::get($cache_name)) {
            return $cache;
        }

        $attributes['load_all'] = false;
        $attributes['where']['path'] = $path;
        $result = reset($this->loadEntityExecutive(null, $attributes));

        \Cache::forever($cache_name, $result);
        return $result;
    }

    function loadEntityAll($attributes = array()) {
        if (!isset($attributes['order'])) {
            $attributes['order'] = array();
        }

        if (!isset($attributes['order']['path'])) {
            $attributes['order']['path'] = 'ASC';
        }

        if (!isset($attributes['order']['perm_id'])) {
            $attributes['order']['perm_id'] = 'DESC';
        }

        return parent::loadEntityAll($attributes, $pager_sum);
    }
}