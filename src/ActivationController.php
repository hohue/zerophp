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
            $zerophp->response->addContent(zerophp_view('activation_success'));
        }
        else {
            $zerophp->addMessage(lang('Your activation code is not match or has expired.'), 'error');
            return \Redirect::to();
        }
    }

    function users_resend() {
        $this->load->library('activation');
        $vars = array(
            'form_id' => $this->activation->resend_users_form(),
        );
        $zerophp->response->addContent(zerophp_view('activation_users_resend'$vars));
    }

    function users_reset_pass($hash) {
        $this->load->library('activation');
        $vars = array(
            'form_id' => $this->activation->users_reset_pass_form($hash),
        );
        $zerophp->response->addContent(zerophp_view('activation_users_reset_pass', $vars));
    }
}
