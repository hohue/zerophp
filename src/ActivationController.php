<?php 
namespace ZeroPHP\ZeroPHP;

class ActivationController {





    
    function users($hash) {
        $this->load->library('activation');
        if ($this->activation->active_users($hash)) {
            $this->lang->load('activation', config_item('language'));
            $items = array(
                0 => array(
                    'item' => zerophp_lang('authentication information')
                )
            );
            $zerophp->response->addBreadcrumb($items);
            $zerophp->response->addContent('activation_success|activation', zerophp_lang('success') );
        }
        else {
            $this->response->messages_add(lang('Your activation code is not match or has expired.'), 'error');
            redirect();
        }
    }

    function users_resend() {
        $this->load->library('activation');
        $vars = array(
            'form_id' => $this->activation->resend_users_form(),
        );
        $zerophp->response->addContent('activation_users_resend|activation', zerophp_lang('Resend activation email'), $vars);
    }

    function users_reset_pass($hash) {
        $this->load->library('activation');
        $vars = array(
            'form_id' => $this->activation->users_reset_pass_form($hash),
        );
        $zerophp->response->addContent('activation_users_reset_pass|activation', zerophp_lang('Reset a new password'), $vars);
    }
}
