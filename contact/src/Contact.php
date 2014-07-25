<?php 
namespace ZeroPHP\Contact;

use ZeroPHP\ZeroPHP\Entity;

class Contact extends Entity {
    function __construct() {
        $this->setStructure(array(
            'id' => 'contact_id',
            'name' => 'contact',
            'class' => 'ZeroPHP\Contact\Contact',
            'title' => zerophp_lang('Contact Us'),
            'fields' => array(
                'contact_id' => array(
                    'name' => 'contact_id',
                    'title' => zerophp_lang('ID'),
                    'type' => 'hidden',
                ),
                'fullname' => array(
                    'name' => 'fullname',
                    'title' => zerophp_lang('Fullname'),
                    'type' => 'input',
                    'placeholder' => zerophp_lang('Paolo Maldini'),
                ),
                'email' => array(
                    'name' => 'email',
                    'title' => zerophp_lang('Email'),
                    'type' => 'input',
                    'validate' => 'valid_email',
                    'js_validate' => array(
                        'data-validate' => 'email',
                    ),
                    'error_messages' => zerophp_lang('Invalid email'),
                    'placeholder' => zerophp_lang('paolo.maldini@gmail.com'),
                ),
                'title' => array(
                    'name' => 'title',
                    'title' => zerophp_lang('Subject'),
                    'type' => 'input',
                    'validate' => 'required',
                    'required' => true,
                ),
                'content' => array(
                    'name' => 'content',
                    'title' => zerophp_lang('Message'),
                    'type' => 'textarea',
                    'validate' => 'required',
                    'required' => true,
                ),
                'created_at' => array(
                    'name' => 'created_at',
                    'title' => zerophp_lang('Created date'),
                    'type' => 'input',
                    'widget' => 'date_timestamp',
                    'form_hidden' => 1,
                ),
            ),
        ));
    }

    function crud_create($type = 'create', $entity = null, $url_prefix = '', $action = '') {
        $result = parent::crud_create($type, $entity, $url_prefix, $action);

        $result['page_title'] = zerophp_lang('Contact Us');

        return $result;
    }
}