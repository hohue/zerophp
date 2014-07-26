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
        if (! \Schema::hasTable('shop')) {
            \Schema::create('shop', function($table) {
                $table->increments('shop_id');
                $table->string('title', 256);
                $table->string('path', 256);
                $table->integer('local_id')->unsigned()->default(0);
                $table->integer('district_id')->unsigned()->default(0);
                $table->string('address', 256)->nullable();
                $table->string('homephone', 32)->nullable();
                $table->string('mobile', 32)->nullable();
                $table->string('website', 128)->nullable();
                $table->string('image', 256)->nullable();
                $table->timestamps();
                $table->integer('created_by')->unsigned();
                $table->boolean('active')->default(0);
                $table->text('paymenth_method')->nullable();
                $table->text('shipmenth_method')->nullable();
                $table->softDeletes();

                $table->index('path');

                $table->foreign('created_by')->references('user_id')->on('users')->onDelete('cascade');
            });
        }

        if (! \Schema::hasTable('shop_cart')) {
            \Schema::create('shop_cart', function($table) {
                $table->increments('shop_cart_id');
                $table->string('session_id', 256);
                $table->integer('shop_id')->unsigned();
                $table->timestamps();
                $table->integer('created_by')->unsigned();
                $table->boolean('active')->default(1);
                $table->softDeletes();

                $table->index('session_id');
                $table->index('shop_id');

                $table->foreign('created_by')->references('user_id')->on('users')->onDelete('cascade');
                $table->foreign('shop_id')->references('shop_id')->on('shop')->onDelete('cascade');
            });
        }

        if (! \Schema::hasTable('shop_product')) {
            \Schema::create('shop_product', function($table) {
                $table->increments('shop_product_id');
                $table->integer('shop_id')->unsigned();
                $table->string('title', 256);
                $table->longText('content')->nullable();
                $table->tinyInteger('label')->nullable();
                $table->integer('price')->default(0);
                $table->integer('promotion')->default(0);
                $table->string('promotion_type', 32)->nullable();
                $table->timestamp('promotion_start');
                $table->timestamp('promotion_end');
                $table->integer('created_by')->unsigned();
                $table->string('image', 256)->nullable();
                $table->timestamps();
                $table->boolean('active')->default(1);
                $table->softDeletes();

                $table->index('shop_id');
                $table->index('price');

                $table->foreign('shop_id')->references('shop_id')->on('shop')->onDelete('cascade');
                $table->foreign('created_by')->references('user_id')->on('users')->onDelete('cascade');
            });
        }

        if (! \Schema::hasTable('shop_cart_shop_product')) {
            \Schema::create('shop_cart_shop_product', function($table) {
                $table->string('field', 32);
                $table->integer('shop_cart_id')->unsigned();
                $table->integer('shop_product_id')->unsigned();

                $table->foreign('shop_cart_id')->references('shop_cart_id')->on('shop_cart')->onDelete('cascade');
                $table->foreign('shop_product_id')->references('shop_product_id')->on('shop_product')->onDelete('cascade');
            });
        }

        if (! \Schema::hasTable('shop_order')) {
            \Schema::create('shop_order', function($table) {
                $table->increments('shop_order_id');
                $table->integer('shop_cart_id')->unsigned();
                $table->tinyInteger('pay_gender');
                $table->string('pay_name', 128);
                $table->string('pay_email', 128);
                $table->string('pay_phone', 32);
                $table->string('pay_address', 256);
                $table->tinyInteger('ship_gender');
                $table->string('ship_name', 128);
                $table->string('ship_email', 128);
                $table->string('ship_phone', 32);
                $table->string('ship_address', 256);
                $table->text('note');
                $table->timestamps();
                $table->integer('created_by')->unsigned();
                $table->boolean('active')->default(1);
                $table->softDeletes();

                $table->index('created_by');

                $table->foreign('shop_cart_id')->references('shop_cart_id')->on('shop_cart')->onDelete('cascade');
                $table->foreign('created_by')->references('user_id')->on('users')->onDelete('cascade');
            });
        }

        if (! \Schema::hasTable('shop_topic')) {
            \Schema::create('shop_topic', function($table) {
                $table->increments('shop_topic_id');
                $table->integer('shop_id')->unsigned();
                $table->string('title', 256);
                $table->string('short_description', 256);
                $table->longText('content');
                $table->integer('price')->default(0);
                $table->string('shipping', 32);
                $table->boolean('is_promotion')->default(0);
                $table->integer('promotion')->default(0);
                $table->string('promotion_type', 32)->nullable();
                $table->timestamp('promotion_start');
                $table->timestamp('promotion_end');
                $table->integer('category_id')->unsigned();
                $table->integer('created_by')->unsigned();
                $table->string('image', 256);
                $table->timestamps();
                $table->boolean('active')->default(1);
                $table->softDeletes();

                $table->index('shop_id');
                $table->index('created_by');
                $table->index('price');

                $table->foreign('shop_id')->references('shop_id')->on('shop')->onDelete('cascade');
                $table->foreign('created_by')->references('user_id')->on('users')->onDelete('cascade');
            });
        }

        if (! \Schema::hasTable('shop_topic_shop_product')) {
            \Schema::create('shop_topic_shop_product', function($table) {
                $table->string('field', 32);
                $table->integer('shop_topic_id')->unsigned();
                $table->integer('shop_product_id')->unsigned();

                $table->foreign('shop_topic_id')->references('shop_topic_id')->on('shop_topic')->onDelete('cascade');
                $table->foreign('shop_product_id')->references('shop_product_id')->on('shop_product')->onDelete('cascade');
            });
        }

        // Add some Blocks
        \DB::table('block')->insert(array(
            array(
                'title' => 'shop_information for topic', 
                'cache_type' => 'page',
                'region' => 'right sidebar',
                'class' => 'ZeroPHP\\Shop\\Shop',
                'method' => 'shop_information', 
                'access' => 'shop_information_access_for_topic',
            ),
            array(
                'title' => 'shop_information for shop', 
                'cache_type' => 'page',
                'region' => 'right sidebar',
                'class' => 'ZeroPHP\\Shop\\Shop',
                'method' => 'shop_information', 
                'access' => 'shop_information_access_for_shop',
            ),
            array(
                'title' => 'shop_topic_warning', 
                'cache_type' => 'full',
                'region' => 'right sidebar',
                'class' => 'ZeroPHP\\Shop\\ShopTopic',
                'method' => 'shop_topic_warning', 
                'access' => 'shop_topic_warning_access',
            ),
        ));

        // Add some menus
        \DB::table('menu')->insert(array(
            array(
                'title' => 'Sop cart items',
                'path' => 'shopcart/items',
                'class' => 'ZeroPHP\\Shop\\ShopcartController',
                'method' => 'showItems',
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