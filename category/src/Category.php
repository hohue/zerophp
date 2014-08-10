<?php 
namespace ZeroPHP\Category;

use ZeroPHP\ZeroPHP\EntityInterface;
use ZeroPHP\ZeroPHP\Entity;

class Category extends Entity implements EntityInterface {
    public function __config() {
        return array(
            '#id' => 'category_id',
            '#name' => 'category',
            '#class' => '\ZeroPHP\Category\Category',
            '#title' => zerophp_lang('Category'),
            '#fields' => array(
                'category_id' => array(
                    '#name' => 'category_id',
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
                'category_group_id' => array(
                    '#name' => 'category_group_id',
                    '#title' => zerophp_lang('Category group'),
                    '#type' => 'select',
                    '#reference' => array(
                        'name' => 'category_group',
                        'class' => '\ZeroPHP\Category\CategoryGroup',
                    ),
                    /*'#ajax' => array(
                        'path' => 'category/parent_get_from_group',
                        'wrapper' => 'fii_parent_content select',
                    ),*/
                ),
                'parent' => array(
                    '#name' => 'parent',
                    '#title' => zerophp_lang('Parent category'),
                    '#type' => 'select',
                    '#reference' => array(
                        'name' => 'category',
                        'class' => '\ZeroPHP\Category\Category',
                        /*'options' => array(
                            'class' => 'category',
                            'method' => 'parent_get_from_group',
                            'arguments' => array(
                                'group' => 0,
                                'load_children' => false,
                            ),
                        ),*/
                    ),
                    '#attributes' => array(
                        'size' => 10,
                    ),
                ),
                'weight' => array(
                    '#name' => 'weight',
                    '#title' => zerophp_lang('Weight'),
                    '#type' => 'select',
                    '#options' => form_options_make_weight(),
                    '#default' => 0,
                    '#validate' => 'required|numeric|between:-100,100',
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

    public function loadEntityExecutive($entity_id = 0, $attributes = array()) {
        $cache_name = __METHOD__ . $entity_id . serialize($attributes);
        if ($cache = \Cache::get($cache_name)) {
            return $cache;
        }

        $result = parent::loadEntityExecutive($entity_id, $attributes);

        \Cache::forever($cache_name, $result);
        return $result;
    }

    public function loadEntityAll($attributes = array()) {
        if (!isset($attributes['order'])) {
            $attributes['order'] = array();
        }

        if (!isset($attributes['order']['weight'])) {
            $attributes['order']['weight'] = 'ASC';
        }

        if (!isset($attributes['order']['title'])) {
            $attributes['order']['title'] = 'ASC';
        }

        return parent::loadEntityAll($attributes);
    }

    public function loadEntityAllByGroup($group, $parent = false, $attributes = array()) {
        $cache_name = __METHOD__ . $group . $parent . serialize($attributes);
        if ($cache = \Cache::get($cache_name)) {
            return $cache;
        }

        $attributes['where']['category_group_id'] = $group;

        if ($parent || $parent === 0) {
            $attributes['where']['parent'] = $parent;
        }

        $entities = $this->loadEntityAll($attributes);

        \Cache::forever($cache_name, $entities);
        return $entities;
    }

    public function loadEntityAllByParent($parent, $attributes = array()) {
        $cache_name = __METHOD__ . $parent . serialize($attributes);
        if ($cache = \Cache::get($cache_name)) {
            return $cache;
        }

        $attributes['where']['parent'] = $parent;

        $entities = $this->loadEntityAll($attributes);

        \Cache::forever($cache_name, $entities);
        return $entities;
    }

    public function loadOptions($category_group_id, $parent = false, $select_text = '--- Select ---') {
        $cache_name = __METHOD__ . $category_group_id . $parent . $select_text;
        if ($cache = \Cache::get($cache_name)) {
            return $cache;
        }

        $result = array(
            '' => zerophp_lang($select_text),
        );

        $categories = $this->loadEntityAllByGroup($category_group_id, $parent);
        foreach ($categories as $value) {
            $result[$value->category_id] = $value->title;
        }

        \Cache::forever($cache_name, $result);
        return $result;
    }
}

// Checked