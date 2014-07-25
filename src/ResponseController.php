<?php 
use ZeroPHP\ZeroPHP;

class ResponseController {
    function showMessage($zerophp) {
        $vars = array(
            'messages' => $zerophp->response->getMessage(),
        );
        $zerophp->response->addContent(zerophp_view(('response_message', $vars));
    }
}