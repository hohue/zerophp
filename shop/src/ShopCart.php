<?php
namespace ZeroPHP\Shop;

use ZeroPHP\ZeroPHP\Entity;
use ZeroPHP\ZeroPHP\EntityInterface;

class ShopCart extends Entity implements EntityInterface {
    function __config() {
        return array(
            '#id' => 'shop_cart_id',
            '#name' => 'shop_cart',
            '#class' => 'ZeroPHP\Shop\ShopCart',
            '#title' => 'Shop cart',
            '#fields' => array(
                'shop_cart_id' => array(
                    '#name' => 'shop_cart_id',
                    '#title' => 'ID',
                    '#type' => 'hidden'
                ),
                'products' => array(
                    '#name' => 'products	',
                    '#title' => 'Products',
                    '#type' => 'textarea',
                    '#form_hidden' => 1,
                ),
                'created_by' => array(
                    '#name' => 'created_by	',
                    '#title' => 'tạo b',
                    '#type' => 'textarea',
                    '#form_hidden' => 1,
                ),
                'created_at' => array(
                    '#name' => 'created_at',
                    '#title' => 'created_at',
                    '#type' => 'text',
                    '#widget' => 'date_timestamp',
                    '#form_hidden' => 1,
                ),
                'updated_at' => array(
                    '#name' => 'updated_at	',
                    '#title' => 'updated_at',
                    '#type' => 'text',
                    '#widget' => 'date_timestamp',
                    '#form_hidden' => 1,
                ),
                'active' => array(
                    '#name' => 'active',
                    '#title' => 'Kích hoạt',
                    '#type' => 'radios',
                    '#options' => array(
                        1 => zerophp_lang('Enable'),
                        0 => zerophp_lang('Disable'),
                    ),
                    '#form_hidden' => 1,
                    '#default' => 1,
                ),
            )
        );
    }

    function showItems($zerophp) {
        $vars = array(
            'items' => 0,
        );
        $zerophp->response->addContent(zerophp_view('shop_cart_items', $vars));
    }
}