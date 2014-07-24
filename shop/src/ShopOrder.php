<?php
namespace ZeroPHP\Shop;

use ZeroPHP\ZeroPHP\Entity;

class ShopOrder extends Entity {

    function __construct() {
        $this->setStructure(array(
            'id' => 'shop_order_id',
            'name' => 'shop_order',
            'class' => 'ZeroPHP\Shop\ShopOrder',
            'title' => 'Shop order',
            'fields' => array(
                'shop_order_id' => array(
                    'name' => 'shop_order_id',
                    'title' => 'ID',
                    'type' => 'hidden'
                ),
                'payinfo_gender' => array(
                    'name' => 'payinfo_gender	',
                    'title' => 'giới tính',
                    'type' => 'checkbox_build',
                ),
                'pay_name' => array(
                    'name' => 'pay_name	',
                    'title' => 'Họ & Tên',
                    'type' => 'input',
                    'placeholder' => 'Họ & Tên',
                ),
                'pay_email' => array(
                    'name' => 'pay_email	',
                    'title' => 'Email',
                    'type' => 'input',
                    'placeholder' => 'Email',
                ),
                'pay_phone' => array(
                    'name' => 'pay_phone	',
                    'title' => 'Điện Thoại',
                    'type' => 'input',
                    'placeholder' => 'Điện Thoại',
                ),
                'pay_address' => array(
                    'name' => 'pay_address	',
                    'title' => 'Địa Chỉ',
                    'type' => 'input',
                    'placeholder' => 'Địa Chỉ',
                ),
                
                
                'shipinfo_gender' => array(
                    'name' => 'payinfo_gender	',
                    'title' => 'giới tính',
                    'type' => 'checkbox_build',
                ),
                'ship_name' => array(
                    'name' => 'pay_name	',
                    'title' => 'Họ & Tên',
                    'type' => 'input',
                    'placeholder' => 'Họ & Tên',
                ),
                'ship_email' => array(
                    'name' => 'pay_email	',
                    'title' => 'Email',
                    'type' => 'input',
                    'placeholder' => 'Email',
                ),
                'ship_phone' => array(
                    'name' => 'pay_phone	',
                    'title' => 'Điện Thoại',
                    'type' => 'input',
                    'placeholder' => 'Điện Thoại',
                ),
                'ship_address' => array(
                    'name' => 'pay_address	',
                    'title' => 'Địa Chỉ',
                    'type' => 'input',
                    'placeholder' => 'Địa Chỉ',
                ),
                'note' => array(
                    'name' => 'note	',
                    'title' => 'GHI CHÚ THÊM',
                    'type' => 'input',
                    'placeholder' => 'GHI CHÚ THÊM',
                ),
            )
        )
        );
    }
}