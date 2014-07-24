<?php 
use ZeroPHP\ZeroPHP;

class ResponseController {
    function message($zerophp) {
        $vars = array(
            'messages' => $zerophp->response->getMessage(),
        );
        $zerophp->response->addContent('response_message', '', $vars);
    }
}