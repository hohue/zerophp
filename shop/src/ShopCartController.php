<?php
use ZeroPHP\ZeroPHP\Theme;

class ShopcartController extends Controller {
    function showItems() {
        $vars = array(
            'items' => 0,
        );
        $zerophp->response->addContent(zerophp_view('shop_cart_items', $vars));
    }
}