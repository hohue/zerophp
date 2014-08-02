<?php 
namespace ZeroPHP\ZeroPHP;

use ZeroPHP\ZeroPHP\Entity;
use ZeroPHP\ZeroPHP\EntityInterface;
use ZeroPHP\ZeroPHP\Form;

class Users extends Entity implements  EntityInterface {
    public function __config() {
        return array(
            '#id' => 'id',
            '#name' => 'users',
            '#class' => 'ZeroPHP\ZeroPHP\Users',
            '#title' => zerophp_lang('Users'),
            '#links' => array(
                //'list' => 'user/users',
            ),
            '#fields' => array(
                'id' => array(
                    '#name' => 'id',
                    '#title' => zerophp_lang('ID'),
                    '#type' => 'hidden',
                ),
                'title' => array(
                    '#name' => 'title',
                    '#title' => zerophp_lang('Fullname'),
                    '#type' => 'text',
                    '#attributes' => array(
                        'placeholder' => zerophp_lang('Paolo Maldini'),
                        'data-required' => '',
                    ),
                    '#validate' => 'required',
                    '#required' => true,
                    '#error_messages' => zerophp_lang('Required field'),
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
                    '#description' => zerophp_lang('Please enter your real email. We will sent you an email to activation your account.'),
                ),
                'password' => array(
                    '#name' => 'password',
                    '#title' => zerophp_lang('Password'),
                    '#type' => 'password',
                    '#validate' => 'min:8|max:36',
                    '#attributes' => array(
                        'data-validate' => 'password',
                    ),
                    '#display_hidden' => 1,
                    '#load_hidden' => 1,
                    '#description' => zerophp_lang('Must contain at least <font>8 characters</font>'),
                    '#error_messages' => zerophp_lang('Invalid password'),
                    '#required' => true,
                ),
                'active' => array(
                    '#name' => 'active',
                    '#title' => zerophp_lang('Status'),
                    '#type' => 'radios',
                    '#options' => array(
                        0 => zerophp_lang('InActive'),
                        1 => zerophp_lang('Active'),
                        2 => zerophp_lang('Blocked')
                    ),
                    '#validate' => 'numeric|between:0,2',
                    '#default' => 0,
                ),
                'remember_token' => array(
                    '#name' => 'remember_token',
                    '#title' => zerophp_lang('Remember token'),
                    '#type' => 'text',
                    '#form_hidden' => 1,
                ),
                'last_activity' => array(
                    '#name' => 'last_activity',
                    '#title' => zerophp_lang('Last active'),
                    '#type' => 'text',
                    '#widget' => 'date_timestamp',
                    '#form_hidden' => 1,
                ),
                'created_at' => array(
                    '#name' => 'created_at',
                    '#title' => zerophp_lang('Registered date'),
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
                'deleted_at' => array(
                    '#name' => 'updated_at',
                    '#title' => zerophp_lang('Updated date'),
                    '#type' => 'text',
                    '#widget' => 'date_timestamp',
                    '#form_hidden' => 1,
                ),
                'roles' => array(
                    '#name' => 'roles',
                    '#title' => zerophp_lang('Roles'),
                    '#type' => 'checkboxes',
                    '#reference' => array(
                        'name' => 'role',
                        'class' => 'ZeroPHP\ZeroPHP\Role',
                    ),
                    '#display_hidden' => 1,
                ),
            ),
            '#can_not_delete' => array(1),
        );
    }

    function saveEntity($entity) {
        if (empty($entity->password)) {
            unset($entity->password);
        }
        else {
            $entity->password = \Hash::make($entity->password);
        }

        return parent::saveEntity($entity);
    }

    function loadEntityByEmail($email, $attributes = array()) {
        $attributes['load_all'] = false;
        $attributes['where']['email'] = $email;

        $entity = $this->loadEntityExecutive(null, $attributes);
        return reset($entity);
    }

    function formLoginValidate($form_id, $form, &$form_values) {
        $zerophp = zerophp_get_instance();

        if (\Auth::attempt(array(
                'email' => $form_values['email'], 
                'password' => $form_values['password'],
                'active' => 1,
            ), $form_values['remember_me'] == 1 ? true : false)
        ) {
            $user = $this->loadEntityByEmail($form_values['email']);

            $zerophp->response->addMessage(zerophp_lang('You have been successfully logged in...'));

            // Update last_activity field
            $user = new \stdClass();
            $user->id = zerophp_userid();
            $user->last_activity = date('Y-m-d H:i:s');
            $this->saveEntity($user);

            return true;
        }

        //@todo 1 Them vao form error message
        $zerophp->response->addMessage(zerophp_lang('Login failed. Your password is incorrect OR Your account is not active yet OR Your account was blocked. Please try again later.'), 'error');
        return false;
    }

    function formRegisterValidate($form_id, $form, &$form_values) {
        $active = zerophp_variable_get('users register email validation', 1);
        if ($active) {
            $form_values['active'] = 0;
        }
        else {
            $form_values['active'] = 1;
        }

        return true;
    }

    function formRegisterSubmit($form_id, $form, &$form_values) {
        if ($form_values['active'] == 0) {
            $activation = new \ZeroPHP\ZeroPHP\Activation;;
            $hash = $activation->setHash($form_values['id'], 'user_register');

            $vars = array(
                'email' => $form_values['email'],
                'link' => url("user/activation/" . $hash->hash),
                'expired' => $hash->expired,
            );

            zerophp_mail($form_values['email'], 
                zerophp_lang(zerophp_variable_get('user activation email subject', 'Activation your account')),
                zerophp_view('email_user_activation', $vars)
            );
        }

        \Session::put('user registered email', $form_values['email']);
    }


    function formChangePasswordValidate($form_id, $form, &$form_values) {
        $passwd = \Auth::user()->__get('password');

        if (\Hash::check($form_values['password_old'], $passwd)) {
            return true;
        }

        zerophp_get_instance()->response->addMessage(zerophp_lang('Your old password does not match.'), 'error');

        return false;
    }

    function formChangePasswordSubmit($form_id, $form, &$form_values){
        $user = $this->loadEntity(zerophp_userid());
        $user->password = $form_values['password'];
        $this->saveEntity($user);

        zerophp_get_instance()->response->addMessage(zerophp_lang('Your password was reset successfully.'));
    }

    function formResetPasswordValidate($form_id, $form, &$form_values) {
        $activation = new \ZeroPHP\ZeroPHP\Activation;;
        $hash = $activation->loadEntityByHash($form_values['hash']);

        if (isset($hash->destination_id)) {
            \Auth::loginUsingId($hash->destination_id);

            return true;
        }

        return false;
    }

    function formForgotPasswordSubmit($form_id, $form, &$form_values) {
        $user = $this->loadEntityByEmail($form_values['email']);

            $activation = new \ZeroPHP\ZeroPHP\Activation;;
            $hash = $activation->setHash($user->id, 'user_forgotpass');

            $vars = array(
                'title' => $user->title,
                'link' => url("user/resetpass/" . $hash->hash),
            );

            zerophp_mail($form_values['email'], 
                zerophp_lang(zerophp_variable_get('user forgotpass email sRubject', 'Reset your password')),
                zerophp_view('email_user_reset_pass', $vars)
            );

            \Session::put('user forgotpass email', $form_values['email']);
            
    }

    private function _unsetFormItem(&$form) {
        unset($form['active'], $form['remember_token'], $form['last_activity'],
            $form['created_at'], $form['updated_at'], $form['deleted_at']);
    }

    function showRegister($zerophp) {
        $form = array(
            'class' => '\ZeroPHP\ZeroPHP\Users',
            'method' => 'showRegisterForm',
        );

        $zerophp->response->addContent(Form::build($form));
    }

    function showRegisterForm() {
        $form = $this->crudCreateForm();
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
            'method' => 'formRegisterValidate',
        );

        $form['remember_me'] = array(
            '#name' => 'remember_me',
            '#type' => 'checkbox',
            '#value' => 1,
            '#title' => zerophp_lang('I agree to the :term and :policy', array(
                    ':term' => zerophp_anchor('article/3', zerophp_lang('Terms of Use'), array('target' => '_blank')),
                    ':policy' => zerophp_anchor('article/4', zerophp_lang('Privacy Policy'), array('target' => '_blank')),
                )),
            '#validate' => 'accepted',
        );

         $form['#actions']['reset'] = array(
            '#name' => 'reset',
            '#type' => 'reset',
            '#value' => zerophp_lang('Reset'),
        );


        $form['#submit'][] = array(
            'class' => 'ZeroPHP\ZeroPHP\Users',
            'method' => 'formRegisterSubmit',
        );

        $form['#redirect'] = 'user/register/success';
        $form['#success_message'] = '';

        return $form;
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

    function showLogin($zerophp) {
        $form = array(
            'class' => '\ZeroPHP\ZeroPHP\Users',
            'method' => 'showLoginForm',
        );

        $zerophp->response->addContent(Form::build($form));
    }

    function showLoginForm() {
        $form = array();
        $structure = $this->getStructure();

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
                'method' => 'formLoginValidate',
            ),
        );

        $form['#redirect'] = zerophp_redirect_get_path();

        $form['#theme'] = 'users_login';

        return $form;
    }

    function showLogout($zerophp) {
        \Auth::logout();
        zerophp_get_instance()->response->addMessage(zerophp_lang('You are successfully logout.'));

        return zerophp_redirect();
    }

    function showChangePassword($zerophp) {
        $form = array(
            'class' => '\ZeroPHP\ZeroPHP\Users',
            'method' => 'showChangePasswordForm',
        );

        $zerophp->response->addContent(Form::build($form));
    }

    function showChangePasswordForm() {
        $structure = $this->getStructure();

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
                'method' => 'formChangePasswordValidate',
            ),
        );

        $form['#submit'] = array(
            array(
                'class' => 'ZeroPHP\ZeroPHP\Users',
                'method' => 'formChangePasswordSubmit',
            ),
        );

        return $form;
    }

    function showForgotPassword($zerophp) {
        $form = array(
            'class' => '\ZeroPHP\ZeroPHP\Users',
            'method' => 'showForgotPasswordForm',
        );

        $zerophp->response->addContent(Form::build($form));
    }

    function showForgotPasswordForm() {
        $structure = $this->getStructure();
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
                'method' => 'formForgotPasswordSubmit',
            ),
        );
        
        $form['#redirect'] = 'user/forgotpass/success';
        $form['#success_message'] = zerophp_lang('You have successfully activated');

        return $form;
    }

    function showForgotPasswordSuccess($zerophp) {
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

    function showResetPassword($zerophp, $hash) {
        $form = array(
            'class' => '\ZeroPHP\ZeroPHP\Users',
            'method' => 'showResetPasswordForm',
            'arguments' => $hash,
        );

        $zerophp->response->addContent(Form::build($form));
    }

    function showResetPasswordForm($hash) {
        $structure = $this->getStructure();
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
                'method' => 'formResetPasswordValidate',
            ),
        );

        $form['#submit'] = array(
            array(
                'class' => 'ZeroPHP\ZeroPHP\Users',
                'method' => 'formChangePasswordSubmit',
            ),
        );

        return $form;
    }

    function showActivationResend($zerophp) {
        $form = array(
            'class' => '\ZeroPHP\ZeroPHP\Users',
            'method' => 'showActivationResendForm',
        );

        $zerophp->response->addContent(Form::build($form));
    }

    function showActivationResendForm($zerophp) {
        $structure = $this->getStructure();
        $form = array();

        $form['email'] = $structure['#fields']['email'];
        $form['email']['#validate'] .= '|exists:users,email';

        $form['#actions']['submit'] = array(
            '#name' => 'submit',
            '#type' => 'submit',
            '#value' => zerophp_lang('User Activation'),
        );

        $form['#submit'][] = array(
            'class' => 'ZeroPHP\ZeroPHP\Users',
            'method' => 'formActivationResendSubmit',
        );

        $form['#redirect'] = 'user/activation/resend';
        $form['#success_message'] = zerophp_lang('confirmation email has been sent successfully');

        return $form;
    }

    function formActivationResendSubmit($form_id, $form, &$form_values) {
            $user = $this->loadEntityByEmail($form_values['email']);

            $activation = new \ZeroPHP\ZeroPHP\Activation;;
            $hash = $activation->setHash($user->id, 'user_activation_resend');

            //zerophp_devel_print($hash);

            $vars = array(
                'email' => $form_values['email'],
                'link' => url("user/activation/" . $hash->hash),
                'expired' => $hash->expired,
            );

            zerophp_mail($form_values['email'], 
                zerophp_lang(zerophp_variable_get('user activation email subject', 'Activation your account')),
                zerophp_view('email_user_activation_resend', $vars)
            );

            \Session::put('user activation resend email', $form_values['email']);
    }

    function showActivation($zerophp, $hash) {
        $activation = new \ZeroPHP\ZeroPHP\Activation;;
        $hash = $activation->loadEntityByHash($hash);

        if (isset($hash->destination_id)) {
            $user = $this->loadEntity($hash->destination_id);
            $user->active = 1;
            $this->saveEntity($user);

            $zerophp->response->addMessage(zerophp_lang('Your account was successfully activated.'));
        }
        else {
            $zerophp->response->addMessage(zerophp_lang('Your activation link has expired. Please use activation resend feature.'));
        }

        zerophp_redirect();
    }

   





    

    function entity_reference($entity, $field, $attributes = array()) {
        $structure = $this->getStructure();
        $entity_id = $entity->{$structure['#id']};
        // Get from cache
        if (!isset($attributes['cache']) || $attributes['cache']) {
            $cache_name = "Users-reference-$field-$entity_id-" . serialize($attributes);
            if ($cache_content = \Cache::get($cache_name)) {
                return $cache_content;
            }
        }

        $result = parent::entity_reference($entity, $field, $attributes);

        if ($field == 'roles') {
            if ($entity_id) {
                // Super admin
                if ($entity_id == 1) {
                    $result[3] = $this->CI->roles->loadEntity(3, $attributes);
                }

                // Registered user
                if (empty($result[2])) {
                    $result[2] = $this->CI->roles->loadEntity(2, $attributes);
                }
            }

            // Anonymous user
            else {
                $result[1] = $this->CI->roles->loadEntity(1, $attributes);
            }
        }

        // Set to cache
        if (!isset($attributes['cache']) || $attributes['cache']) {
            \Cache::put($cache_name, $result, ZEROPHP_CACHE_EXPIRE_TIME);
        }

        return $result;
    }
}