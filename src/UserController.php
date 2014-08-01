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

        $form['#validate'][] = array(
            'class' => 'ZeroPHP\ZeroPHP\Users',
            'method' => 'registerFormValidate',
        );

        $form['#submit'][] = array(
            'class' => 'ZeroPHP\ZeroPHP\Users',
            'method' => 'registerFormSubmit',
        );

        $form['#redirect'] = 'user/register/success';
        $form['#success_message'] = '';

        $zerophp->response->addContent(Form::build($form));
    }

    function showRegisterSuccess($zerophp) {
        $email = \Session::get('user registered email');
        \Session::forget('user registered email');

        if (empty($email)) {
            \App::abort(404);
        }

        $items = array(
            array(
                '#item' => zerophp_lang('User register')
            )
        );
        $zerophp->response->setBreadcrumb($items);

        $vars = array(
            'email' => $email,
        );
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

    function userChangePasswordForm($zerophp) {
        $user = Entity::loadEntityObject('ZeroPHP\ZeroPHP\Users');
        $structure = $user->getStructure();

        $form = array();
        $form['password_old'] = $structure['#fields']['password'];
        $form['password_old']['#name'] = 'password_old';
        $form['password_old']['#title'] = zerophp_lang('Old password');

        $form['password'] = $structure['#fields']['password'];
        $form['password']['#name'] = 'password';
        $form['password']['#title'] = zerophp_lang('New password');

        $form['password_confirm'] = $structure['#fields']['password'];
        $form['password_confirm']['#name'] = 'password_confirm';
        $form['password_confirm']['#attributes']['data-validate'] = 'password_confirm';
        $form['password_confirm']['#title'] = zerophp_lang('Password confirmation');
        $form['password_confirm']['#error_messages'] = zerophp_lang('New password confirmation is not match with new password');

        $form['#actions']['submit'] = array(
            '#name' => 'submit',
            '#type' => 'submit',
            '#value' => zerophp_lang('Change Password'),
        );

        $form['#validate'] = array(
            array(
                'class' => 'ZeroPHP\ZeroPHP\Users',
                'method' => 'changepassValidate',
            ),
        );

        $form['#submit'] = array(
            array(
                'class' => 'ZeroPHP\ZeroPHP\Users',
                'method' => 'changepassSubmit',
            ),
        );

        //zerophp_devel_print($form);

        $zerophp->response->addContent(Form::build($form));
    }

    function userForgotPasswordForm($zerophp) {
        $user = Entity::loadEntityObject('ZeroPHP\ZeroPHP\Users');
        $structure = $user->getStructure();
        $form = array();

        $form['email'] = $structure['#fields']['email'];
        $form['email']['#validate'] .= '|exists:users,email';
        

        $form['#actions']['submit'] = array(
            '#name' => 'submit',
            '#type' => 'submit',
            '#value' => zerophp_lang('Forgot password'),
        );

        $form['#submit'] = array(
            array(
                'class' => 'ZeroPHP\ZeroPHP\Users',
                'method' => 'forgotpassFormSubmit',
            ),
        );
        
        $form['#redirect'] = 'user/forgotpass/success';
        $form['#success_message'] = 'you have successfully activated';

        $zerophp->response->addContent(Form::build($form));
    }

    function userForgotPasswordSuccess($zerophp) {
        $email = \Session::get('user forgotpass email');
        \Session::forget('user forgotpass email');

        if (empty($email)) {
            \App::abort(404);
        }

        $items = array(
            array(
                '#item' => zerophp_lang('User forgotpass')
            )
        );
        $zerophp->response->setBreadcrumb($items);

        $vars = array(
            'email' => $email,
        );

        $zerophp->response->addContent(zerophp_view('users_forgotpass_success', $vars));

    }

    function userResetPasswordForm($zerophp, $hash) {
        $user = Entity::loadEntityObject('ZeroPHP\ZeroPHP\Users');
        $structure = $user->getStructure();
        $form = array();

        $form['password'] = $structure['#fields']['password'];

        $form['password_confirm'] = $structure['#fields']['password'];
        $form['password_confirm']['#name'] = 'password_confirm';
        $form['password_confirm']['#title'] = zerophp_lang('Password confirmation');
        $form['password_confirm']['#attributes']['data-validate'] = 'password_confirm';
        $form['password_confirm']['#error_messages'] = zerophp_lang('Password confirmation is not match with password');
        $form['password_confirm']['#description'] = zerophp_lang('Send a confirmation email to register for an account at ChoVip.vn');

        $form['#actions']['submit'] = array(
            '#name' => 'submit',
            '#type' => 'submit',
            '#value' => zerophp_lang('Reset Password'),
        );

        $form['hash'] = array(
            '#name' => 'hash',
            '#type' => 'hidden',
            '#value' => $hash,
            '#disabled' => true,
        );

        $form['#validate'] = array(
            array(
                'class' => 'ZeroPHP\ZeroPHP\Users',
                'method' => 'resetValidate',
            ),
        );

        $form['#submit'] = array(
            array(
                'class' => 'ZeroPHP\ZeroPHP\Users',
                'method' => 'changepassSubmit',
            ),
        );


        //zerophp_devel_print($form);

        $zerophp->response->addContent(Form::build($form));
    }

    function userActivationResendForm($zerophp) {
        $user = Entity::loadEntityObject('ZeroPHP\ZeroPHP\Users');
        $structure = $user->getStructure();
        $form = array();

        $form['email'] = $structure['#fields']['email'];
        $form['email']['#validate'] .= '|exists:users,email';

        $form['#actions']['submit'] = array(
            '#name' => 'submit',
            '#type' => 'submit',
            '#value' => zerophp_lang('User Activation'),
        );

        $form['#validate'] = array(
            array(
                'class' => 'ZeroPHP\ZeroPHP\Users',
                'method' => 'activationresendValidate',
            ),
        );

        $form['#submit'] = array(
            array(
                'class' => 'ZeroPHP\ZeroPHP\Users',
                'method' => 'activationresendSubmit',
            ),
        );

        //zerophp_devel_print($form);

        $zerophp->response->addContent(Form::build($form));
    }
    
    function userActivation($zerophp, $hash) {
        $activation = Entity::loadEntityObject('ZeroPHP\ZeroPHP\Activation');
        $hash = $activation->loadEntityByHash($hash);

        if (isset($hash->destination_id)) {
            $user_obj = Entity::loadEntityObject('ZeroPHP\ZeroPHP\Users');
            $user = $user_obj->loadEntity($hash->destination_id);
            $user->active = 1;
            $user_obj->saveEntity($user);

            $zerophp->response->addMessage(zerophp_lang('Your account was successfully activated.'));
        }
        else {
            $zerophp->response->addMessage(zerophp_lang('Your activation link has expired. Please use activation resend feature.'));
        }

        zerophp_redirect();
    }
}
