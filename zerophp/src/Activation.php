<?php 
namespace ZeroPHP\ZeroPHP;

use ZeroPHP\ZeroPHP\Entity;

class Activation extends Entity {
    function __construct() {
        $this->setStructure(array(
            '#id' => 'activation_id',
            '#name' => 'activation',
            '#class' => 'ZeroPHP\ZeroPHP\Activation',
            '#title' => zerophp_lang('Activation'),
            '#fields' => array(
                'activation_id' => array(
                    '#name' => 'activation_id',
                    '#title' => zerophp_lang('ID'),
                    '#type' => 'hidden',
                ),
                'destination_id' => array(
                    '#name' => 'destination_id',
                    '#title' => zerophp_lang('ID'),
                    '#type' => 'hidden',
                ),
                'hash' => array(
                    '#name' => 'hash',
                    '#title' => zerophp_lang('Activation hash'),
                    '#type' => 'text',
                ),
                'expired' => array(
                    '#name' => 'expired',
                    '#title' => zerophp_lang('Expired'),
                    '#type' => 'text',
                ),
                'type' => array(
                    '#name' => '#type',
                    '#title' => zerophp_lang('Activation type'),
                    '#type' => 'text',
                ),
            ),
        ));
    }

    function users_create_form_alter($form_id, &$form) {
        $form['#submit'][] = array(
            'class' => 'activation',
            'method' => 'users_create_form_submit'
        );
    }

    function users_create_form_submit($form_id, $form, &$form_values) {
        if (empty($form_values['active'])) {
            $hash = $this->hash_set($form_values['id']);

            $content = array(
                'email' => $form_values['email'],
                'active_link' => \URL::to('activation/users/' . $hash),
            );
            $entity = Entity::loadEntityObject('mail');
            $this->CI->mail->send($form_values['email'], fw_variable_get('Activation email template users subject', 'Active your acount'), $content, 'mail_template_activation_users|activation');
        }
    }

    function hash_set($destination_id, $type = 'users') {
        // Load activation
        $activation = $this->loadEntity_from_destination($destination_id, array(
            'where' => array(
                'expired >=' => time(),
            ),
        ));

        if (!isset($activation->hash)) {
            $activation = new \stdClass();
            $activation->destination_id = $destination_id;
            $activation->expired = time() + $this->expired;
            $activation->hash = md5($activation->destination_id . $activation->expired . mt_rand());
            $activation->type = $type;

            //@todo 8 Them ma kich hoat cho phep thanh vien co the nhap truc tiep tu trang web

            $this->saveEntity($activation);
        }

        return $activation->hash;
    }

    function loadEntity_from_hash($hash, $attributes = array()) {
        $attributes['load_all'] = false;
        $attributes['where']['hash'] = $hash;
        $attributes['where']['expired >='] = time();
        return reset($this->loadEntityExecutive(null, $attributes));
    }

    function loadEntity_from_destination($destination_id, $attributes = array()) {
        $attributes['load_all'] = false;
        $attributes['where']['destination_id'] = $destination_id;
        return reset($this->loadEntityExecutive(null, $attributes));
    }

    function active_users($hash) {
        $activation = $this->loadEntity_from_hash($hash, array(
            'where' => array(
                '#type' => 'users',
            ),
        ));

        if (!empty($activation->destination_id) && ($activation->expired >= time())) {
            $user = $this->CI->users->loadEntity($activation->destination_id, array('check_active' => false));

            $user_update = new \stdClass();
            $user_update->id = $user->id;
            $user_update->active = 1;
            $this->CI->users->saveEntity($user_update);

            $this->entity_delete($activation->activation_id);

            return true;
        }

        return false;
    }

    function resend_users_form() {
        $form['email'] = array(
            '#name' => 'email',
            '#type' => 'text',
            '#label' => zerophp_lang('Email'),
            '#item' => array(
                '#name' => 'email',
                '#type' => 'text',
                'label' => zerophp_lang('Email'),
                'data-validate' => 'email',
                'placeholder' => zerophp_lang('paolo.maldini@gmail.com'),
            ),
        );

        $form['submit'] = array(
            '#name' => 'submit',
            '#type' => 'submit',
            '#item' => array(
                '#name' => 'submit',
                'value' => zerophp_lang('Resend'),
            ),
        );

        $form['#validate'][] = array(
            'class' => 'activation',
            'method' => 'resend_users_form_validate',
        );

        $form['#submit'][] = array(
            'class' => 'activation',
            'method' => 'resend_users_form_submit',
        );

        $form['#redirect'] = \URL::to();

        $form_id = 'activation-resend_form';
        $this->CI->form->form_build($form_id, $form);
        return $form_id;
    }

    function resend_users_form_validate($form_id, $form, &$form_values) {
        $entity = Entity::loadEntityObject('form_validation');
        $this->CI->form_validation->set_rules('email', $form['email']['#label'], 'trim|required|email|is_exists[users.email]');
        if ($this->CI->form_validation->run() == FALSE) {
            return false;
        }

        // Account activated
        $user = $this->CI->users->loadEntityByEmail($form_values['email'], array(
            'where' => array(
                'active' => 0,
            ),
        ));
        if (!isset($user->email)) {
            zerophp_get_instance()->response->addMessage(zerophp_lang('Your account was activated. Please login.'), 'error');
            return false;
        }
        $form_values['id'] = $user->id;

        return true;
    }

    function resend_users_form_submit($form_id, $form, &$form_values) {
        $hash = $this->hash_set($form_values['id']);

        // Send Email
        $content = array(
            'email' => $form_values['email'],
            'active_link' => \URL::to("activation/users/$hash")
        );
        $entity = Entity::loadEntityObject('mail');
        $this->CI->mail->send($form_values['email'], fw_variable_get('Activation email template users resend subject', 'Resend activation code'), $content, 'mail_template_activation_users_resend|activation');

        zerophp_get_instance()->response->addMessage(zerophp_lang('We sent activation email to your email. Please check your email now.'), 'success');
    }

    function users_reset_pass_form($hash) {
        $activation = $this->loadEntity_from_hash($hash);

        if (!$activation->destination_id) {
            zerophp_get_instance()->response->addMessage(zerophp_lang('Your reset password link is not match or has expired.'), 'error');
            return \Redirect::to(\URL::to());
        }

        $form['password'] = array(
            '#name' => 'password',
            '#type' => 'password',
            '#label' => zerophp_lang('Password'),
            '#item' => array(
                '#name' => 'password',
                '#type' => 'password',
                'label' => zerophp_lang('Password'),
                'data-validate' => 'password',
            ),
        );

        $form['password_confirm'] = array(
            '#name' => 'password_confirm',
            '#type' => 'password',
            '#label' => zerophp_lang('Password confirm'),
            '#item' => array(
                '#name' => 'password_confirm',
                '#type' => 'password',
                'label' => zerophp_lang('Password confirm'),
                'data-validate' => 'password_confirm',
            ),
        );

        $form['submit'] = array(
            '#name' => 'submit',
            '#type' => 'submit',
            '#item' => array(
                '#name' => 'submit',
                'value' => zerophp_lang('Submit'),
            ),
        );

        $form['id'] = array(
            '#name' => 'id',
            '#type' => 'hidden',
            '#disabled' => 'disabled',
            '#value' => $activation->destination_id,
            '#item' => array(
                'id' => $activation->destination_id,
            ),
        );

        $form['#validate'][] = array(
            'class' => 'activation',
            'method' => 'users_reset_pass_form_validate',
        );

        $form['#submit'][] = array(
            'class' => 'activation',
            'method' => 'users_reset_pass_form_submit',
        );

        $form['#redirect'] = \URL::to();

        $form_id = 'activation-users_reset_pass_form';
        $this->CI->form->form_build($form_id, $form);
        return $form_id;
    }

    function users_reset_pass_form_validate($form_id, $form, &$form_values) {
        $entity = Entity::loadEntityObject('form_validation');
        $this->CI->form_validation->set_rules('password_confirm', zerophp_lang('Password confirm'), 'require');
        $this->CI->form_validation->set_rules('password', zerophp_lang('Password'), 'required|min:8|max:12|matches[password_confirm]');
        if ($this->CI->form_validation->run() == FALSE) {
            return false;
        }

        $form_values['password'] = $this->CI->users->password_hash($form_values['password']);

        return true;
    }

    function users_reset_pass_form_submit($form_id, $form, &$form_values) {
        $user_update = new \stdClass();
        $user_update->id = $form_values['id'];
        $user_update->password = $form_values['password'];

        $this->CI->users->saveEntity($user_update);

        zerophp_get_instance()->response->addMessage(zerophp_lang('Your new password was saved successfully.'), 'success');
    }
}