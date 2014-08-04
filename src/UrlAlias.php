<?php 

namespace ZeroPHP\ZeroPHP;

use ZeroPHP\ZeroPHP\Entity;
use ZeroPHP\ZeroPHP\EntityInterface;

//@todo 9 Add URL alias when use Entity::saveEntity();
// Write re-build feature for alias
// Add alias & real folder to .gitignore

class UrlAlias extends Entity implements  EntityInterface {
    public function __config() {
        return array(
            '#id' => 'urlalias_id',
            '#name' => 'urlalias',
            '#title' => zerophp_lang('URL alias'),
            '#fields' => array(
                'urlalias_id' => array(
                    '#name' => 'urlalias_id',
                    '#title' => zerophp_lang('ID'),
                    '#type' => 'hidden',
                ),
                'real' => array(
                    '#name' => 'real',
                    '#title' => zerophp_lang('URL real'),
                    '#type' => 'text',
                ),
                'alias' => array(
                    '#name' => 'alias',
                    '#title' => zerophp_lang('URL alias'),
                    '#type' => 'text',
                ),
                'created_at' => array(
                    '#name' => 'created_at',
                    '#title' => zerophp_lang('Created At'),
                    '#type' => 'text',
                    '#widget' => 'date_timestamp',
                    '#form_hidden' => 1,
                ),
                'updated_at' => array(
                    '#name' => 'updated_at',
                    '#title' => zerophp_lang('Updated At'),
                    '#type' => 'text',
                    '#widget' => 'date_timestamp',
                    '#form_hidden' => 1,
                ),
            ),
        );
    }

    function loadEntityAll($attributes = array()) {
        if (!isset($attributes['order'])) {
            $attributes['order'] = array();
        }

        if (!isset($attributes['order']['updated_at'])) {
            $attributes['order']['updated_at'] = 'DESC';
        }

        return parent::loadEntityAll($attributes);
    }

    function loadEntityByAlias($path) {
        $attributes = array(
            'where' => array(
                'alias' => $path,
            )
        );
        $alias = $this->loadEntityExecutive(null, $attributes);

        if (count($alias)) {
            return reset($alias);
        }

        return false;
    }

    function loadEntityByReal($path) {
        $attributes = array(
            'where' => array(
                'real' => $path,
            )
        );
        $real = $this->loadEntityExecutive(null, $attributes);

        if (count($real)) {
            return reset($real);
        }

        return false;
    }

    function verify($real, $alias, $validate = true) {
        // Remove unexpected characters
        $alias = $validate ? zerophp_uri_validate($alias) : $alias;

        // Check url alias exists
        $alias = $this->loadEntityByAlias($alias);
        if (!empty($alias->alias) && $alias->real != $real) {
            $alias .= '-' . strtolower(str_random(4));

            // Re-check with new url alias
            $alias = $this->verify($real, $alias, false);
        }

        return $alias;
    }






    function alias_create($real, $alias, $prefix = '') {
        $prefix = $prefix ? $prefix : fw_variable_get('url alias all prefix', '');

        // Remove prefix
        $old_prefix = strpos($alias, $prefix . '/');
        if ($old_prefix === 0) {
            $alias = substr($alias, $old_prefix + strlen($prefix) + 1);
        }

        $alias = $prefix . '/' . $alias;
        $alias = $this->alias_verify_alias($real, $alias);

        // Save to db
        $this->CI->load->model('alias_model');
        $alias = $this->CI->alias_model->get_from_real($real);
        if (!empty($alias->alias)) {
            // Update a new url alias for old url real
            if ($alias->alias != $alias) {
                @$this->CI->cachef->del_alias($alias->alias);
                $this->CI->cachef->set_alias($alias, $real);
                $this->CI->cachef->set_real($real, $alias);

                $alias->alias = $alias;
                $this->saveEntity($alias);
            }
        }
        // Save a new url alias for new url real
        else {

            $this->CI->cachef->set_alias($alias, $real);
            $this->CI->cachef->set_real($real, $alias);

            $alias->real = $real;
            $alias->alias = $alias;
            $this->saveEntity($alias);
        }
    }

    function crud_create_form_submit($form_id, $form, &$form_values, $message = '') {
        $new = true;
        // Create url alias cache file
        if (!empty($form_values['alias_id'])) {
            $url = $this->loadEntity($form_values['alias_id'], array('cache' => false));
            @$this->CI->cachef->del_alias($url->alias);
            @$this->CI->cachef->del_real($url->real);
            $new = false;
        }

        $this->alias_create($form_values['real'], $form_values['alias']);

        $message = $message ? $message : zerophp_lang('Your data was updated successfully.');
        zerophp_get_instance()->response->addMessage($message, 'success');

        $this->crud_create_form_submit_hook();
    }

    function crud_delete_form_submit($form_id, $form, &$form_values, $message = '') {
        // Delete url alias cache file
        foreach ($form_values['#delete'] as $url_id) {
            $url = $this->loadEntity($url_id, array('cache' => false));
            @$this->CI->cachef->del_alias($url->alias);
            @$this->CI->cachef->del_real($url->real);
        }

        parent::crud_delete_form_submit($form_id, $form, $form_values, $message);
    }

    function alias_form_alter($form_id, &$form) {
        if (!in_array($form_id, fw_variable_get('url alias form alter support', array()))) {
            return;
        }

        //@todo 9 Chuyen hidden field thanh text field cho phep user tu nhap alias
        // Dieu nay phai phan quyen, boi vi nguoi ban hang co the khong duoc phep nhap url alias
        $form['alias'] = array(
            '#name' => 'alias',
            '#type' => 'text',
            '#label' => zerophp_lang('URL alias'),
            '#item' => array(
                '#type' => 'text',
                '#name' => 'alias',
            ),
            '#description' => zerophp_lang('Example:') . ' ' . zerophp_lang('female-fashion'),
        );

        if (substr($form_id, 0, 19) == 'entity_crud_update_') {
            $form['alias']['#disabled'] = 'disabled';
            $form['alias']['#item']['disabled'] = 'disabled';
        }

        $form['#submit'][] = array(
            'class' => 'alias',
            'method' => 'alias_form_alter_submit',
        );
    }

    function alias_form_alter_submit($form_id, $form, &$form_values) {
        if(!empty($form_values['alias']) && !empty($form_values['entity_name'])) {
            $entity = new $form_values['entity_name'];
            $structure = $this->CI->{$form_values['entity_name']}->getStructure();

            $real = 'e/read/' . $form_values['entity_name'] . '/' . $form_values[$structure['#id']];
            $prefix = fw_variable_get("url alias entity " . $form_values['entity_name'] . " prefix", '');
            $this->alias_create($real, $form_values['alias'], $prefix);
        }
    }

    function alias_form_value_alter($form_id, $form, &$form_values) {
        if (!in_array($form_id, fw_variable_get('url alias form alter support', array()))) {
            return;
        }

        if(empty($form['entity_name']['#item']['entity_name'])) {
            return;
        }

        $entity_name = $form['entity_name']['#item']['entity_name'];
        $entity = new $entity_name;
        $structure = $this->CI->{$entity_name}->getStructure();

        if (!empty($form_values[$structure['#id']])) {
            $alias = $this->CI->cachef->get_real("e/read/$entity_name/" . $form_values[$structure['#id']]);
            $form_values['alias'] = $alias ? $alias : ' ';
        }
    }
}