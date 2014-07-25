<?php 
namespace ZeroPHP\ZeroPHP;

define('VERSION_ZEROPHP_ZEROPHP', 0.01);

class SystemInstall {
    public static function up($prev_version) {
        if ($prev_version < VERSION_ZEROPHP_ZEROPHP && VERSION_ZEROPHP_ZEROPHP <= 0.01) {
            self::up_0_01();
        }
    }

    public static function down($prev_version) {
        if ($prev_version < VERSION_ZEROPHP_ZEROPHP && VERSION_ZEROPHP_ZEROPHP <= 0.01) {
            self::down_0_01();
        }
    }

    private static function up_0_01() {
        // Create Tables
        \Schema::create('block', function($table) {
            $table->increments('block_id');
            $table->string('title', 256);
            $table->string('cache_type', 32);
            $table->string('region', 32);
            $table->text('content');
            $table->string('class', 128);
            $table->string('method', 128);
            $table->string('access', 128);
            $table->tinyInteger('weight');
            $table->boolean('active');
        });

        \Schema::create('menu', function($table) {
            $table->increments('menu_id');
            $table->string('title', 256);
            $table->string('cache', 32);
            $table->string('path', 256);
            $table->string('class', 128);
            $table->string('method', 128);
            $table->text('access');
            $table->tinyInteger('weight');
            $table->boolean('active');
        });

        \Schema::create('urlalias', function($table) {
            $table->increments('urlalias_id');
            $table->string('url_real', 256);
            $table->string('url_alias', 256);
            $table->timestamps();
        });

        if (! \Schema::hasTable('language_translate')) {
            \Schema::create('language_translate', function($table) {
                $table->increments('language_translate_id');
                $table->text('en');
                $table->text('vi');
            });
        }

        // Insert Default Data
        \DB::table('block')->insert(array(
            array(
                'title' => 'Admin Menus', 
                'cache_type' => 'full',
                'region' => 'admin left sidebar', 
                'content' => '', 
                'class' => 'ZeroPHP\\ZeroPHP\\BlockDefault',
                'method' => 'admin_menu', 
                'access' => 'admin_menu_access',
                'weight' => 0, 
                'active' => 1,
            ),
        ));

        \DB::table('menu')->insert(array(
            array(
                'title' => 'Homepage', 
                'cache' => '',
                'path' => '/',
                'class' => 'ZeroPHP\\ZeroPHP\\DashboardController',
                'method' => 'showHomepage', 
                'access' => '',
                'weight' => 0, 
                'active' => 1,
            ),
            array(
                'title' => 'Message', 
                'cache' => '',
                'path' => 'response/message',
                'class' => 'ZeroPHP\\ZeroPHP\\ResponseController',
                'method' => 'showMessage', 
                'access' => '',
                'weight' => 0, 
                'active' => 1,
            ),
        ));
    }

    private static function down_0_01() {
        // Drop Tables
        \Schema::drop('block');
        \Schema::drop('menu');
        \Schema::drop('urlalias');
    }
}