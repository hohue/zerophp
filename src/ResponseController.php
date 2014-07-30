<?php 
namespace ZeroPHP\ZeroPHP;

class ResponseController {
    function showMessage($zerophp) {
        $vars = array(
            'messages' => zerophp_message(),
        );
        $zerophp->response->addContent(zerophp_view('response_message', $vars));
    }
}