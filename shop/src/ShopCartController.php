<?php
use ZeroPHP\ZeroPHP\Theme;

class ShopcartController extends Controller {
    function items() {
        $vars = array(
            'items' => 0,
        );
        $zerophp->response->addContent('shop_cart_items|shop_cart', '', $vars);
    }
}