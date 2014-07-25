<?php 
namespace ZeroPHP\Shop;

define('VERSION_ZEROPHP_SHOP', 0.01);

class ShopInstall {
    public static function up($prev_version) {
        if ($prev_version < VERSION_ZEROPHP_SHOP && VERSION_ZEROPHP_SHOP <= 0.01) {
            self::up_0_01();
        }
    }

    public static function down($prev_version) {
        if ($prev_version < VERSION_ZEROPHP_SHOP && VERSION_ZEROPHP_SHOP <= 0.01) {
            self::down_0_01();
        }
    }

    private static function up_0_01() {
        // Add some Blocks
        \DB::table('block')->insert(array(
            array(
                'title' => 'shop_information for topic', 
                'cache_type' => 'page',
                'region' => 'right sidebar', 
                'content' => '', 
                'class' => 'ZeroPHP\\Shop\\Shop',
                'method' => 'shop_information', 
                'access' => 'shop_information_access_for_topic',
                'weight' => 0, 
                'active' => 1,
            ),
            array(
                'title' => 'shop_information for shop', 
                'cache_type' => 'page',
                'region' => 'right sidebar', 
                'content' => '', 
                'class' => 'ZeroPHP\\Shop\\Shop',
                'method' => 'shop_information', 
                'access' => 'shop_information_access_for_shop',
                'weight' => 0, 
                'active' => 1,
            ),
            array(
                'title' => 'shop_topic_warning', 
                'cache_type' => 'full',
                'region' => 'right sidebar', 
                'content' => '', 
                'class' => 'ZeroPHP\\Shop\\ShopTopic',
                'method' => 'shop_topic_warning', 
                'access' => 'shop_topic_warning_access',
                'weight' => 0, 
                'active' => 1,
            ),
        ));

        // Add some menus
        \DB::table('menu')->insert(array(
            array(
                'title' => 'Sop cart items', 
                'cache' => '',
                'path' => 'shopcart/items',
                'class' => 'ZeroPHP\\Shop\\ShopcartController',
                'method' => 'showItems',
                'access' => '',
                'weight' => 0, 
                'active' => 1,
            ),
        ));
    }

    private static function down_0_01() {
        // Remove Blocks
        /*\DB::table('block')
            ->where('class', '=', 'ZeroPHP\\Shop\\Shop')
            ->where('method', '=', 'shop_information')
            ->delete();
        \DB::table('block')
            ->where('class', '=', 'ZeroPHP\\Shop\\ShopTopic')
            ->where('method', '=', 'shop_topic_warning')
            ->delete();*/
    }
}