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
        if (! \Schema::hasTable('block')) {
            \Schema::create('block', function($table) {
                $table->increments('block_id');
                $table->string('title', 256);
                $table->string('cache_type', 32)->nullable();
                $table->string('region', 32)->nullable();
                $table->mediumText('content')->nullable();
                $table->string('class', 128);
                $table->string('method', 128);
                $table->string('access', 128)->nullable();
                $table->tinyInteger('weight')->default(0);
                $table->boolean('active')->default(1);

                $table->index('region');
            });
        }

        if (! \Schema::hasTable('menu')) {
            \Schema::create('menu', function($table) {
                $table->increments('menu_id');
                $table->string('title', 256);
                $table->string('cache', 32)->nullable();
                $table->string('path', 256);
                $table->string('class', 128);
                $table->string('method', 128);
                $table->string('arguments', 128)->nullable();
                $table->string('access', 256)->nullable();
                $table->tinyInteger('weight')->default(0);
                $table->boolean('active')->default(1);

                $table->index('path');
            });
        }

        if (! \Schema::hasTable('urlalias')) {
            \Schema::create('urlalias', function($table) {
                $table->increments('urlalias_id');
                $table->string('url_real', 256);
                $table->string('url_alias', 256);
                $table->timestamps();

                $table->index('url_real');
                $table->index('url_alias');
            });
        }

        if (! \Schema::hasTable('language_translate')) {
            \Schema::create('language_translate', function($table) {
                $table->increments('language_translate_id');
                $table->text('en');
                $table->text('vi')->nullable();
            });
        }

        if (! \Schema::hasTable('users')) {
            \Schema::create('users', function($table) {
                $table->increments('user_id');
                $table->string('title', 128)->nullable();
                $table->string('email', 128);
                $table->string('password', 128);
                $table->boolean('active')->default(0);
                $table->rememberToken();
                $table->timestamp('last_activity');
                $table->timestamps();
                $table->softDeletes();

                $table->index('email');
            });
        }

        if (! \Schema::hasTable('activation')) {
            \Schema::create('activation', function($table) {
                $table->increments('activation_id');
                $table->integer('destination_id')->unsigned();
                $table->string('hash', 128);
                $table->timestamp('expired');
                $table->string('type', 32)->nullable();

                $table->index('hash');
            });
        }

        if (! \Schema::hasTable('hook')) {
            \Schema::create('hook', function($table) {
                $table->increments('hook_id');
                $table->string('title', 256);
                $table->string('hook_type', 32);
                $table->string('hook_condition', 128);
                $table->string('class', 128);
                $table->string('method', 128);
                $table->tinyInteger('weight')->default(0);
                $table->boolean('active')->default(1);

                $table->index('hook_type');
                $table->index('hook_condition');
            });
        }

        if (! \Schema::hasTable('image_style')) {
            \Schema::create('image_style', function($table) {
                $table->string('style', 32);
                $table->smallInteger('width');
                $table->smallInteger('height');
                $table->string('type', 32);

                $table->primary('style');
            });
        }

        if (! \Schema::hasTable('role')) {
            \Schema::create('role', function($table) {
                $table->increments('role_id');
                $table->string('title', 64);
                $table->tinyInteger('weight')->default(0);
                $table->boolean('active')->default(1);
            });
        }

        if (! \Schema::hasTable('users_role')) {
            \Schema::create('users_role', function($table) {
                $table->string('field', 32);
                $table->integer('user_id')->unsigned();
                $table->integer('role_id')->unsigned();

                $table->foreign('user_id')->references('user_id')->on('users')->onDelete('cascade');
                $table->foreign('role_id')->references('role_id')->on('role')->onDelete('cascade');
            });
        }

        if (! \Schema::hasTable('variable')) {
            \Schema::create('variable', function($table) {
                $table->string('variable_key', 128);
                $table->text('variable_value');

                $table->primary('variable_key');
            });
        }

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
    }
}