<?php
namespace ZeroPHP\Shop;

use ZeroPHP\ZeroPHP\Entity;

class ShopProduct extends Entity {

    function __construct() {
        $this->setStructure(array(
            '#id' => 'shop_product_id',
            '#name' => 'shop_product',
            '#class' => 'ZeroPHP\Shop\ShopProduct',
            '#title' => 'Shop product',
            '#fields' => array(
                'shop_product_id' => array(
                    '#name' => 'shop_product_id',
                    '#title' => 'ID',
                    '#type' => 'hidden'
                ),
                'title' => array(
                    '#name' => 'title',
                    '#title' => 'Tên sản phẩm',
                    '#type' => 'text',
                    '#validate' => 'required',
                    '#attributes' => array(
                        'placeholder' => 'Tên sản phẩm',
                    ),
                ),
                'content' => array(
                    '#name' => 'content',
                    '#title' => 'Mô tả ngắn',
                    '#type' => 'textarea',
                    '#validate' => 'required',
                    '#attributes' => array(
                        'placeholder' => 'Mô tả ngắn',
                    ),
                ),
                'label' => array(
                    '#name' => 'label',
                    '#title' => 'Số thứ tự',
                    '#type' => 'text',
                    '#validate' => 'required',
                ),
                'price' => array(
                    '#name' => 'price',
                    '#title' => 'Giá Gốc',
                    '#type' => 'text',
                    '#validate' => 'required|numeric',
                    '#attributes' => array(
                        'placeholder' => 'Giá Gốc',
                    ),
                ),
                'promotion' => array(
                    '#name' => 'promotion',
                    '#title' => 'Giá Bán',
                    '#type' => 'text',
                    '#attributes' => array(
                        'placeholder' => 'Giá Bán',
                    ),
                ),
                'promotion_type' => array(
                    '#name' => 'promotion_type',
                    '#title' => 'Kiểu Khuyến Mãi',
                    '#type' => 'select_build',
                    '#options' => array(
                        1 => 'Phần trăm',
                        2 => 'Giá trị',
                    ),
                    '#form_hidden' => 1,
                ),
                'promotion_start' => array(
                    '#name' => 'promotion_start',
                    '#title' => 'Thời Gian Khuyến Mãi',
                    '#type' => 'text',
                    '#form_hidden' => 1,
                ),
                'promotion_end' => array(
                    '#name' => 'promotion_end',
                    '#title' => 'đến',
                    '#type' => 'text',
                    '#form_hidden' => 1,
                ),
                'created_by' => array(
                    '#name' => 'created_by',
                    '#title' => 'Tạo bởi',
                    '#type' => 'text',
                    '#validate' => 'required',
                    '#widget' => 'date_timestamp',
                    '#form_hidden' => 1,
                ),
                'image' => array(
                    '#name' => 'image',
                    '#title' => 'Hình Ảnh',
                    '#type' => 'upload',
                    '#widget' => 'image',
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
        )
        );
    }
}