<?php 
use ZeroPHP\ZeroPHP\Theme;

class ShoporderController extends Controller {
    function  order_finalize() {
        $vars = array(
            'cart_id' => isset($_GET['cart_id']) ? intval($_GET['cart_id']) : 0,
        );
        $zerophp->response->addContent('shop_order_order_finalize|shop_order', 'Chờ giao hàng', $vars);
    }
}