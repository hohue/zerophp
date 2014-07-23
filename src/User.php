<?php 
namespace ZeroPHP\ZeroPHP;

use ZeroPHP\ZeroPHP\Entity;

class User extends Entity {

    private $user;
    private $expired = 7200; // 2 hours

    function __construct() {
        parent::__construct();

        

        $this->setStructure(array(
            'id' => 'user_id',
            'name' => 'users',
            'title' => zerophp_lang('Users'),
            'fields' => array(
                'user_id' => array(
                    'name' => 'user_id',
                    'title' => zerophp_lang('ID'),
                    'type' => 'hidden',
                ),
                'title' => array(
                    'name' => 'title',
                    'title' => zerophp_lang('Fullname'),
                    'type' => 'input',
                    'placeholder' => zerophp_lang('Paolo Maldini'),
                    'validate' => 'required',
                    'required' => true,
                ),
                'email' => array(
                    'name' => 'email',
                    'title' => zerophp_lang('Email'),
                    'type' => 'input',
                    'validate' => 'required|valid_email',
                    'js_validate' => array(
                        'data-validate' => 'email',
                    ),
                    'error_messages' => zerophp_lang('Invalid email'),
                    'required' => true,
                    'description' => zerophp_lang('Please enter your real email. We will sent you an email to activation your account.'),
                    'placeholder' => zerophp_lang('paolo.maldini@gmail.com'),
                    'required' => true,
                ),
                'password' => array(
                    'name' => 'password',
                    'title' => zerophp_lang('Password'),
                    'type' => 'password',
                    'validate' => 'min_length[8]|max_length[32]',
                    'js_validate' => array(
                        'data-validate' => 'password',
                    ),
                    'display_hidden' => 1,
                    'load_hidden' => 1,
                    'description' => zerophp_lang('Must contain at least <font>8 characters</font>'),
                    'error_messages' => zerophp_lang('Invalid password'),
                    'required' => true,
                ),
                'active' => array(
                    'name' => 'active',
                    'title' => zerophp_lang('Status'),
                    'type' => 'radio_build',
                    'options' => array(
                        1 => zerophp_lang('Active'),
                        0 => zerophp_lang('InActive'),
                        2 => zerophp_lang('Blocked')
                    ),
                    'validate' => 'numeric|greater_than[-1]|less_than[3]',
                    'default' => 0,
                ),
                'created_date' => array(
                    'name' => 'created_date',
                    'title' => zerophp_lang('Registered date'),
                    'type' => 'input',
                    'widget' => 'date_timestamp',
                    'form_hidden' => 1,
                ),
                'updated_date' => array(
                    'name' => 'updated_date',
                    'title' => zerophp_lang('Updated date'),
                    'type' => 'input',
                    'widget' => 'date_timestamp',
                    'form_hidden' => 1,
                ),
                'last_activity' => array(
                    'name' => 'last_activity',
                    'title' => zerophp_lang('Last active'),
                    'type' => 'input',
                    'widget' => 'date_timestamp',
                    'form_hidden' => 1,
                ),
                'roles' => array(
                    'name' => 'roles',
                    'title' => zerophp_lang('Roles'),
                    'type' => 'checkbox_build',
                    'reference' => 'roles',
                    'display_hidden' => 1,
                ),
            ),
            'can_not_delete' => array(1),
        ));

        // Get default data
        $this->user_set();
    }

    function user_get() {
        return $this->user;
    }

    function user_set() {
        $user = $this->CI->session->userdata('users-user');
        $last_activity = $this->CI->session->userdata('last_activity');

        if (isset($user->expired) && ($user->expired == 0 || $user->expired >= (time() - $last_activity))) {
            $this->user = $user;
        }
        // Login expired
        elseif (isset($user->user_id)) {
            $this->CI->session->unset_userdata('users-user');
        }

        // Anonymous user (user 0)
        if (!isset($this->user->user_id)) {
            $this->user = new stdClass();
            $this->user->user_id = 0;
            $this->user->roles = array(
                1 => $this->CI->roles->entity_load(1),
            );
        }

        // Hack for Responsive File Manager
        //@todo 9 Chuyen den hook_init
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION['user_id'] = $this->user->user_id;
    }

    function entity_load_from_email($email, $attributes = array()) {
        $attributes['load_all'] = false;
        $attributes['where']['email'] = $email;
        return reset($this->entity_load_executive(null, $attributes));
    }

    function login_form() {
        $form['email'] = array(
            '#name' => 'email',
            '#type' => 'input',
            '#label' => zerophp_lang('Email'),
            '#item' => array(
                'name' => 'email',
                'type' => 'input',
                'data-validate' => 'email',
                'placeholder' => zerophp_lang('paolo.maldini@gmail.com'),
            ),
            '#error_messages' => zerophp_lang('Invalid email'),
            '#required' => true,
        );

        $form['password'] = array(
            '#name' => 'password',
            '#type' => 'password',
            '#label' => zerophp_lang('Password'),
            '#item' => array(
                'name' => 'password',
                'type' => 'password',
                'data-validate' => 'password',
            ),
            '#description' => zerophp_lang('Must contain at least <font>8 characters</font>'),
            '#error_messages' => zerophp_lang('Invalid password'),
            '#required' => true,
        );

        $form['remember_me'] = array(
            '#name' => 'remember_me',
            '#type' => 'checkbox',
            '#label' => zerophp_lang('Remember me'),
            '#item' => array(
                'name' => 'remember_me',
                'type' => 'checkbox',
                'value' => 1,
            ),
        );

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

        $redirect = '';
        if (isset($_GET['destination'])) {
            $redirect = site_url(trim($_GET['destination']));
        }
        $form['#redirect'] = site_url($redirect);

        $form_id = 'users-login_form';
        $this->CI->form->form_build($form_id, $form);
        return $form_id;
    }

    function login_check($email, $password, &$user = null) {
        $attributes = array(
            'check_active' => false,
            'cache' => false,
            'load_hidden' => true,
        );
        $user = $this->entity_load_from_email($email, $attributes);

        if (isset($user->password) && $this->password_verify($password, $user->password)) {
            return true;
        }

        return false;
    }

    function login_form_validate($form_id, $form, &$form_values) {
        $structure = $this->getStructure();
        $entity = Entity::loadEntityObject('form_validation');

        $this->CI->form_validation->set_rules('email', zerophp_lang('Email'), $structure['fields']['email']['validate'] . '|is_exists[users.email]');
        $this->CI->form_validation->set_rules('password', zerophp_lang('Password'), $structure['fields']['password']['validate']);

        if ($this->CI->form_validation->run() == FALSE) {
            $this->CI->theme->messages_add(validation_errors(), 'error');
            return false;
        }

        ##### LOGIN #####
        $user = new stdClass();
        if ($this->login_check($form_values['email'], $form_values['password'], $user)) {
            // Update last_activity field
            $user_update = new stdClass();
            $user_update->user_id = $user->user_id;
            $user_update->last_activity = date('Y-m-d H:i:s');
            $this->entity_save($user_update);

            if ($user->active == 1) {
                $user_update->user_id = $user->user_id;
                $user_update->title = $user->title;
                $user_update->email = $user->email;
                $user_update->roles = $user->roles;
                $user_update->expired = isset($form_values['remember_me']) && $form_values['remember_me'] ? 0 : $this->expired;

                $data = array(
                    'users-user' => $user_update,
                );
                $this->CI->session->set_userdata($data);
                $this->user_set();

                $this->CI->theme->messages_add(lang('You have been successfully logged in...'));
            }
            elseif ($user->active == 2) {
                $this->CI->theme->messages_add(lang('Your account was blocked. Please contact the administrator.'), 'error');
            }
            else {
                $this->CI->theme->messages_add(lang('Your account is not active yet.'), 'error');
            }

            return true;
        }

        $this->CI->theme->messages_add(lang('Your password is incorrect. Please try again.'), 'error');
        return false;
    }

    function logout() {
        $this->CI->session->unset_userdata('users-user');
        $this->user_set();
        $this->CI->theme->messages_add(lang('You are successfully logout.'));
    }

    function password_hash($password) {
        return password_hash($password, PASSWORD_BCRYPT);
    }

    function password_verify($password, $hash) {
        return password_verify($password, $hash);
    }

    function entity_reference($entity, $field, $attributes = array()) {
        $structure = $this->getStructure();
        $entity_id = $entity->{$structure['id']};
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
                    $result[3] = $this->CI->roles->entity_load(3, $attributes);
                }

                // Registered user
                if (empty($result[2])) {
                    $result[2] = $this->CI->roles->entity_load(2, $attributes);
                }
            }

            // Anonymous user
            else {
                $result[1] = $this->CI->roles->entity_load(1, $attributes);
            }
        }

        // Set to cache
        if (!isset($attributes['cache']) || $attributes['cache']) {
            \Cache::put($cache_name, $result);
        }

        return $result;
    }

    function entity_load_executive($entity_id = null, $attributes = array(), $pager_sum = 1) {
        // Get from cache
        if (!isset($attributes['cache']) || $attributes['cache']) {
            $cache_name = "Users-entity_load_executive-$entity_id-" . serialize($attributes);
            if ($cache_content = \Cache::get($cache_name)) {
                return $cache_content;
            }
        }

        $entities = parent::entity_load_executive($entity_id, $attributes, $pager_sum);

        foreach ($entities as $key => $entity) {
            $entities[$key]->created_by = $entity->user_id;
        }

        // Set to cache
        if (!isset($attributes['cache']) || $attributes['cache']) {
            \Cache::put($cache_name, $entities);
        }

        return $entities;
    }

    function users_create_form_alter($form_id, &$form) {

        array_unshift($form['#validate'], array(
            'class' => 'users',
            'function' => 'users_create_form_validate'
        ));

        $form['password_confirm'] = $form['password'];
        $form['password_confirm']['#label'] = zerophp_lang('Password confirmation');
        $form['password_confirm']['#name'] = 'password_confirm';
        $form['password_confirm']['#id'] = 'fii_password_confirm';
        $form['password_confirm']['#item']['name'] = 'password_confirm';
        $form['password_confirm']['#item']['id'] = 'fii_password_confirm_field';
        $form['password_confirm']['#item']['data-validate'] = 'password_confirm';
        $form['password_confirm']['#error_messages'] = zerophp_lang('New password confirmation is not match with new password');

        //@todo 9 Move me to chovip module
        $form['accept'] = array(
            '#name' => 'accept',
            '#type' => 'checkbox',
            '#item' => array(
                'name' => 'accept',
                'type' => 'checkbox',
                'value' => 1,
                'data-required' => 'true',
            ),
            '#error_messages' => zerophp_lang('You must accept to register your account.'),
            '#description' => zerophp_lang('Accept websites\'s  policy', array(
                '%link1' => site_url('e/read/article/3'),
                '%link2' => site_url('e/read/article/4'),
            )),
        );
        $form['submit']['#item']['value'] = zerophp_lang('Register');
        $form['#redirect'] = site_url('user/register_success');

        unset($form['active']);
        unset($form['roles']);
    }

    function users_update_form_alter($form_id, &$form) {
        $this->users_create_form_alter($form_id, $form);

        $form['email']['#disabled'] = 'disabled';
        $form['email']['#item']['disabled'] = 'disabled';

        $form['email_disabled'] = array(
            '#name' => 'email_disabled',
            '#type' => 'hidden',
            '#item' => array(
                'email' => '',
            ),
        );
    }

    function users_update_form_value_alter($form_id, $form, &$form_values) {
        if (isset($form_values['email'])) {
            $form_values['email_disabled'] = array(
                'email' => $form_values->email,
            );
        }
    }

    function users_create_form_validate($form_id, $form, &$form_values) {
        $structure = $this->getStructure();
        $entity = Entity::loadEntityObject('form_validation');

        if ($form_id =='entity_crud_create_users') {
            $email_rule = 'is_unique[users.email]';
        }
        elseif ($form_id =='entity_crud_update_users') {
            $email_rule = 'is_exists[users.email]';
        }
        $this->CI->form_validation->set_rules('email', $form['email']['#label'], $structure['fields']['email']['validate'] . '|' . $email_rule);

        if (isset($form_values['password']) || isset($form_values['password_confirm']) || $form_id =='entity_crud_create_users') {
            $this->CI->form_validation->set_rules('password_confirm', $form['password_confirm']['#label'], 'require');
            $this->CI->form_validation->set_rules('password', $form['password']['#label'], $structure['fields']['password']['validate'] . '|matches[password_confirm]');

            $form_values['password'] = $this->password_hash($form_values['password']);
        }

        if ($this->CI->form_validation->run() == FALSE) {
            return false;
        }

        // Create user
        if ($form_id =='entity_crud_create_users') {
            $form_values['last_activity'] = entity_widget_date_timestamp_make(time());
        }

        return true;
    }

    function entity_save($entity) {
        if (empty($entity->password)) {
            unset($entity->password);
        }

        return parent::entity_save($entity);
    }

    function forgot_pass_form() {
        $form['email'] = array(
            '#name' => 'email',
            '#type' => 'input',
            '#label' => zerophp_lang('Email'),
            '#item' => array(
                'name' => 'email',
                'type' => 'input',
                'label' => zerophp_lang('Email'),
                'data-validate' => 'email',
                'placeholder' => zerophp_lang('paolo.maldini@gmail.com'),
            ),
            '#description' => zerophp_lang('Please enter your email exactly as you entered it when registering with our system.'),
            '#error_messages' => zerophp_lang('Invalid email'),
        );

        $form['submit'] = array(
            '#name' => 'submit',
            '#type' => 'submit',
            '#item' => array(
                'name' => 'submit',
                'value' => zerophp_lang('Send me a new password'),
            ),
        );

        $form['#validate'][] = array(
            'class' => 'users',
            'function' => 'forgot_pass_form_validate',
        );

        $form['#submit'][] = array(
            'class' => 'users',
            'function' => 'forgot_pass_form_submit',
        );

        $form_id = 'users-forgot_pass_form';
        $this->CI->form->form_build($form_id, $form);
        return $form_id;
    }

    function forgot_pass_form_validate($form_id, $form, &$form_values) {
        $entity = Entity::loadEntityObject('form_validation');
        $this->CI->form_validation->set_rules('email', $form['email']['#label'], 'trim|required|valid_email|is_exists[users.email]');
        if ($this->CI->form_validation->run() == FALSE) {
            return false;
        }

        return true;
    }

    function forgot_pass_form_submit($form_id, $form, &$form_values) {
        $user = $this->entity_load_from_email($form_values['email']);

        $entity = Entity::loadEntityObject('activation');
        $hash = $this->CI->activation->hash_set($user->user_id, 'users_reset_pass');

        // Send Email
        $content = array(
            'title' => $user->title,
            'link' => site_url("activation/users_reset_pass/$hash"),
        );
        $entity = Entity::loadEntityObject('mail');
        $this->CI->mail->send($form_values['email'], fw_variable_get('Activation email template users reset password subject', 'Reset your password'), $content, 'mail_template_activation_users_reset_pass|activation');

        $this->CI->theme->messages_add(lang('We sent reset password email to your email. Please check your email now.'), 'success');
    }

    function change_pass_form() {
        $form['password_old'] = array(
            '#name' => 'password_old',
            '#type' => 'password',
            '#label' => zerophp_lang('Old password'),
            '#item' => array(
                'name' => 'password_old',
                'type' => 'password',
                'label' => zerophp_lang('Old password'),
                'data-validate' => 'password',
                'placeholder' => zerophp_lang('Enter your current password'),
            ),
            '#description' => zerophp_lang('Must contain at least <font>8 characters</font>'),
            '#error_messages' => zerophp_lang('Invalid old password'),
            '#required' => true,
        );

        $form['password'] = array(
            '#name' => 'password',
            '#type' => 'password',
            '#label' => zerophp_lang('New password'),
            '#item' => array(
                'name' => 'password',
                'type' => 'password',
                'label' => zerophp_lang('New password'),
                'data-validate' => 'password',
                'placeholder' => zerophp_lang('Enter your new password'),
            ),
            '#description' => zerophp_lang('Must contain at least <font>8 characters</font>'),
            '#error_messages' => zerophp_lang('Invalid new password'),
            '#required' => true,
        );

        $form['password_confirm'] = array(
            '#name' => 'password_confirm',
            '#type' => 'password',
            '#label' => zerophp_lang('New password confirmation'),
            '#item' => array(
                'name' => 'password_confirm',
                'type' => 'password',
                'label' => zerophp_lang('New password confirm'),
                'data-validate' => 'password_confirm',
                'placeholder' => zerophp_lang('Enter your new confirmation password'),
            ),
            '#description' => zerophp_lang('Must contain at least <font>8 characters</font>'),
            '#error_messages' => zerophp_lang('New password confirmation is not match with new password'),
            '#required' => true,
        );

        $form['submit'] = array(
            '#name' => 'submit',
            '#type' => 'submit',
            '#item' => array(
                'name' => 'submit',
                'value' => zerophp_lang('Save'),
            ),
        );

        $form['reset'] = array(
            '#name' => 'reset',
            '#type' => 'reset',
            '#item' => array(
                'name' => 'reset',
                'type' => "reset",
                'value' => zerophp_lang('Reset'),
            ),
        );

        $form['#validate'][] = array(
            'class' => 'users',
            'function' => 'change_pass_form_validate',
        );

        $form['#submit'][] = array(
            'class' => 'users',
            'function' => 'change_pass_form_submit',
        );

        $form['#redirect'] = site_url('user/logout');

        $form_id = 'users-change_pass_form';
        $this->CI->form->form_build($form_id, $form);
        return $form_id;
    }

    function change_pass_form_validate($form_id, $form, &$form_values) {
        $entity = Entity::loadEntityObject('form_validation');
        $this->CI->form_validation->set_rules('password_old', zerophp_lang('Old password'), 'require|min_length[8]|max_length[32]');
        $this->CI->form_validation->set_rules('password_confirm', zerophp_lang('New password confirmation'), 'require');
        $this->CI->form_validation->set_rules('password', zerophp_lang('New password'), 'required|min_length[8]|max_length[32]|matches[password_confirm]');
        if ($this->CI->form_validation->run() == FALSE) {
            return false;
        }

        if (!$this->login_check($this->user->email, $form_values['password_old'])) {
            $this->CI->theme->messages_add(lang('Your old password is not match.'), 'error');
            return false;
        }

        $form_values['password'] = $this->CI->users->password_hash($form_values['password']);

        return true;
    }

    function change_pass_form_submit($form_id, $form, &$form_values) {
        $user = new stdClass();
        $user->user_id = $this->user->user_id;
        $user->password = $form_values['password'];
        $this->entity_save($user);

        $this->CI->theme->messages_add(lang('Your new password was updated successfully.'), 'success');
    }
}