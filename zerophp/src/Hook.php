<?php
namespace ZeroPHP\ZeroPHP;

use ZeroPHP\ZeroPHP\Entity;
use ZeroPHP\ZeroPHP\EntityInterface;

class Hook extends Entity implements  EntityInterface {
    public function __config() {
        return array(
            '#id' => 'hook_id',
            '#name' => 'hook',
            '#class' => '\ZeroPHP\ZeroPHP\Hook',
            '#title' => zerophp_lang('Hook'),
            '#order' => array(
                'weight' => 'asc',
                'id' => 'asc',
            ),
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
                    '#type' => 'select',
                    '#options' => $this->getTypes(),
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

    // Define hook types
    private function getTypes() {
        return array(
            'entity_structure_alter' => 'entity_structure_alter', //Alter a entity structure
            'form_alter' => 'form_alter', //Alter a form
            'form_value_alter' => 'form_value_alter', //Alter values of a form
            'init' => 'init', // Call at initial application
        );
    }

    public function loadEntityAll($attributes = array()) {
        $cache_name = __METHOD__ . serialize($attributes);

        if ($cache = \Cache::get($cache_name)) {
            return $cache;
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

    public function loadEntityAllByHookType($hook_type, $hook_condition = '') {
        $hooks = $this->loadEntityAll();

        $result = array();

        if (isset($hooks[$hook_type])) {
            if (isset($hooks[$hook_type]['#all'])) {
                $result = array_merge($result, $hooks[$hook_type]['#all']);
            }

            if (isset($hooks[$hook_type][$hook_condition])) {
                $result = array_merge($result, $hooks[$hook_type][$hook_condition]);
            }
        }

        return $result;
    }

    public function run($hooks) {
        $arguments = func_get_args();
        unset($arguments[0]);
        foreach ($hooks as $hook) {
            call_user_func_array(array(new $hook['class'], $hook['method']), $arguments);
        }
    }
}

// Checked