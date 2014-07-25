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
            $table->string('title')->length(256);
            $table->string('cache_type')->nullable()->length(32);
            $table->string('region')->nullable()->length(32);
            $table->mediumText('content')->nullable();
            $table->string('class', 128);
            $table->string('method', 128);
            $table->string('access')->nullable()->length(128);
            $table->tinyInteger('weight')->default(0);
            $table->boolean('active')->default(1);

            $table->index('region');
            $table->index('class');
            $table->index('method');
        });

        \Schema::create('menu', function($table) {
            $table->increments('menu_id');
            $table->string('title', 256);
            $table->string('cache')->nullable()->length(32);
            $table->string('path', 256);
            $table->string('class', 128);
            $table->string('method', 128);
            $table->string('arguments')->nullable()->length(128);
            $table->string('access')->nullable()->length(256);
            $table->tinyInteger('weight')->default(0);
            $table->boolean('active')->default(1);

            $table->index('path');
            $table->index('class');
            $table->index('method');
        });

        \Schema::create('urlalias', function($table) {
            $table->increments('urlalias_id');
            $table->string('url_real', 256);
            $table->string('url_alias', 256);
            $table->timestamps();

            $table->index('url_real');
            $table->index('url_alias');
        });

        if (! \Schema::hasTable('language_translate')) {
            \Schema::create('language_translate', function($table) {
                $table->increments('language_translate_id');
                $table->mediumText('en');
                $table->mediumText('vi')->nullable();

                $table->index('en');
            });
        }

        \Schema::create('users', function($table) {
            $table->increments('user_id');
            $table->string('title')->nullable()->length(128);
            $table->string('email', 128);
            $table->string('password', 128);
            $table->boolean('active')->default(0);
            $table->rememberToken();
            $table->timestamp('last_activity');
            $table->timestamps();

            $table->index('email');
        });

        // Insert Default Data
        \DB::table('block')->insert(array(
            array(
                'title' => 'Admin Menus', 
                'cache_type' => 'full',
                'region' => 'admin left sidebar',
                'class' => 'ZeroPHP\\ZeroPHP\\BlockDefault',
                'method' => 'admin_menu', 
                'access' => 'admin_menu_access',
            ),
        ));

        \DB::table('menu')->insert(array(
            array(
                'title' => 'Homepage',
                'path' => '/',
                'class' => 'ZeroPHP\\ZeroPHP\\DashboardController',
                'method' => 'showHomepage',
            ),
            array(
                'title' => 'Message',
                'path' => 'response/message',
                'class' => 'ZeroPHP\\ZeroPHP\\ResponseController',
                'method' => 'showMessage',
            ),
        ));
    }

    private static function down_0_01() {
        // Drop Tables
        \Schema::drop('block');
        \Schema::drop('menu');
        \Schema::drop('urlalias');
        \Schema::drop('users');
    }
}