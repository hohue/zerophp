<?php
namespace ZeroPHP\ZeroPHP;

use ZeroPHP\ZeroPHP\Entity;
use ZeroPHP\ZeroPHP\EntityInterface;

class Hook extends Entity implements  EntityInterface {
    public function __config() {
        return array(
            '#id' => 'hook_id',
            '#name' => 'hook',
            '#class' => 'ZeroPHP\ZeroPHP\Hook',
            '#title' => zerophp_lang('Hook'),
            '#fields' => array(
                'hook_id' => array(
                    '#name' => 'hook_id',
                    '#title' => zerophp_lang('ID'),
                    '#type' => 'hidden',
                ),
                'title' => array(
                    '#name' => 'title',
                    '#title' => zerophp_lang('Title'),
                    '#type' => 'text',
                    '#validate' => 'required',
                ),
                'hook_type' => array(
                    '#name' => 'hook_type',
                    '#title' => zerophp_lang('Hook type'),
                    '#type' => 'radios',
                    '#options' => $this->hook_types(),
                    '#validate' => 'required',
                ),
                'hook_condition' => array(
                    '#name' => 'hook_condition',
                    '#title' => zerophp_lang('Hook Condition'),
                    '#type' => 'text',
                ),
                'class' => array(
                    '#name' => 'class',
                    '#title' => zerophp_lang('Class'),
                    '#type' => 'text',
                ),
                'method' => array(
                    '#name' => 'method',
                    '#title' => zerophp_lang('Method'),
                    '#type' => 'text',
                    '#display_hidden' => 1,
                ),
                'weight' => array(
                    '#name' => 'weight',
                    '#title' => zerophp_lang('Weight'),
                    '#type' => 'select',
                    '#options' => form_options_make_weight(),
                    '#validate' => 'required|numeric|between:-999,999',
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
                    '#validate' => 'required|numeric|greater_than[-1]|less_than[2]'
                ),
            ),
        );
    }

    function loadEntityAll($attributes = array()) {
        $cache_name = __METHOD__ . serialize($attributes);

        if ($cache = \Cache::get($cache_name)) {
            return $cache;
        }

        if (!isset($attributes['order'])) {
            $attributes['order'] = array();
        }

        if (!isset($attributes['order']['hook_type'])) {
            $attributes['order']['hook_type'] = 'ASC';
        }

        if (!isset($attributes['order']['hook_condition'])) {
            $attributes['order']['hook_condition'] = 'ASC';
        }

        if (!isset($attributes['order']['class'])) {
            $attributes['order']['class'] = 'ASC';
        }

        if (!isset($attributes['order']['method'])) {
            $attributes['order']['method'] = 'ASC';
        }

        $hooks = parent::loadEntityAll($attributes);

        $result = array();
        foreach ($hooks as $value) {
            if (empty($value->hook_condition)) {
                $value->hook_condition = '#all';
            }
            $result[$value->hook_type][$value->hook_condition][] = $value;
        }

        \Cache::forever($cache_name, $result);
        return $result;
    }

    function loadEntityAllByHookType($hook_type, $hook_condition = '#all') {
        $hooks = $this->loadEntityAll();

        if (isset($hooks[$hook_type]) && isset($hooks[$hook_type][$hook_condition])) {
            return $hooks[$hook_type][$hook_condition];
        }

        return array();
    }







    // Define hook type
    private function hook_types() {
        return array(
            'init' => 'init', //Call at Hook_general->hook_general_post_controller_constructor
            'entity_structure_alter' => 'entity_structure_alter', //Call at Entity->structure_set
            'entity_create_submit' => 'entity_create_submit', // Call at Entity->crud_create_form_submit
            'form_alter' => 'form_alter', //Call at Form->form_build
            'form_value_alter' => 'form_value_alter', //Cal at Form->form_build
        );
    }

    function run($hooks, $arguments) {
        foreach ($hooks as $hook) {
            $entity = new $hook['class'];;
            $this->CI->{$hook['library']}->{$hook['function']}($arguments);
        }
    }
}