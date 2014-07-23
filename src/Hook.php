<?php
namespace ZeroPHP\ZeroPHP;

use ZeroPHP\ZeroPHP\Entity;

class Hook extends Entity {

    function __construct() {
        parent::__construct();

        

        $this->setStructure(array(
            'id' => 'hook_id',
            'name' => 'hook',
            'title' => zerophp_lang('Hook'),
            'fields' => array(
                'hook_id' => array(
                    'name' => 'hook_id',
                    'title' => zerophp_lang('ID'),
                    'type' => 'hidden',
                ),
                'title' => array(
                    'name' => 'title',
                    'title' => zerophp_lang('Title'),
                    'type' => 'input',
                    'validate' => 'required',
                ),
                'hook_type' => array(
                    'name' => 'hook_type',
                    'title' => zerophp_lang('Hook type'),
                    'type' => 'radio_build',
                    'options' => $this->hook_types(),
                    'validate' => 'required',
                ),
                'hook_condition' => array(
                    'name' => 'hook_condition',
                    'title' => zerophp_lang('Hook Condition'),
                    'type' => 'input',
                ),
                'library' => array(
                    'name' => 'library',
                    'title' => zerophp_lang('Class'),
                    'type' => 'input',
                ),
                'function' => array(
                    'name' => 'function',
                    'title' => zerophp_lang('Method'),
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

    function entity_load_all($attributes = array(), &$pager_sum = 0) {
        if (!isset($attributes['order'])) {
            $attributes['order'] = array();
        }

        if (!isset($attributes['order']['hook_type'])) {
            $attributes['order']['hook_type'] = 'ASC';
        }

        if (!isset($attributes['order']['library'])) {
            $attributes['order']['library'] = 'ASC';
        }

        if (!isset($attributes['order']['hook_condition'])) {
            $attributes['order']['hook_condition'] = 'ASC';
        }

        return parent::entity_load_all($attributes, $pager_sum);
    }

    function hook_get_all($hook_type, $hook_condition = '#all') {
        $cache = \Cache::get("Hook-hook_get_all");

        if (empty($cache)) {
            $entity = Entity::loadEntityObject('hook');
            $hooks = $this->CI->hook->entity_load_all();
            $cache = array();
            foreach ($hooks as $hook) {
                if (empty($hook->hook_condition)) {
                    $hook->hook_condition = '#all';
                }
                $cache[$hook->hook_condition][$hook->hook_type][] = array(
                    'library' => $hook->library,
                    'function' => $hook->function
                );
            }

            \Cache::forever("Hook-hook_get_all", $cache);
        }

        return !empty($cache[$hook_condition][$hook_type]) ? $cache[$hook_condition][$hook_type] : array();
    }

    function run($hooks, $arguments) {
        foreach ($hooks as $hook) {
            $entity = Entity::loadEntityObject($hook['library']);
            $this->CI->{$hook['library']}->{$hook['function']}($arguments);
        }
    }
}