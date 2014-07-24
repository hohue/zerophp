<?php 

namespace ZeroPHP\ZeroPHP;

use ZeroPHP\ZeroPHP\Entity;

//@todo 9 Add URL alias when use Entity::entity_save();
// Write re-build feature for url_alias
// Add url_alias & url_real folder to .gitignore

class UrlAlias extends Entity {
    function __construct() {
        $this->setStructure(array(
            'id' => 'urlalias_id',
            'name' => 'urlalias',
            'title' => zerophp_lang('URL alias'),
            'fields' => array(
                'urlalias_id' => array(
                    'name' => 'urlalias_id',
                    'title' => zerophp_lang('ID'),
                    'type' => 'hidden',
                ),
                'url_real' => array(
                    'name' => 'url_real',
                    'title' => zerophp_lang('URL real'),
                    'type' => 'input',
                ),
                'url_alias' => array(
                    'name' => 'url_alias',
                    'title' => zerophp_lang('URL alias'),
                    'type' => 'input',
                ),
                'created_at' => array(
                    'name' => 'created_at',
                    'title' => zerophp_lang('Created At'),
                    'type' => 'input',
                    'widget' => 'date_timestamp',
                    'form_hidden' => 1,
                ),
                'updated_at' => array(
                    'name' => 'updated_at',
                    'title' => zerophp_lang('Updated At'),
                    'type' => 'input',
                    'widget' => 'date_timestamp',
                    'form_hidden' => 1,
                ),
            ),
        ));
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
                'url_alias' => $path,
            )
        );
        $url_alias = $this->loadEntityExecutive(null, $attributes);

        if (count($url_alias)) {
            return reset($url_alias);
        }

        return false;
    }

    function loadEntityByReal($path) {
        $attributes = array(
            'where' => array(
                'url_real' => $path,
            )
        );
        $url_real = $this->loadEntityExecutive(null, $attributes);

        if (count($url_real)) {
            return reset($url_real);
        }

        return false;
    }

    function verify($url_real, $url_alias, $validate = true) {
        // Remove unexpected characters
        $url_alias = $validate ? zerophp_uri_validate($url_alias) : $url_alias;

        // Check url alias exists
        $alias = $this->loadEntityByAlias($url_alias);
        if (!empty($alias->url_alias) && $alias->url_real != $url_real) {
            $url_alias .= '-' . strtolower(str_random(4));

            // Re-check with new url alias
            $url_alias = $this->verify($url_real, $url_alias, false);
        }

        return $url_alias;
    }






    function url_alias_create($url_real, $url_alias, $prefix = '') {
        $prefix = $prefix ? $prefix : fw_variable_get('url alias all prefix', '');

        // Remove prefix
        $old_prefix = strpos($url_alias, $prefix . '/');
        if ($old_prefix === 0) {
            $url_alias = substr($url_alias, $old_prefix + strlen($prefix) + 1);
        }

        $url_alias = $prefix . '/' . $url_alias;
        $url_alias = $this->url_alias_verify_alias($url_real, $url_alias);

        // Save to db
        $this->CI->load->model('url_alias_model');
        $alias = $this->CI->url_alias_model->get_from_real($url_real);
        if (!empty($alias->url_alias)) {
            // Update a new url alias for old url real
            if ($alias->url_alias != $url_alias) {
                @$this->CI->cachef->del_url_alias($alias->url_alias);
                $this->CI->cachef->set_url_alias($url_alias, $url_real);
                $this->CI->cachef->set_url_real($url_real, $url_alias);

                $alias->url_alias = $url_alias;
                $this->entity_save($alias);
            }
        }
        // Save a new url alias for new url real
        else {

            $this->CI->cachef->set_url_alias($url_alias, $url_real);
            $this->CI->cachef->set_url_real($url_real, $url_alias);

            $alias->url_real = $url_real;
            $alias->url_alias = $url_alias;
            $this->entity_save($alias);
        }
    }

    function crud_create_form_submit($form_id, $form, &$form_values, $message = '') {
        $new = true;
        // Create url alias cache file
        if (!empty($form_values['url_alias_id'])) {
            $url = $this->loadEntity($form_values['url_alias_id'], array('cache' => false));
            @$this->CI->cachef->del_url_alias($url->url_alias);
            @$this->CI->cachef->del_url_real($url->url_real);
            $new = false;
        }

        $this->url_alias_create($form_values['url_real'], $form_values['url_alias']);

        $message = $message ? $message : zerophp_lang('Your data was updated successfully.');
        $this->CI->theme->messages_add($message, 'success');

        $this->crud_create_form_submit_hook();
    }

    function crud_delete_form_submit($form_id, $form, &$form_values, $message = '') {
        // Delete url alias cache file
        foreach ($form_values['#delete'] as $url_id) {
            $url = $this->loadEntity($url_id, array('cache' => false));
            @$this->CI->cachef->del_url_alias($url->url_alias);
            @$this->CI->cachef->del_url_real($url->url_real);
        }

        parent::crud_delete_form_submit($form_id, $form, $form_values, $message);
    }

    function url_alias_form_alter($form_id, &$form) {
        if (!in_array($form_id, fw_variable_get('url alias form alter support', array()))) {
            return;
        }

        //@todo 9 Chuyen hidden field thanh text field cho phep user tu nhap alias
        // Dieu nay phai phan quyen, boi vi nguoi ban hang co the khong duoc phep nhap url alias
        $form['url_alias'] = array(
            '#name' => 'url_alias',
            '#type' => 'input',
            '#label' => zerophp_lang('URL alias'),
            '#item' => array(
                'type' => 'input',
                'name' => 'url_alias',
            ),
            '#description' => zerophp_lang('Example:') . ' ' . zerophp_lang('female-fashion'),
        );

        if (substr($form_id, 0, 19) == 'entity_crud_update_') {
            $form['url_alias']['#disabled'] = 'disabled';
            $form['url_alias']['#item']['disabled'] = 'disabled';
        }

        $form['#submit'][] = array(
            'class' => 'url_alias',
            'function' => 'url_alias_form_alter_submit',
        );
    }

    function url_alias_form_alter_submit($form_id, $form, &$form_values) {
        if(!empty($form_values['url_alias']) && !empty($form_values['entity_name'])) {
            $entity = Entity::loadEntityObject($form_values['entity_name']);
            $structure = $this->CI->{$form_values['entity_name']}->getStructure();

            $url_real = 'e/read/' . $form_values['entity_name'] . '/' . $form_values[$structure['id']];
            $prefix = fw_variable_get("url alias entity " . $form_values['entity_name'] . " prefix", '');
            $this->url_alias_create($url_real, $form_values['url_alias'], $prefix);
        }
    }

    function url_alias_form_value_alter($form_id, $form, &$form_values) {
        if (!in_array($form_id, fw_variable_get('url alias form alter support', array()))) {
            return;
        }

        if(empty($form['entity_name']['#item']['entity_name'])) {
            return;
        }

        $entity_name = $form['entity_name']['#item']['entity_name'];
        $entity = Entity::loadEntityObject($entity_name);
        $structure = $this->CI->{$entity_name}->getStructure();

        if (!empty($form_values[$structure['id']])) {
            $url_alias = $this->CI->cachef->get_url_real("e/read/$entity_name/" . $form_values[$structure['id']]);
            $form_values['url_alias'] = $url_alias ? $url_alias : ' ';
        }
    }
}