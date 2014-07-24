<?php
use ZeroPHP\ZeroPHP\Theme;

class RolesController extends Controller {
    function index() {
        redirect('admin/e/index/roles');
    }

    function permissions() {
        $roles = $this->roles->loadEntity_all(array('cache' => false));
        $access = $this->roles->access_get_list('', false);

        $this->load->library('perms');
        $permissions = $this->perms->loadEntity_all(array('cache' => false));

        $vars = array(
            'form_id' => $this->roles->permissions_form($roles, $permissions, $access),
            'roles' => $roles,
            'permissions' => $permissions,
            'access' => $access,
        );
        $zerophp->response->addContent('admin_roles_permissions', zerophp_lang('Users permissions'), $vars);
    }
}
