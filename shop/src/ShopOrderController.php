<?php 
namespace ZeroPHP\Shop;

class ShoporderController {
    function  orderFinalize() {
        $vars = array(
            'cart_id' => isset($_GET['cart_id']) ? intval($_GET['cart_id']) : 0,
        );
        $zerophp->response->addContent(zerophp_view('shop_order_order_finalize', $vars));
    }
}

//Checked