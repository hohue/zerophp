<?php 
namespace ZeroPHP\Category;

use ZeroPHP\ZeroPHP\Entity;
use ZeroPHP\ZeroPHP\EntityInterface;

class CategoryGroup extends Entity implements EntityInterface {
    function __config() {
        return array(
            '#id' => 'category_group_id',
            '#name' => 'category_group',
            '#class' => 'ZeroPHP\Category\CategoryGroup',
            '#title' => zerophp_lang('Category group'),
            '#fields' => array(
                'category_group_id' => array(
                    '#name' => 'category_group_id',
                    '#title' => zerophp_lang('ID'),
                    '#type' => 'hidden',
                ),
                'title' => array(
                    '#name' => 'title',
                    '#title' => zerophp_lang('Title'),
                    '#type' => 'text',
                    '#validate' => 'required',
                    '#required' => true,
                ),
                'weight' => array(
                    '#name' => 'weight',
                    '#title' => zerophp_lang('Weight'),
                    '#type' => 'select',
                    '#options' => form_options_make_weight(),
                    '#default' => 0,
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
                    '#default' => 1,
                    '#validate' => 'required|numeric|between:0,1',
                ),
            ),
        );
    }



    

    function loadEntityExecutive($entity_id = 0, $attributes = array(), &$pager_sum = 1) {
        $cache_name = "Category_group-loadEntityExecutive-$entity_id" . serialize($attributes);
        if ($cache = \Cache::get($cache_name)) {
            return $cache;
       }

        $result = parent::loadEntityExecutive($entity_id, $attributes, $pager_sum);

        \Cache::forever($cache_name, $result);
        return $result;
    }
}