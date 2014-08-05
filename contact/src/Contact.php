<?php 
namespace ZeroPHP\Contact;

use ZeroPHP\ZeroPHP\Entity;
use ZeroPHP\ZeroPHP\EntityInterface;
use ZeroPHP\ZeroPHP\Form;

class Contact extends Entity implements EntityInterface {
    function __config() {
        return array(
            '#id' => 'contact_id',
            '#name' => 'contact',
            '#class' => 'ZeroPHP\Contact\Contact',
            '#title' => zerophp_lang('Contact Us'),
            '#fields' => array(
                'contact_id' => array(
                    '#name' => 'contact_id',
                    '#title' => zerophp_lang('ID'),
                    '#type' => 'hidden',
                ),
                'fullname' => array(
                    '#name' => 'fullname',
                    '#title' => zerophp_lang('Fullname'),
                    '#type' => 'text',
                    '#attributes' => array(
                        'placeholder' => zerophp_lang('Paolo Maldini'),
                    ),
                    '#validate' => 'required',
                    '#required' => true,
                ),
                'email' => array(
                    '#name' => 'email',
                    '#title' => zerophp_lang('Email'),
                    '#type' => 'text',
                    '#validate' => 'required|email',
                    '#attributes' => array(
                        'data-validate' => 'email',
                        'placeholder' => zerophp_lang('paolo.maldini@gmail.com'),
                    ),
                    '#error_messages' => zerophp_lang('Invalid email'),
                    '#required' => true,
                ),
                'title' => array(
                    '#name' => 'title',
                    '#title' => zerophp_lang('Subject'),
                    '#type' => 'text',
                    '#validate' => 'required',
                    '#required' => true,
                ),
                'content' => array(
                    '#name' => 'content',
                    '#title' => zerophp_lang('Message'),
                    '#type' => 'textarea',
                    '#validate' => 'required',
                    '#required' => true,
                ),
                'created_at' => array(
                    '#name' => 'created_at',
                    '#title' => zerophp_lang('Created date'),
                    '#type' => 'text',
                    '#widget' => 'date_timestamp',
                    '#form_hidden' => 1,
                ),
                'updated_at' => array(
                    '#name' => 'updated_at',
                    '#title' => zerophp_lang('Updated date'),
                    '#type' => 'text',
                    '#widget' => 'date_timestamp',
                    '#form_hidden' => 1,
                ),
            ),
        );
    }

    function create($zerophp) {
        $form = array(
            'class' => '\ZeroPHP\Contact\Contact',
            'method' => 'createForm',
        );

        $zerophp->response->addContent(Form::build($form));
    }

    function createForm() {
        $form = $this->crudCreateForm();

        unset($form['contact_id']);

        return $form;
    }
}