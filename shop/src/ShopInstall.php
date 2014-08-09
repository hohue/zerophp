<?php 
namespace ZeroPHP\Shop;

define('VERSION_ZEROPHP_SHOP', 0.01);

class ShopInstall {
    public static function up($prev_version) {
        if ($prev_version < 0.01) {
            self::up_0_01();
        }
    }

    public static function down($prev_version) {
        if ($prev_version < 0.01) {
            self::down_0_01();
        }
    }

    private static function up_0_01() {
        if (! \Schema::hasTable('shop')) {
            \Schema::create('shop', function($table) {
                $table->increments('shop_id');
                $table->string('title', 256);
                $table->string('path', 256);
                $table->integer('province_id')->unsigned()->nullable();
                $table->integer('district_id')->unsigned()->nullable();
                $table->string('address', 256)->nullable();
                $table->string('homephone', 32)->nullable();
                $table->string('mobile', 32)->nullable();
                $table->string('website', 128)->nullable();
                $table->string('image', 256)->nullable();
                $table->dateTime('created_at')->nullable();
                $table->dateTime('updated_at')->nullable();
                $table->integer('created_by')->unsigned()->nullable();
                $table->integer('updated_by')->unsigned()->nullable();
                $table->boolean('active')->default(0);
                $table->text('paymenth_method')->nullable();
                $table->text('shipmenth_method')->nullable();
                $table->softDeletes();

                $table->index('path');

                $table->foreign('province_id')->references('category_id')->on('category')->onDelete('SET NULL');
                $table->foreign('district_id')->references('category_id')->on('category')->onDelete('SET NULL');
                $table->foreign('created_by')->references('id')->on('users')->onDelete('SET NULL');
                $table->foreign('updated_by')->references('id')->on('users')->onDelete('SET NULL');
            });
        }

        if (! \Schema::hasTable('shop_cart')) {
            \Schema::create('shop_cart', function($table) {
                $table->increments('shop_cart_id');
                $table->string('session_id', 256)->nullable();
                $table->integer('shop_id')->unsigned()->nullable();
                $table->dateTime('created_at')->nullable();
                $table->dateTime('updated_at')->nullable();
                $table->integer('created_by')->unsigned()->nullable();
                $table->integer('updated_by')->unsigned()->nullable();
                $table->boolean('active')->default(1);
                $table->softDeletes();

                $table->index('shop_id');

                $table->foreign('shop_id')->references('shop_id')->on('shop')->onDelete('SET NULL');
                $table->foreign('created_by')->references('id')->on('users')->onDelete('SET NULL');
                $table->foreign('updated_by')->references('id')->on('users')->onDelete('SET NULL');
            });
        }

        if (! \Schema::hasTable('shop_product')) {
            \Schema::create('shop_product', function($table) {
                $table->increments('shop_product_id');
                $table->integer('shop_id')->unsigned()->nullable();
                $table->string('title', 256);
                $table->longText('content')->nullable();
                $table->tinyInteger('label')->nullable();
                $table->integer('price')->default(0)->unsigned();
                $table->integer('promotion')->default(0)->unsigned();
                $table->string('promotion_type', 32)->nullable();
                $table->dateTime('promotion_start')->nullable();
                $table->dateTime('promotion_end')->nullable();
                $table->string('image', 256)->nullable();
                $table->integer('created_by')->unsigned()->nullable();
                $table->integer('updated_by')->unsigned()->nullable();
                $table->dateTime('created_at')->nullable();
                $table->dateTime('updated_at')->nullable();
                $table->boolean('active')->default(1);
                $table->softDeletes();

                $table->index('shop_id');
                $table->index('price');

                $table->foreign('shop_id')->references('shop_id')->on('shop')->onDelete('SET NULL');
                $table->foreign('created_by')->references('id')->on('users')->onDelete('SET NULL');
                $table->foreign('updated_by')->references('id')->on('users')->onDelete('SET NULL');
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
                $table->boolean('pay_gender')->default(0);
                $table->string('pay_name', 128)->nullable();
                $table->string('pay_email', 128)->nullable();
                $table->string('pay_phone', 32)->nullable();
                $table->string('pay_address', 256)->nullable();
                $table->boolean('ship_gender')->default(0);
                $table->string('ship_name', 128)->nullable();
                $table->string('ship_email', 128)->nullable();
                $table->string('ship_phone', 32)->nullable();
                $table->string('ship_address', 256)->nullable();
                $table->text('note')->nullable();
                $table->dateTime('created_at')->nullable();
                $table->dateTime('updated_at')->nullable();
                $table->integer('created_by')->unsigned()->nullable();
                $table->integer('updated_by')->unsigned()->nullable();
                $table->boolean('active')->default(1);
                $table->softDeletes();

                $table->index('shop_cart_id');
                $table->index('created_by');

                $table->foreign('shop_cart_id')->references('shop_cart_id')->on('shop_cart')->onDelete('cascade');
                $table->foreign('created_by')->references('id')->on('users')->onDelete('SET NULL');
                $table->foreign('updated_by')->references('id')->on('users')->onDelete('SET NULL');
            });
        }

        if (! \Schema::hasTable('shop_topic')) {
            \Schema::create('shop_topic', function($table) {
                $table->increments('shop_topic_id');
                $table->integer('shop_id')->unsigned()->nullable();
                $table->string('title', 256);
                $table->string('short_description', 256)->nullable();
                $table->longText('content')->nullable();
                $table->integer('price')->default(0)->unsigned();
                $table->string('shipping', 32)->nullable();
                $table->boolean('is_promotion')->default(0);
                $table->integer('promotion')->default(0)->unsigned();
                $table->string('promotion_type', 32)->nullable();
                $table->dateTime('promotion_start')->nullable();
                $table->dateTime('promotion_end')->nullable();
                $table->integer('category_id')->unsigned()->nullable();
                $table->integer('created_by')->unsigned()->nullable();
                $table->integer('updated_by')->unsigned()->nullable();
                $table->string('image', 256)->nullable();
                $table->dateTime('created_at')->nullable();
                $table->dateTime('updated_at')->nullable();
                $table->boolean('active')->default(1);
                $table->softDeletes();

                $table->index('shop_id');
                $table->index('created_by');
                $table->index('price');

                $table->foreign('shop_id')->references('shop_id')->on('shop')->onDelete('cascade');
                $table->foreign('created_by')->references('id')->on('users')->onDelete('SET NULL');
                $table->foreign('updated_by')->references('id')->on('users')->onDelete('SET NULL');
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
                'class' => '\ZeroPHP\Shop\Shop',
                'method' => 'shop_information', 
                'access' => 'shop_information_access_for_topic',
            ),
            array(
                'title' => 'shop_information for shop', 
                'cache_type' => 'page',
                'region' => 'right sidebar',
                'class' => '\ZeroPHP\Shop\Shop',
                'method' => 'shop_information', 
                'access' => 'shop_information_access_for_shop',
            ),
            array(
                'title' => 'shop_topic_warning', 
                'cache_type' => 'full',
                'region' => 'right sidebar',
                'class' => '\ZeroPHP\Shop\ShopTopic',
                'method' => 'shop_topic_warning', 
                'access' => 'shop_topic_warning_access',
            ),
        ));

        // Add some menus
        \DB::table('menu')->insert(array(
            array(
                'title' => 'Shop cart items',
                'path' => 'shopcart/items',
                'class' => '\ZeroPHP\Shop\ShopCart',
                'method' => 'showItems',
            ),
        ));

        if (!\DB::table('role')->where('title', 'Salesman')->first()) {
            \DB::table('role')->insert(array(
                array(
                    'title' => 'Salesman',
                ),
            ));
        }
    }

    private static function down_0_01() {}
}