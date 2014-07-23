<?php 
use ZeroPHP\ZeroPHP\Theme;

class TplController extends Controller {
    function message() {
        $vars = array(
            'messages' => $this->response->messages_get(),
        );
        $zerophp->response->addContent('tpl_message', '', $vars);
    }
}