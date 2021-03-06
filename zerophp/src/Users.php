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
            '#class' => '\ZeroPHP\ZeroPHP\Users',
            '#title' => zerophp_lang('Users'),
            '#links' => array(
                'list' => 'admin/user/list',
                'create' => 'admin/user/create',
                'read' => 'admin/user/%',
                'preview' => 'admin/user/%/preview',
                'update' => 'admin/user/%/update',
                'delete' => 'admin/user/%/delete',
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
                'username' => array(
                    '#name' => 'username',
                    '#title' => zerophp_lang('Username'),
                    '#type' => 'text',
                    '#validate' => 'required',
                    '#required' => true,
                    '#list_hidden' => true,
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
                    '#list_hidden' => 1,
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
                    '#list_hidden' => 1,
                    '#load_hidden' => 1,
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
                    '#list_hidden' => true,
                ),
                'deleted_at' => array(
                    '#name' => 'updated_at',
                    '#title' => zerophp_lang('Updated date'),
                    '#type' => 'text',
                    '#widget' => 'date_timestamp',
                    '#form_hidden' => 1,
                    '#list_hidden' => 1,
                ),
                'roles' => array(
                    '#name' => 'roles',
                    '#title' => zerophp_lang('Roles'),
                    '#type' => 'checkboxes',
                    '#options_callback' => array(
                        'class' => '\ZeroPHP\ZeroPHP\Role',
                        'method' => 'loadOptionsAll',
                    ),
                    '#reference' => array(
                        'name' => 'role',
                        'class' => '\ZeroPHP\ZeroPHP\Role',
                        'internal' => false,
                    ),
                    '#display_hidden' => 1,
                    '#list_hidden' => 1,
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

        if (empty($entity->id) && empty($entity->username)) {
            $entity->username = $entity->email;
        }

        return parent::saveEntity($entity);
    }

    function loadEntityByEmail($email, $attributes = array()) {
        $attributes['where']['email'] = $email;

        $entity = $this->loadEntityExecutive(null, $attributes);
        return reset($entity);
    }

    public function buildEntity($entity, $attributes = array()) {
        $entity = parent::buildEntity($entity, $attributes);

        // Registered user
        $entity->roles[2] = 2;

        // Super admin
        $entity->roles[3] = 3;

        return $entity;
    }

    private function _unsetFormItem(&$form) {
        unset($form['username'], $form['active'], $form['remember_token'], $form['last_activity'],
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
        $form = $this->showCreateForm();
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
            'method' => 'showRegisterFormValidate',
        );

        $form['accepted'] = array(
            '#name' => 'accepted',
            '#type' => 'checkbox',
            '#value' => 1,
            '#title' => zerophp_lang('I agree to the :term and :policy', array(
                    ':term' => zerophp_anchor('article/3', zerophp_lang('Terms of Use'), array('target' => '_blank')),
                    ':policy' => zerophp_anchor('article/4', zerophp_lang('Privacy Policy'), array('target' => '_blank')),
                )),
            '#validate' => 'accepted',
            '#attributes' => array(
                'required' => '',
            ),
        );

         $form['#actions']['reset'] = array(
            '#name' => 'reset',
            '#type' => 'reset',
            '#value' => zerophp_lang('Reset'),
        );


        $form['#submit'][] = array(
            'class' => 'ZeroPHP\ZeroPHP\Users',
            'method' => 'showRegisterFormSubmit',
        );

        $form['#theme'] = 'form-popup';

        $form['#redirect'] = 'user/register/success';
        $form['#success_message'] = '';

        return $form;
    }

    function showRegisterFormValidate($form_id, $form, &$form_values) {
        $active = zerophp_variable_get('users register email validation', 1);
        if ($active) {
            $form_values['active'] = 0;
        }
        else {
            $form_values['active'] = 1;
        }

        return true;
    }

    function showRegisterFormSubmit($form_id, $form, &$form_values) {
        if ($form_values['active'] == 0) {
            $activation = new \ZeroPHP\ZeroPHP\Activation;
            $hash = $activation->setHash($form_values['id'], 'user_register');

            $vars = array(
                'email' => $form_values['email'],
                'link' => zerophp_url("user/activation/" . $hash->hash),
                'expired' => $hash->expired,
            );

            zerophp_mail($form_values['email'], 
                zerophp_lang(zerophp_variable_get('user activation email subject', 'Activation your account')),
                zerophp_view('email_user_activation', $vars)
            );
        }

        \Session::put('user registered email', $form_values['email']);
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
                'method' => 'showLoginFormValidate',
            ),
        );

        $form['#redirect'] = zerophp_redirect_get_path();

        $form['#theme'] = 'users_login';

        return $form;
    }

    function showLoginFormValidate($form_id, &$form, &$form_values) {
        $zerophp = zerophp_get_instance();

        if (\Auth::attempt(array(
                'email' => $form_values['email'], 
                'password' => $form_values['password'],
                'active' => 1,
            ), $form_values['remember_me'] == 1 ? true : false, true)
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

        $form['#error']['#form'] = zerophp_lang('Login failed. <br/> - Your password is incorrect <br/> - OR Your account is not active yet <br/> - OR Your account was blocked. Please try again later.');
        return false;
    }

    function showLogout($zerophp) {
        \Auth::logout();
        $zerophp->response->addMessage(zerophp_lang('You are successfully logout.'));

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
                'method' => 'showChangePasswordFormValidate',
            ),
        );

        $form['#submit'] = array(
            array(
                'class' => 'ZeroPHP\ZeroPHP\Users',
                'method' => 'showChangePasswordFormSubmit',
            ),
        );

        $form['#theme'] = 'form-popup';

        return $form;
    }


    function showChangePasswordFormValidate($form_id, &$form, &$form_values) {
        $passwd = \Auth::user()->__get('password');

        if (\Hash::check($form_values['password_old'], $passwd)) {
            return true;
        }

        $form['#error'][] = zerophp_lang('Your old password does not match.');

        return false;
    }

    function showChangePasswordFormSubmit($form_id, $form, &$form_values){
        $user = $this->loadEntity(zerophp_userid());
        $user->password = $form_values['password'];
        $this->saveEntity($user);

        zerophp_get_instance()->response->addMessage(zerophp_lang('Your password was reset successfully.'));
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
                'method' => 'showForgotPasswordFormSubmit',
            ),
        );

        $form['#theme'] = 'form-popup';
        
        $form['#redirect'] = 'user/forgotpass/success';

        return $form;
    }

    function showForgotPasswordFormSubmit($form_id, $form, &$form_values) {
        $user = $this->loadEntityByEmail($form_values['email']);

            $activation = new \ZeroPHP\ZeroPHP\Activation;;
            $hash = $activation->setHash($user->id, 'user_forgotpass');

            $vars = array(
                'title' => $user->title,
                'link' => zerophp_url("user/resetpass/" . $hash->hash),
            );

            zerophp_mail($form_values['email'], 
                zerophp_lang(zerophp_variable_get('user forgotpass email subject', 'Reset your password')),
                zerophp_view('email_user_reset_pass', $vars)
            );            
    }

    function showForgotPasswordSuccess($zerophp) {
        $items = array(
            array(
                '#item' => zerophp_lang('User forgot password')
            )
        );
        $zerophp->response->setBreadcrumb($items);

        $zerophp->response->addContent(zerophp_view('users_forgotpass_success'));
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
                'method' => 'showResetPasswordFormValidate',
            ),
        );

        $form['#submit'] = array(
            array(
                'class' => 'ZeroPHP\ZeroPHP\Users',
                'method' => 'showChangePasswordFormSubmit',
            ),
        );

        $form['#theme'] = 'form-popup';
        $form['#redirect'] = 'user/resetpass/success';
        $form['#success_message'] = '';

        return $form;
    }

    function showResetPasswordFormValidate($form_id, $form, &$form_values) {
        $activation = new \ZeroPHP\ZeroPHP\Activation;;
        $hash = $activation->loadEntityByHash($form_values['hash']);

        if (isset($hash->destination_id)) {
            \Auth::loginUsingId($hash->destination_id);

            return true;
        }

        return false;
    }

    function showResetPasswordSuccess($zerophp) {
        $items = array(
            array(
                '#item' => zerophp_lang('User reset password')
            )
        );
        $zerophp->response->setBreadcrumb($items);

        $zerophp->response->addContent(zerophp_view('users_resetpass_success'));
    }

    function showActivationResend($zerophp) {
        $form = array(
            'class' => '\ZeroPHP\ZeroPHP\Users',
            'method' => 'showActivationResendForm',
        );

        $zerophp->response->addContent(Form::build($form));
    }

    function showActivationResendForm() {
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
            'method' => 'showActivationResendFormSubmit',
        );

        $form['#redirect'] = 'user/activation/resend/success';

        $form['#theme'] = 'form-popup';

        return $form;
    }

    function showActivationResendFormSubmit($form_id, $form, &$form_values) {
            $user = $this->loadEntityByEmail($form_values['email']);

            $activation = new \ZeroPHP\ZeroPHP\Activation;
            $hash = $activation->setHash($user->id, 'user_activation_resend');

            $vars = array(
                'email' => $form_values['email'],
                'link' => zerophp_url("user/activation/" . $hash->hash),
                'expired' => $hash->expired,
            );

            zerophp_mail($form_values['email'], 
                zerophp_lang(zerophp_variable_get('user activation email subject', 'Activation your account')),
                zerophp_view('email_user_activation_resend', $vars)
            );
    }

    function showActivationResendSuccess($zerophp) {
        $items = array(
            array(
                '#item' => zerophp_lang('User activation resend')
            )
        );
        $zerophp->response->setBreadcrumb($items);

        $zerophp->response->addContent(zerophp_view('users_activation_resend_success'));
    }

    function showActivation($zerophp, $hash) {
        $activation = new \ZeroPHP\ZeroPHP\Activation;;
        $hash = $activation->loadEntityByHash($hash);

        if (isset($hash->destination_id)) {
            $user = $this->loadEntity($hash->destination_id);
            $user->active = 1;
            $this->saveEntity($user);

            $activation->deleteEntity($hash->activation_id);

            $zerophp->response->addMessage(zerophp_lang('Your account was successfully activated.'));
        }
        else {
            $zerophp->response->addMessage(zerophp_lang('Your activation link has expired. Please use activation resend feature.'));
        }

        return zerophp_redirect();
    }

    function showCreateForm(){
        $form = parent::showCreateForm();

        $temp_active = $form['active'];
        unset($form['active']);
        $temp_roles = $form['roles'];
        unset($form['roles']);

        $form['password_confirm'] = $form['password'];
        $form['password_confirm']['#name'] = 'password_confirm';
        $form['password_confirm']['#title'] = zerophp_lang('Password confirmation');
        $form['password_confirm']['#attributes']['data-validate'] = 'password_confirm';
        $form['password_confirm']['#error_messages'] = zerophp_lang('Password confirmation is not match with password');

        $form['active'] = $temp_active;
        $form['roles'] = $temp_roles;

        return $form;
     }

     function showUpdate($zerophp, $userid) {
        $form_values = $this->loadEntity($userid);

        $form = array(
            'class' => '\ZeroPHP\ZeroPHP\Users',
            'method' => 'showUpdateForm',
        );
        $zerophp->response->addContent(Form::build($form, $form_values));
    }

    function showUpdateForm() {
        $form = $this->showCreateForm();

        $form['email']['#disabled'] = true;
        $form['email']['#attributes']['disabled'] = 'disabled';
        unset($form['email']['#description']);

        $form['username']['#disabled'] = true;
        $form['username']['#attributes']['disabled'] = 'disabled';

        unset($form['password']);
        unset($form['password_confirm']);

        return $form;
    }
}