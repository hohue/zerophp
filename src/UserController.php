<?php 
use ZeroPHP\ZeroPHP\Theme;

class UserController extends Controller {
    function index() {
        redirect('e/read/users/' . zerophp_user_current());
    }

    function login() {
        $vars = array(
            'form_id' => $this->users->login_form(),
        );
        $zerophp->response->addContent('users_login', zerophp_lang('Login'), $vars);
    }

    function logout() {
        $this->users->logout();
        redirect(!empty($_GET['destination']) ? trim($_GET['destination']) : '');
    }

    function forgot_pass() {
        $vars = array(
            'form_id' => $this->users->forgot_pass_form(),
        );
        $zerophp->response->addContent('users_forgot_pass', zerophp_lang('Forgot Pass'), $vars);
    }

    function changepass() {
        $vars = array(
            'form_id' => $this->users->change_pass_form(),
        );
        $zerophp->response->addContent('users_change_pass', zerophp_lang('Change Password'), $vars);
    }

    function register_success() {
        $items = array(
            0 => array(
                'item' => zerophp_lang('User register'),
            )
        );
        $this->response->breadcrumbs_add($items);
        $zerophp->response->addContent('register_success', zerophp_lang('Register success'), '');
    }
}
