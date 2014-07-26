<?php 
namespace ZeroPHP\ZeroPHP;

use ZeroPHP\ZeroPHP\Entity;
use ZeroPHP\ZeroPHP\Form;

class UserController {
    private $fields;

    function __construct() {
        $fields = Entity::loadEntityObject('\ZeroPHP\ZeroPHP\Users')->getStructure();
        unset($fields['fields']['active'], $fields['fields']['remember_token'], $fields['fields']['last_activity'],
            $fields['fields']['created_at'], $fields['fields']['updated_at'], $fields['fields']['deleted_at']);
        $this->fields = $fields['fields'];
    }
    function showRegisterForm($zerophp) {
        $form = $this->fields;

        unset($form['user_id'], $form['roles']);
        $form['password_confirm'] = $form['password'];
        $form['password_confirm']['#label'] = zerophp_lang('Password confirmation');
        $form['password_confirm']['#name'] = 'password_confirm';
        $form['password_confirm']['#id'] = 'fii_password_confirm';
        $form['password_confirm']['#item']['name'] = 'password_confirm';
        $form['password_confirm']['#item']['id'] = 'fii_password_confirm_field';
        $form['password_confirm']['#item']['data-validate'] = 'password_confirm';
        $form['password_confirm']['#error_messages'] = zerophp_lang('New password confirmation is not match with new password');


        $form['submit'] = array(
            '#name' => 'submit',
            '#type' => 'submit',
            '#item' => array(
                'name' => 'submit',
                'value' => zerophp_lang('Login'),
            ),
        );

        $form['#validate'][] = array(
            'class' => 'users',
            'function' => 'login_form_validate',
        );

        $redirect = '/';
        if ($dest = $zerophp->request->query('destination')) {
            $redirect = $dest;
        }
        $form['#redirect'] = \URL::to($redirect);

        zerophp_devel_print($form);

        $zerophp->response->addContent(Form::build('users_login', $vars), zerophp_lang('Login'));
    }





    function showLoginForm($zerophp) {
        $vars = array(
            'form_id' => $this->users->login_form(),
        );
        $zerophp->response->addContent(zerophp_form('users_login', $vars), zerophp_lang('Login'));
    }

    function registerSuccess($zerophp) {
        $items = array(
            0 => array(
                'item' => zerophp_lang('User register'),
            )
        );
        $zerophp->response->addBreadcrumb($items);
        $zerophp->response->addContent('register_success', zerophp_lang('Register success'), '');
    }







    function logout() {
        $this->users->logout();
        redirect(!empty($zerophp->request->query('destination')) ? trim($zerophp->request->query('destination')) : '');
    }

    function forgot_pass() {
        $vars = array(
            'form_id' => $this->users->forgot_pass_form(),
        );
        $zerophp->response->addContent('users_forgot_pass', zerophp_lang('Forgot Pass'), $vars);
    }

    function changepass() {
        $vars = array(
            'form_id' => $this->users->change_pass_form(),
        );
        $zerophp->response->addContent('users_change_pass', zerophp_lang('Change Password'), $vars);
    }
}
