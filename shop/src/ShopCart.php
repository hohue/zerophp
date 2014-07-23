<?php
namespace ZeroPHP\Shop;

use ZeroPHP\ZeroPHP\Entity;

class ShopCart extends Entity {

    function __construct() {
        parent::__construct();

        

        $this->setStructure(array(
            'id' => 'shop_cart_id',
            'name' => 'shop_cart',
            'title' => 'Shop cart',
            'fields' => array(
                'shop_cart_id' => array(
                    'name' => 'shop_cart_id',
                    'title' => 'ID',
                    'type' => 'hidden'
                ),
                'products' => array(
                    'name' => 'products	',
                    'title' => 'products',
                    'type' => 'textarea',
                    'form_hidden' => 1,
                ),
                'created_by' => array(
                    'name' => 'created_by	',
                    'title' => 'tạo b',
                    'type' => 'textarea',
                    'form_hidden' => 1,
                ),
                'created_date' => array(
                    'name' => 'created_date',
                    'title' => 'created_date',
                    'type' => 'input',
                    'widget' => 'date_timestamp',
                    'form_hidden' => 1,
                ),
                'updated_date' => array(
                    'name' => 'updated_date	',
                    'title' => 'updated_date',
                    'type' => 'input',
                    'widget' => 'date_timestamp',
                    'form_hidden' => 1,
                ),
                'active' => array(
                    'name' => 'active',
                    'title' => 'Kích hoạt',
                    'type' => 'radio_build',
                    'options' => array(
                        1 => zerophp_lang('Enable'),
                        0 => zerophp_lang('Disable'),
                    ),
                    'form_hidden' => 1,
                    'default' => 1,
                ),
            )
        )
        );
    }
}