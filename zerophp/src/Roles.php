<?php 
namespace ZeroPHP\ZeroPHP;

use ZeroPHP\ZeroPHP\Entity;

class Roles extends Entity {

    function __construct() {
        parent::__construct();

        

        $this->setStructure(array(
            'name' => 'roles',
            'title' => zerophp_lang('Roles'),
            'id' => 'role_id',
            'fields' => array(
                'role_id' => array(
                    'name' => 'role_id',
                    'title' => zerophp_lang('ID'),
                    'type' => 'hidden',
                ),
                'title' => array(
                    'name' => 'title',
                    'title' => zerophp_lang('Role title'),
                    'type' => 'input',
                    'validate' => 'required|max_length[80]'
                ),
                'active' => array(
                    'name' => 'active',
                    'title' => zerophp_lang('Active'),
                    'type' => 'radio_build',
                    'options' => array(
                        1 => zerophp_lang('Enable'),
                        0 => zerophp_lang('Disable'),
                    ),
                    'default' => 0,
                    'validate' => 'required|numeric|greater_than[-1]|less_than[2]',
                ),
                'weight' => array(
                    'name' => 'weight',
                    'title' => zerophp_lang('Weight'),
                    'type' => 'dropdown_build',
                    'options' => form_options_make_weight(),
                    'default' => 0,
                    'validate' => 'required|numeric|greater_than[-100]|less_than[100]',
                    'fast_edit' => 1,
                ),
            ),
            'can_not_delete' => array(1, 2, 3, 4),
        ));
    }

    function permissions_form($roles, $permissions, $access) {
        $form = array();
        $form_values = array();
        $field = array(
            'type' => 'checkbox',
        );

        foreach ($permissions as $permission) {
            $perms_and = explode('&&', $permission->access_key);
            $perms = array();
            foreach ($perms_and as $perms_key => $perms_or) {
                $perms = array_merge($perms, explode('||', $perms_or));
            }

            foreach ($perms as $perm) {
                foreach ($roles as $role_id => $role) {
                    $field['name'] = "perm:$perm:$role_id";
                    $field['value'] = 1;
                    $field['checked'] = !empty($access[$perm][$role_id]) && $access[$perm][$role_id] == 1 ? true : false;
                    $form[$field['name']] = $this->CI->form->form_item_generate($field);
                    $form_values[$field['name']] = 1;
                }
            }
        }

        if (count($form)) {
            $form['submit'] = array(
                '#name' => 'submit',
                '#type' => 'submit',
                '#item' => array(
                    'name' => 'submit',
                    'value' => zerophp_lang('Save Configuration'),
                ),
            );

            $form['#submit'][] = array(
                'class' => 'roles',
                'function' => 'permissions_form_submit',
            );

            $form['#redirect'] = 'admin/roles/permissions';
        }

        $form_id = "ZUsers-permissions";
        $this->CI->form->form_build($form_id, $form, $form_values, false);
        return $form_id;
    }

    function permissions_form_submit($form_id, $form, &$form_values) {
        $data = array();
        foreach ($form_values as $key => $value) {
            if ($value && substr($key, 0, 5) == 'perm:') {
                $key = explode(':', $key);
                $data[] = array(
                    'role_id' => $key[2],
                    'access_key' => $key[1],
                    'access_value' => 1,
                );
            }
        }

        $this->CI->load->model('roles_model');
        $this->CI->roles_model->access_set_list($data);
        $this->CI->cachef->del_system('users-access_get_list');
        $this->CI->theme->messages_add(lang('Your data was updated successfully.'));
    }

    function access_check($path = null, $user_id = null, $access_key = array()) {
        $user_id = $user_id ? $user_id : zerophp_user_current();$user = $this->CI->users->entity_load($user_id);

        // true for user 1 (super admin)
        if ($user_id == 1) {
            return true;
        }

        $path = $path ? $path : \URL::current();
        $path = explode('/', $path);
        switch ($path[0]) {
            case 'admin':
                array_shift($path);
                $path[0] = !empty($path[0]) ? $path[0] : 'zdashboard';
                $path[1] = isset($path[1]) && $path[1] ? $path[1] : 'index';
                $access_key[] = array('access_admin_control_panel');
                break;

            case 'up':
                $access_key[] = array('access_user_control_panel');
            case 'ajax':
            case 'esi':
                array_shift($path);
            default:
                $home = fw_variable_get('url page home', 'dashboard');
                $home = explode('/', $home);
                for ($i = 0; $i < count($home); $i++) {
                    $path[$i] = !empty($path[$i]) ? $path[$i] : $home[$i];
                }
                $path[1] = !empty($path[1]) ? $path[1] : 'index';
                break;
        }

        $access_key = array_merge($access_key, $this->_access_get_key($path));

        // Don't have access key => Don't need to check
        if (!count($access_key)) {
            return true;
        }

        $user = $this->CI->users->entity_load($user_id);
        $roles = array_keys($user->roles);

        $result = true;
        // AND
        foreach ($access_key as $access_group) {
            // OR
            foreach ($access_group as $key) {
                $access = $this->access_get_list($key);

                if (count(array_intersect($roles, array_keys($access)))) {
                    $entity = Entity::loadEntityObject('perms_func');
                    $func = $this->CI->perms_func->entity_load_from_access_key($key);
                    if (!empty($func->library) && !empty($func->function)) {
                        $entity = Entity::loadEntityObject($func->library);
                        $result = $this->CI->{$func->library}->{$func->function}();
                    }
                    else {
                        $result = true;
                    }

                    // Because "OR"
                    if ($result) {
                        break;
                    }
                }

                $result = false;
            }

            // Because "AND"
            if (!$result) {
                break;
            }
        }

        return $result;
    }

    private function _access_get_key($path) {
        // Support for 3 first element only
        $path = array_slice($path, 0, 3);

        $cache_name ="Roles-_access_get_key-" . serialize($path);
        if ($cache = $this->CI->cachef->get_file($cache_name)) {
            return $cache;
        }

        $entity = Entity::loadEntityObject('perms');
        $access_key = '';
        while (count($path) && !$access_key) {
            $entity = $this->CI->perms->entity_load_from_path(implode('/', $path));

            if (isset($entity->access_key)) {
                $access_key = $entity->access_key;
            }
            else {
                array_pop($path);
            }
        }
        $access_key = $access_key ? explode("&&", $access_key) : array();

        if (count($access_key)) {
            foreach ($access_key as $key => $val) {
                $access_key[$key] = explode("||", $val);
            }
        }

        $this->CI->cachef->set_file($cache_name, $access_key);
        return $access_key;
    }

    function access_get_list($access_key = '', $cache = true) {
        // Get from cache
        $cache_content = '';
        if ($cache) {
            $cache_content = \Cache::get('Roles-access_get_list');
        }

        // Get from database
        if (!$cache_content) {
            $this->CI->load->model('roles_model');
            $cache_content = $this->CI->roles_model->access_get_list($access_key);

            // Set to cache
            if ($cache) {
                \Cache::forever('Roles-access_get_list', $cache_content);
            }
        }

        if ($access_key) {
            if (isset($cache_content[$access_key])) {
                return $cache_content[$access_key];
            }
        }
        else {
            return $cache_content;
        }

        return array();
    }
}