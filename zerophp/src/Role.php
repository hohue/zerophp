<?php 
namespace ZeroPHP\ZeroPHP;

use ZeroPHP\ZeroPHP\Entity;
use ZeroPHP\ZeroPHP\EntityInterface;

class Role extends Entity implements  EntityInterface {
    public function __config() {
        return array(
            '#name' => 'role',
            '#class' => 'ZeroPHP\ZeroPHP\Role',
            '#title' => zerophp_lang('Roles'),
            '#id' => 'role_id',
            '#fields' => array(
                'role_id' => array(
                    '#name' => 'role_id',
                    '#title' => zerophp_lang('ID'),
                    '#type' => 'hidden',
                ),
                'title' => array(
                    '#name' => 'title',
                    '#title' => zerophp_lang('Role title'),
                    '#type' => 'text',
                    '#validate' => 'required|max_length[80]'
                ),
                'active' => array(
                    '#name' => 'active',
                    '#title' => zerophp_lang('Active'),
                    '#type' => 'radios',
                    '#options' => array(
                        1 => zerophp_lang('Enable'),
                        0 => zerophp_lang('Disable'),
                    ),
                    '#default' => 0,
                    '#validate' => 'required|numeric|greater_than[-1]|less_than[2]',
                ),
                'weight' => array(
                    '#name' => 'weight',
                    '#title' => zerophp_lang('Weight'),
                    '#type' => 'select_build',
                    '#options' => form_options_make_weight(),
                    '#default' => 0,
                    '#validate' => 'required|numeric|between:-999,999',
                    '#fast_edit' => 1,
                ),
            ),
            '#can_not_delete' => array(1, 2, 3, 4),
        );
    }

    function permissions_form($roles, $permissions, $access) {
        $form = array();
        $form_values = array();
        $field = array(
            '#type' => 'checkbox',
        );

        foreach ($permissions as $permission) {
            $perms_and = explode('&&', $permission->access_key);
            $perms = array();
            foreach ($perms_and as $perms_key => $perms_or) {
                $perms = array_merge($perms, explode('||', $perms_or));
            }

            foreach ($perms as $perm) {
                foreach ($roles as $role_id => $role) {
                    $field['#name'] = "perm:$perm:$role_id";
                    $field['value'] = 1;
                    $field['checked'] = !empty($access[$perm][$role_id]) && $access[$perm][$role_id] == 1 ? true : false;
                    $form[$field['#name']] = $this->CI->form->form_item_generate($field);
                    $form_values[$field['#name']] = 1;
                }
            }
        }

        if (count($form)) {
            $form['submit'] = array(
                '#name' => 'submit',
                '#type' => 'submit',
                '#item' => array(
                    '#name' => 'submit',
                    'value' => zerophp_lang('Save Configuration'),
                ),
            );

            $form['#submit'][] = array(
                'class' => 'roles',
                'method' => 'permissions_form_submit',
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
        zerophp_get_instance()->response->addMessage(zerophp_lang('Your data was updated successfully.'));
    }

    function access_check($path = null, $id = null, $access_key = array()) {
        $id = $id ? $id : zerophp_userid();$user = $this->CI->users->loadEntity($id);

        // true for user 1 (super admin)
        if ($id == 1) {
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

        $user = $this->CI->users->loadEntity($id);
        $roles = array_keys($user->roles);

        $result = true;
        // AND
        foreach ($access_key as $access_group) {
            // OR
            foreach ($access_group as $key) {
                $access = $this->access_get_list($key);

                if (count(array_intersect($roles, array_keys($access)))) {
                    $entity = new \ZeroPHP\ZeroPHP\PermsFunc;
                    $func = $this->CI->perms_func->loadEntity_from_access_key($key);
                    if (!empty($func->library) && !empty($func->function)) {
                        $entity = new $func->class;
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
        if ($cache = \Cache::get($cache_name)) {
            return $cache;
        }

        $entity = new \ZeroPHP\ZeroPHP\Perms;
        $access_key = '';
        while (count($path) && !$access_key) {
            $entity = $this->CI->perms->loadEntity_from_path(implode('/', $path));

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

        \Cache::forever($cache_name, $access_key);
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

    function permissions() {
        $roles = $this->roles->loadEntityAll(array('cache' => false));
        $access = $this->roles->access_get_list('', false);

        $this->load->library('perms');
        $permissions = $this->perms->loadEntityAll(array('cache' => false));

        $vars = array(
            'form_id' => $this->roles->permissions_form($roles, $permissions, $access),
            'roles' => $roles,
            'permissions' => $permissions,
            'access' => $access,
        );
        $zerophp->response->addContent(zerophp_view('admin_roles_permissions', $vars));
    }

}