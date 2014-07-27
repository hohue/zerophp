<?php 
namespace ZeroPHP\Category;

use ZeroPHP\ZeroPHP\Entity;

class Category extends Entity {
    function __construct() {
        $this->setStructure(array(
            'id' => 'category_id',
            'name' => 'category',
            'class' => 'ZeroPHP\Category\Category',
            'title' => zerophp_lang('Category'),
            'fields' => array(
                'category_id' => array(
                    'name' => 'category_id',
                    'title' => zerophp_lang('ID'),
                    'type' => 'hidden',
                ),
                'title' => array(
                    'name' => 'title',
                    'title' => zerophp_lang('Title'),
                    'type' => 'input',
                    'validate' => 'required',
                    'required' => true,
                ),
                'category_group_id' => array(
                    'name' => 'category_group_id',
                    'title' => zerophp_lang('Category group'),
                    'type' => 'select_build',
                    'reference' => 'category_group',
                    'reference_type' => 'internal',
                    'ajax' => array(
                        'path' => 'category/parent_get_from_group',
                        'wrapper' => 'fii_parent_content select',
                    ),
                ),
                'parent' => array(
                    'name' => 'parent',
                    'title' => zerophp_lang('Parent category'),
                    'type' => 'select_build',
                    'reference' => 'category',
                    'reference_type' => 'internal',
                    'reference_option' => array(
                        'library' => 'category',
                        'method' => 'parent_get_from_group',
                        'arguments' => array(
                            'group' => 0,
                            'load_children' => false,
                        ),
                    ),
                    'size' => 10,
                ),
                'weight' => array(
                    'name' => 'weight',
                    'title' => zerophp_lang('Weight'),
                    'type' => 'select_build',
                    'options' => form_options_make_weight(),
                    'default' => 0,
                    'validate' => 'required|numeric|greater_than[-100]|less_than[100]',
                    'fast_edit' => 1,
                ),
                'active' => array(
                    'name' => 'active',
                    'title' => zerophp_lang('Active'),
                    'type' => 'radios',
                    'options' => array(
                        1 => zerophp_lang('Enable'),
                        0 => zerophp_lang('Disable'),
                    ),
                    'default' => 1,
                    'validate' => 'required|numeric|greater_than[-1]|less_than[2]',
                ),
            ),
        ));
    }

    function parent_get_from_group($arguments = null) {
        $cache_name = __METHOD__ . serialize($arguments);
        if ($cache = \Cache::get($cache_name)) {
            return $cache;
        }

        $result = array();

        $group = !empty($arguments['group']) && is_numeric($arguments['group']) ? $arguments['group'] : 0;

        $empty = new stdClass();
        $empty->title = '---';
        $empty->category_id = 0;
        $result[0] = $empty;

        // @todo 9 Hack for parent
        $parent = array(
            3 => 1,
            4 => 3,
            5 => 2,
        );
        $group = isset($parent[$group]) ? $parent[$group] : $group;

        if ($group) {
            if (!isset($arguments['attributes'])) {
                $arguments['attributes'] = array();
            }
            $categories = $this->loadEntityAll_from_group($group, $arguments['attributes']);

            //fw_devel_print($categories);
            if (count($categories) > 1) {
                $categories = template_tree_build_option($categories, 0, 0, false);
            }
            $result = array_merge($result, $categories);
        }

        \Cache::get($cache_name, $result);
        return $result;
    }

    function loadEntityAll_from_group($group, $attributes = array()) {
        $cache_name = __METHOD__ . $group . serialize($attributes);
        if ($cache = \Cache::get($cache_name)) {
            return $cache;
        }

        $attributes['where']['category_group_id'] = $group;

        $entities = $this->loadEntityAll($attributes);

        foreach ($entities as $key => $value) {
            $entities[$key]->children_count = count($this->loadEntityAll_from_parent($key));
        }

        \Cache::forever($cache_name, $entities);
        return $entities;
    }

    function loadEntityAll_from_parent($parent, $attributes = array()) {
        $cache_name = __METHOD__ . $parent . serialize($attributes);
        if ($cache = \Cache::get($cache_name)) {
            return $cache;
        }

        $attributes['where']['parent'] = $parent;

        $entities = $this->loadEntityAll($attributes);

        foreach ($entities as $key => $value) {
            $entities[$key]->children_count = count($this->loadEntityAll_from_parent($key));
        }

        \Cache::forever($cache_name, $entities);
        return $entities;
    }

    function loadEntityExecutive($entity_id = 0, $attributes = array(), &$pager_sum = 1) {
        $cache_name = __METHOD__ . $entity_id . serialize($attributes);
        if ($cache = \Cache::get($cache_name)) {
            return $cache;
        }

        $result = parent::loadEntityExecutive($entity_id, $attributes, $pager_sum);

        \Cache::forever($cache_name, $result);
        return $result;
    }

    function loadEntityAll($attributes = array()) {
        if (!isset($attributes['order'])) {
            $attributes['order'] = array();
        }

        if (!isset($attributes['order']['weight'])) {
            $attributes['order']['weight'] = 'ASC';
        }

        if (!isset($attributes['order']['category_id'])) {
            $attributes['order']['category_id'] = 'ASC';
        }

        return parent::loadEntityAll($attributes, $pager_sum);
    }
}