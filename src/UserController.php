<?php 
namespace ZeroPHP\ZeroPHP;

use ZeroPHP\ZeroPHP\Entity;
use ZeroPHP\ZeroPHP\Form;

class UserController {
    private $fields;

    private function _unsetFormItem(&$form) {
        unset($form['active'], $form['remember_token'], $form['last_activity'],
            $form['created_at'], $form['updated_at'], $form['deleted_at']);
    }

    function showRegisterForm($zerophp) {
        $user = Entity::loadEntityObject('ZeroPHP\ZeroPHP\Users');
        $form = $user->crudCreateForm();
        $this->_unsetFormItem($form);
        unset($form['id'], $form['roles']);

        // Validate email unique
        $form['email']['#validate'] .= '|unique:users,email';

        // Add password confirmation field
        $form['password_confirm'] = $form['password'];
        $form['password_confirm']['#title'] = zerophp_lang('Password confirmation');
        $form['password_confirm']['#name'] = 'password_confirm';
        $form['password_confirm']['#attributes']['data-validate'] = 'password_confirm';
        $form['password_confirm']['#error_messages'] = zerophp_lang('New password confirmation is not match with new password');

        $form['#actions']['submit']['#value'] = zerophp_lang('Register');

        $form['#redirect'] = 'user/register/success';
        $form['#success_message'] = '';

        $zerophp->response->addContent(Form::build($form));
    }

    function showRegisterSuccess($zerophp) {
        $items = array(
            array(
                '#item' => zerophp_lang('User register')
            )
        );
        $zerophp->response->setBreadcrumb($items);

        $vars = array();
        $zerophp->response->addContent(zerophp_view('users_register_success', $vars));
    }

    function showLoginForm($zerophp) {
        $form = array();
        $user = Entity::loadEntityObject('ZeroPHP\ZeroPHP\Users');
        $structure = $user->getStructure();

        $form['email'] = $structure['#fields']['email'];
        $form['email']['#validate'] .= '|exists:users,email';
        unset($form['email']['#description']);

        $form['password'] = $structure['#fields']['password'];

        $form['remember_me'] = array(
            '#name' => 'remember_me',
            '#type' => 'checkbox',
            '#value' => 1,
            '#title' => zerophp_lang('Remember me'),
        );

        $form['#actions']['submit'] = array(
            '#name' => 'submit',
            '#type' => 'submit',
            '#value' => zerophp_lang('Login'),
        );

        $form['#validate'] = array(
            array(
                'class' => 'ZeroPHP\ZeroPHP\Users',
                'method' => 'login',
            ),
        );

        $form['#redirect'] = zerophp_redirect_get_path();

        $form['#theme'] = 'users_login';

        $zerophp->response->addContent(Form::build($form));
    }

    function userLogout($zerophp) {
        \Auth::logout();
        zerophp_get_instance()->response->addMessage(zerophp_lang('You are successfully logout.'));

        return zerophp_redirect();
    }

    function userChangePasswordForm($zerophp) {}

    function userForgotPasswordForm($zerophp) {}

    function userActivationResendForm($zerophp) {}

    function userResetPasswordForm($zerophp) {}

    function userActivation($zerophp) {}
}
