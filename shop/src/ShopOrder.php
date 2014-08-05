<?php
namespace ZeroPHP\Shop;

use ZeroPHP\ZeroPHP\Entity;
use ZeroPHP\ZeroPHP\EntityInterface;

class ShopOrder extends Entity implements EntityInterface {

    function __config() {
        return array(
            '#id' => 'shop_order_id',
            '#name' => 'shop_order',
            '#class' => 'ZeroPHP\Shop\ShopOrder',
            '#title' => 'Shop order',
            '#fields' => array(
                'shop_order_id' => array(
                    '#name' => 'shop_order_id',
                    '#title' => 'ID',
                    '#type' => 'hidden'
                ),
                'pay_gender' => array(
                    '#name' => 'payinfo_gender	',
                    '#title' => 'giới tính',
                    '#type' => 'checkboxes',
                ),
                'pay_name' => array(
                    '#name' => 'pay_name	',
                    '#title' => 'Họ & Tên',
                    '#type' => 'text',
                    '#attributes' => array(
                        'placeholder' => 'Họ & Tên',
                    ),
                ),
                'pay_email' => array(
                    '#name' => 'pay_email	',
                    '#title' => 'Email',
                    '#type' => 'text',
                    '#attributes' => array(
                        'placeholder' => 'Email',
                    ),
                ),
                'pay_phone' => array(
                    '#name' => 'pay_phone	',
                    '#title' => 'Điện Thoại',
                    '#type' => 'text',
                    '#attributes' => array(
                        'placeholder' => 'Điện Thoại',
                    ),
                ),
                'pay_address' => array(
                    '#name' => 'pay_address	',
                    '#title' => 'Địa Chỉ',
                    '#type' => 'text',
                    '#attributes' => array(
                        'placeholder' => 'Địa Chỉ',
                    ),
                ),

                'ship_gender' => array(
                    '#name' => 'payinfo_gender	',
                    '#title' => 'giới tính',
                    '#type' => 'checkboxes',
                ),
                'ship_name' => array(
                    '#name' => 'pay_name	',
                    '#title' => 'Họ & Tên',
                    '#type' => 'text',
                    '#attributes' => array(
                        'placeholder' => 'Họ & Tên',
                    ),
                ),
                'ship_email' => array(
                    '#name' => 'pay_email	',
                    '#title' => 'Email',
                    '#type' => 'text',
                    '#attributes' => array(
                        'placeholder' => 'Email',
                    ),
                ),
                'ship_phone' => array(
                    '#name' => 'pay_phone	',
                    '#title' => 'Điện Thoại',
                    '#type' => 'text',
                    '#attributes' => array(
                        'placeholder' => 'Điện Thoại',
                    ),
                ),
                'ship_address' => array(
                    '#name' => 'pay_address	',
                    '#title' => 'Địa Chỉ',
                    '#type' => 'text',
                    '#attributes' => array(
                        'placeholder' => 'Địa Chỉ',
                    ),
                ),
                'note' => array(
                    '#name' => 'note	',
                    '#title' => 'GHI CHÚ THÊM',
                    '#type' => 'text',
                    '#attributes' => array(
                        'placeholder' => 'GHI CHÚ THÊM',
                    ),
                ),
            )
        );
    }

    function  orderFinalize() {
        $vars = array(
            'cart_id' => isset($_GET['cart_id']) ? intval($_GET['cart_id']) : 0,
        );
        $zerophp->response->addContent(zerophp_view('shop_order_order_finalize', $vars));
    }
}