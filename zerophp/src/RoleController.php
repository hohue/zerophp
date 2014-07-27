<?php
namespace ZeroPHP\ZeroPHP;

class RoleController{


    
    function index() {
        redirect('admin/e/index/roles');
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
        $zerophp->response->addContent('admin_roles_permissions', zerophp_lang('Users permissions'), $vars);
    }
}
