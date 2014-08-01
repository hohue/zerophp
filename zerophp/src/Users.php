<?php 
namespace ZeroPHP\ZeroPHP;

use ZeroPHP\ZeroPHP\Entity;

class Users extends Entity {
    function __construct() {
        $this->setStructure(array(
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
        ));
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

    function login($form_id, $form, &$form_values) {
        if (\Auth::attempt(array(
                'email' => $form_values['email'], 
                'password' => $form_values['password'],
                'active' => 1,
            ), $form_values['remember_me'] == 1 ? true : false)
        ) {
            $user = $this->loadEntityByEmail($form_values['email']);

            zerophp_get_instance()->response->addMessage(zerophp_lang('You have been successfully logged in...'));

            // Update last_activity field
            $user = new \stdClass();
            $user->id = zerophp_userid();
            $user->last_activity = date('Y-m-d H:i:s');
            $this->saveEntity($user);

            // Hack for Responsive File Manager
            //@todo 9 Chuyen den hook_init
            if (session_status() == PHP_SESSION_NONE) {
                session_start();
            }
            $_SESSION['id'] = zerophp_userid();

            return true;
        }

        //@todo 1 Them vao form error message
        zerophp_get_instance()->response->addMessage(zerophp_lang('Login failed. Your password is incorrect OR Your account is not active yet OR Your account was blocked. Please try again later.'), 'error');
        return false;
    }

    function registerFormValidate($form_id, $form, &$form_values) {
        $active = zerophp_variable_get('users register email validation', 1);
        if ($active) {
            $form_values['active'] = 0;
        }
        else {
            $form_values['active'] = 1;
        }

        return true;
    }

    function registerFormSubmit($form_id, $form, &$form_values) {
        if ($form_values['active'] == 0) {
            $activation = Entity::loadEntityObject('ZeroPHP\ZeroPHP\Activation');
            $hash = $activation->setHash($form_values['id'], 'user_register');

            $vars = array(
                'email' => $form_values['email'],
                'active_link' => url("user/activation/$hash"),
            );

            zerophp_mail($form_values['email'], 
                zerophp_lang(zerophp_variable_get('user activation email subject', 'Activation your account')),
                zerophp_view('email_user_activation', $vars)
            );

            \Session::put('user registered email', $form_values['email']);
        }
    }

    function changepassValidate($form_id, $form, &$form_values) {
        $passwd = \Auth::user()->__get('password');

        if (\Hash::check($form_values['password_old'], $passwd)) {
            return true;
        }

        zerophp_get_instance()->response->addMessage(zerophp_lang('Your old password does not match.'), 'error');

        return false;
    }

    function changepassSubmit($form_id, $form, &$form_values){
        $user = $this->loadEntity(zerophp_userid());
        $user->password = $form_values['password'];
        $this->saveEntity($user);

        zerophp_get_instance()->response->addMessage(zerophp_lang('Your password was reset successfully.'));
    }

    function resetValidate($form_id, $form, &$form_values) {
        //@todo 4 get userid from hash
        \Auth::loginUsingId(2);

        return true;
    }

    function activationresendValidate($form_id, $form, &$form_values) {
        //@todo 4 get userid from hash
        \Auth::loginUsingId(2);

        return true;
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