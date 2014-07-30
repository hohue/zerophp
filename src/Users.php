<?php 
namespace ZeroPHP\ZeroPHP;

use ZeroPHP\ZeroPHP\Entity;

class Users extends Entity {
    function __construct() {
        $this->setStructure(array(
            '#id' => 'user_id',
            '#name' => 'users',
            '#class' => 'ZeroPHP\ZeroPHP\Users',
            '#title' => zerophp_lang('Users'),
            '#links' => array(
                //'list' => 'user/users',
            ),
            '#fields' => array(
                'user_id' => array(
                    '#name' => 'user_id',
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
        ));
    }

    function saveEntity($entity) {
        if (empty($entity->password)) {
            unset($entity->password);
        }
        else {
            $entity->password = \Hash::make($entity->password);
        }

        if (!empty($entity->user_id)) {
            $old = $this->loadEntityByEmail($entity->email);
            if (isset($old->email)) {
                \Log::error(zerophp_lang('Can not create this user because this email was exists: %email. Log in %function', array('%email' => $entity->email, '%function' => 'zerophp/zerophp/src/Users::saveEntity')));
                return false;
            }
        }

        return parent::saveEntity($entity);
    }

    function loadEntityByEmail($email, $attributes = array()) {
        $attributes['load_all'] = false;
        $attributes['where']['email'] = $email;
        return reset($this->loadEntityExecutive(null, $attributes));
    }

    function logout() {
        \Auth::logout();
        zerophp_get_instance()->response->addMessage(lang('You are successfully logout.'));

        zerophp_redirect();
    }

    function login($form_id, $form, &$form_values) {
        $structure = $this->getStructure();

        ##### LOGIN #####
        $user = new stdClass();
        if (\Auth::attempt(array('email' => $form_values['email'], 'password' => $form_values['password']), $form_values['remember_me'])) {
            // Update last_activity field
            $user_update = new stdClass();
            $user_update->user_id = $user->user_id;
            $user_update->last_activity = date('Y-m-d H:i:s');
            $this->saveEntity($user_update);

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
                $this->setUser();

                zerophp_get_instance()->response->addMessage(lang('You have been successfully logged in...'));
            }
            elseif ($user->active == 2) {
                zerophp_get_instance()->response->addMessage(lang('Your account was blocked. Please contact the administrator.'), 'error');
            }
            else {
                zerophp_get_instance()->response->addMessage(lang('Your account is not active yet.'), 'error');
            }

            return true;
        }

        zerophp_get_instance()->response->addMessage(lang('Your password is incorrect. Please try again.'), 'error');
        return false;

        // Hack for Responsive File Manager
        //@todo 9 Chuyen den hook_init
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION['user_id'] = zerophp_userid();
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

    function users_create_form_alter($form_id, &$form) {

        array_unshift($form['#validate'], array(
            'class' => 'users',
            'method' => 'users_create_form_validate'
        ));

        $form['password_confirm'] = $form['password'];
        $form['password_confirm']['#label'] = zerophp_lang('Password confirmation');
        $form['password_confirm']['#name'] = 'password_confirm';
        $form['password_confirm']['#id'] = 'fii_password_confirm';
        $form['password_confirm']['#item']['#name'] = 'password_confirm';
        $form['password_confirm']['#item']['id'] = 'fii_password_confirm_field';
        $form['password_confirm']['#item']['data-validate'] = 'password_confirm';
        $form['password_confirm']['#error_messages'] = zerophp_lang('New password confirmation is not match with new password');

        //@todo 9 Move me to chovip module
        $form['accept'] = array(
            '#name' => 'accept',
            '#type' => 'checkbox',
            '#item' => array(
                '#name' => 'accept',
                '#type' => 'checkbox',
                'value' => 1,
                'data-required' => 'true',
            ),
            '#error_messages' => zerophp_lang('You must accept to register your account.'),
            '#description' => zerophp_lang('Accept websites\'s  policy', array(
                '%link1' => \URL::to('e/read/article/3'),
                '%link2' => \URL::to('e/read/article/4'),
            )),
        );
        $form['submit']['#item']['value'] = zerophp_lang('Register');
        $form['#redirect'] = \URL::to('user/register_success');

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

    function forgot_pass_form() {
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
            '#description' => zerophp_lang('Please enter your email exactly as you entered it when registering with our system.'),
            '#error_messages' => zerophp_lang('Invalid email'),
        );

        $form['submit'] = array(
            '#name' => 'submit',
            '#type' => 'submit',
            '#item' => array(
                '#name' => 'submit',
                'value' => zerophp_lang('Send me a new password'),
            ),
        );

        $form['#validate'][] = array(
            'class' => 'users',
            'method' => 'forgot_pass_form_validate',
        );

        $form['#submit'][] = array(
            'class' => 'users',
            'method' => 'forgot_pass_form_submit',
        );

        $form_id = 'users-forgot_pass_form';
        $this->CI->form->form_build($form_id, $form);
        return $form_id;
    }

    function forgot_pass_form_validate($form_id, $form, &$form_values) {
        $entity = Entity::loadEntityObject('form_validation');
        $this->CI->form_validation->set_rules('email', $form['email']['#label'], 'trim|required|email|is_exists[users.email]');
        if ($this->CI->form_validation->run() == FALSE) {
            return false;
        }

        return true;
    }

    function forgot_pass_form_submit($form_id, $form, &$form_values) {
        $user = $this->loadEntityByEmail($form_values['email']);

        $entity = Entity::loadEntityObject('activation');
        $hash = $this->CI->activation->hash_set($user->user_id, 'users_reset_pass');

        // Send Email
        $content = array(
            '#title' => $user->title,
            'link' => \URL::to("activation/users_reset_pass/$hash"),
        );
        $entity = Entity::loadEntityObject('mail');
        $this->CI->mail->send($form_values['email'], fw_variable_get('Activation email template users reset password subject', 'Reset your password'), $content, 'mail_template_activation_users_reset_pass|activation');

        zerophp_get_instance()->response->addMessage(lang('We sent reset password email to your email. Please check your email now.'), 'success');
    }

    function change_pass_form() {
        $form['password_old'] = array(
            '#name' => 'password_old',
            '#type' => 'password',
            '#label' => zerophp_lang('Old password'),
            '#item' => array(
                '#name' => 'password_old',
                '#type' => 'password',
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
                '#name' => 'password',
                '#type' => 'password',
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
                '#name' => 'password_confirm',
                '#type' => 'password',
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
                '#name' => 'submit',
                'value' => zerophp_lang('Save'),
            ),
        );

        $form['reset'] = array(
            '#name' => 'reset',
            '#type' => 'reset',
            '#item' => array(
                '#name' => 'reset',
                '#type' => "reset",
                'value' => zerophp_lang('Reset'),
            ),
        );

        $form['#validate'][] = array(
            'class' => 'users',
            'method' => 'change_pass_form_validate',
        );

        $form['#submit'][] = array(
            'class' => 'users',
            'method' => 'change_pass_form_submit',
        );

        $form['#redirect'] = \URL::to('user/logout');

        $form_id = 'users-change_pass_form';
        $this->CI->form->form_build($form_id, $form);
        return $form_id;
    }

    function change_pass_form_validate($form_id, $form, &$form_values) {
        $entity = Entity::loadEntityObject('form_validation');
        $this->CI->form_validation->set_rules('password_old', zerophp_lang('Old password'), 'require|min:8|max:12');
        $this->CI->form_validation->set_rules('password_confirm', zerophp_lang('New password confirmation'), 'require');
        $this->CI->form_validation->set_rules('password', zerophp_lang('New password'), 'required|min:8|max:12|matches[password_confirm]');
        if ($this->CI->form_validation->run() == FALSE) {
            return false;
        }

        if (!$this->login_check($this->user->email, $form_values['password_old'])) {
            zerophp_get_instance()->response->addMessage(lang('Your old password is not match.'), 'error');
            return false;
        }

        $form_values['password'] = $this->CI->users->password_hash($form_values['password']);

        return true;
    }

    function change_pass_form_submit($form_id, $form, &$form_values) {
        $user = new stdClass();
        $user->user_id = $this->user->user_id;
        $user->password = $form_values['password'];
        $this->saveEntity($user);

        zerophp_get_instance()->response->addMessage(lang('Your new password was updated successfully.'), 'success');
    }
}