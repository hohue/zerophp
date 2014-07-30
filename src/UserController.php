<?php 
namespace ZeroPHP\ZeroPHP;

use ZeroPHP\ZeroPHP\Entity;
use ZeroPHP\ZeroPHP\Form;

class UserController {
    private $fields;

    private function _unsetFormItem(&$form) {
        unset($form['active'], $form['remember_token'], $form['last_activity'],
            $form['created_at'], $form['updated_at'], $form['deleted_at']);
    }

    function showRegisterForm($zerophp) {
        $user = Entity::loadEntityObject('ZeroPHP\ZeroPHP\Users');
        $form = $user->crudCreateForm();
        $this->_unsetFormItem($form);
        unset($form['user_id'], $form['roles']);

        // Validate email unique
        $form['email']['#validate'] .= '|unique:users,email';

        // Add password confirmation field
        $form['password_confirm'] = $form['password'];
        $form['password_confirm']['#title'] = zerophp_lang('Password confirmation');
        $form['password_confirm']['#name'] = 'password_confirm';
        $form['password_confirm']['#attributes']['data-validate'] = 'password_confirm';
        $form['password_confirm']['#error_messages'] = zerophp_lang('New password confirmation is not match with new password');

        $form['#actions']['submit']['#value'] = zerophp_lang('Register');

        $form['#redirect'] = 'user/register/success';

        $zerophp->response->addContent(Form::build($form));
    }

    function showRegisterSuccess($zerophp) {
        $items = array(
            array(
                '#item' => zerophp_lang('User register')
            )
        );
        $zerophp->response->setBreadcrumb($items);

        $vars = array();
        $zerophp->response->addContent(zerophp_view('users_register_success', $vars));
    }

    function showLoginForm($zerophp) {
        $form = array();
        $user = Entity::loadEntityObject('ZeroPHP\ZeroPHP\Users');
        $structure = $user->getStructure();

        $form['email'] = $structure['#fields']['email'];
        $form['password'] = $structure['#fields']['password'];

        $form['email']['#validate'] .= '|exists:users,email';

        $form['#submit'] = array(
            array(
                'class' => 'ZeroPHP\ZeroPHP\Users',
                'method' => 'login',
            ),
        );

        $zerophp->response->addContent(Form::build($form));
    }







    function logout() {
        $this->users->logout();
        return \Redirect::to(!empty($zerophp->request->query('destination')) ? trim($zerophp->request->query('destination')) : '');
    }

    function forgot_pass() {
        $vars = array(
            'form_id' => $this->users->forgot_pass_form(),
        );
        $zerophp->response->addContent(zerophp_view('users_forgot_pass', $vars));
    }

    function changepass() {
        $vars = array(
            'form_id' => $this->users->change_pass_form(),
        );
        $zerophp->response->addContent(zerophp_view('users_change_pass', $vars));
    }
}
