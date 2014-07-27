<?php
namespace ZeroPHP\Shop;

class ShopcartController {
    function showItems($zerophp) {
        $vars = array(
            'items' => 0,
        );
        $zerophp->response->addContent(zerophp_view('shop_cart_items', $vars));
    }
}