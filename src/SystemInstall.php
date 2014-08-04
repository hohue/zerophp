<?php 
namespace ZeroPHP\ZeroPHP;

define('VERSION_ZEROPHP_ZEROPHP', 0.01001);

class SystemInstall {
    public static function up($prev_version) {
        if ($prev_version < 0.01)    self::up_0_01();
        if ($prev_version < 0.01001) self::up_0_01001();
    }

    public static function down($prev_version) {
        if ($prev_version < 0.01)    self::down_0_01();
        if ($prev_version < 0.01001) self::down_0_01001();
    }

    private static function up_0_01001() {
        // Insert User 1
        if (!\DB::table('users')->where('id', 1)->first()) {
            $now = date('Y-m-d H:i:s');
            \DB::table('users')->insert(array(
                array(
                    'id' => 1,
                    'title' => 'Administrator',
                    'email' => 'admin@localhost.com',
                    'password' => \Hash::make('12345678'),
                    'active' => 1,
                    'created_at' => $now,
                    'updated_at' => $now,
                    'last_activity' => $now,
                ),
            ));
        }

        \DB::table('menu')->insert(array(
            array(
                'title' => 'User register success',
                'path' => 'user/register/success',
                'arguments' => '',
                'class' => '\ZeroPHP\ZeroPHP\Users',
                'method' => 'showRegisterSuccess',
            ),
            array(
                'title' => 'Forgot password success',
                'path' => 'user/forgotpass/success',
                'arguments' => '',
                'class' => '\ZeroPHP\ZeroPHP\Users',
                'method' => 'userForgotPasswordSuccess',
            ),
            array(
                'title' => 'Clear Cache',
                'path' => 'performance/clear/cache',
                'arguments' => '',
                'class' => '\ZeroPHP\ZeroPHP\Performance',
                'method' => 'clearCache',
            ),
            array(
                'title' => 'Clear OPCache',
                'path' => 'performance/clear/opcache',
                'arguments' => '',
                'class' => '\ZeroPHP\ZeroPHP\Performance',
                'method' => 'clearOPCache',
            ),
            array(
                'title' => 'User list',
                'path' => 'user/list',
                'arguments' => '',
                'class' => '\ZeroPHP\ZeroPHP\Users',
                'method' => 'lst',
            ),
            array(
                'title' => 'User create',
                'path' => 'user/create',
                'arguments' => '',
                'class' => '\ZeroPHP\ZeroPHP\Users',
                'method' => 'create',
            ),
            array(
                'title' => 'User read',
                'path' => 'user/read',
                'arguments' => '',
                'class' => '\ZeroPHP\ZeroPHP\Users',
                'method' => 'read',
            ),
            array(
                'title' => 'User update',
                'path' => 'user/%/update',
                'arguments' => '1',
                'class' => '\ZeroPHP\ZeroPHP\Users',
                'method' => 'update',
            ),
            array(
                'title' => 'User delete',
                'path' => 'user/%/delete',
                'arguments' => '1',
                'class' => '\ZeroPHP\ZeroPHP\Users',
                'method' => 'delete',
            ),
            array(
                'title' => 'Reset Password success',
                'path' => 'user/resetpass/success',
                'arguments' => '',
                'class' => '\ZeroPHP\ZeroPHP\Users',
                'method' => 'showResetPasswordSuccess',
            ),
        ));

        \DB::table('variable')->insert(array(
            array(
                'variable_key' => 'theme admin',
                'variable_value' => 'bootstrap',
            ),
            array(
                'variable_key' => 'users register email validation',
                'variable_value' => 1,
            ),
            array(
                'variable_key' => 'activation expired',
                'variable_value' => 172800,
            ),
            array(
                'variable_key' => 'user activation email subject',
                'variable_value' => 'Activation your account',
            ),
        ));

        \DB::table('image_style')->insert(array(
            array(
                'style' => 'normal',
                'width' => 450,
                'height' => 300,
                'type' => 'scale',
            ),
            array(
                'style' => 'thumbnail',
                'width' => 100,
                'height' => 100,
                'type' => 'scale',
            ),
        ));
    }

    private static function down_0_01001() {
        // Remove menu
        \DB::table('menu')->whereIn('path', array(
                'user/register/success',
                'performance/clear/cache',
                'performance/clear/opcache',
                'user/forgotpass/success',
                'user/list',
                'user/create',
                'user/%',
                'user/%/update',
                'user/%/delete',
                'user/resetpass/success',
            ))->delete();

        \DB::table('variable')->whereIn('variable_key', array(
                'theme admin',
                'users register email validation',
                'activation expired',
                'user activation email subject',
            ))->delete();

        \DB::table('image_style')->whereIn('style', array(
                'normal',
                'thumbnail',
            ))->delete();
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
                $table->increments('id');
                $table->string('title', 128)->nullable();
                $table->string('email', 128);
                $table->string('password', 128);
                $table->boolean('active')->default(0);
                $table->rememberToken();
                $table->timestamp('last_activity')->nullable();
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
                $table->tinyInteger('is_upsize')->default(0);

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
                $table->integer('id')->unsigned();
                $table->integer('role_id')->unsigned();

                $table->foreign('id')->references('id')->on('users')->onDelete('cascade');
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
                'class' => '\ZeroPHP\ZeroPHP\BlockDefault',
                'method' => 'admin_menu', 
                'access' => 'admin_menu_access',
            ),
        ));

        \DB::table('menu')->insert(array(
            array(
                'title' => 'Homepage',
                'path' => '/',
                'arguments' => '',
                'class' => '\ZeroPHP\ZeroPHP\Dashboard',
                'method' => 'showHomepage',
            ),
            array(
                'title' => 'Message',
                'path' => 'response/message',
                'arguments' => '',
                'class' => '\ZeroPHP\ZeroPHP\Response',
                'method' => 'showMessage',
            ),
            array(
                'title' => 'User Login',
                'path' => 'user/login',
                'arguments' => '',
                'class' => '\ZeroPHP\ZeroPHP\Users',
                'method' => 'showLogin',
            ),
            array(
                'title' => 'User Logout',
                'path' => 'user/logout',
                'arguments' => '',
                'class' => '\ZeroPHP\ZeroPHP\Users',
                'method' => 'showLogout',
            ),
            array(
                'title' => 'User Register',
                'path' => 'user/register',
                'arguments' => '',
                'class' => '\ZeroPHP\ZeroPHP\Users',
                'method' => 'showRegister',
            ),
            array(
                'title' => 'User Activation',
                'path' => 'user/activation/%',
                'arguments' => '2',
                'class' => '\ZeroPHP\ZeroPHP\Users',
                'method' => 'showActivation',
            ),
            array(
                'title' => 'User Resend Activation Code',
                'path' => 'user/activation/resend',
                'arguments' => '',
                'class' => '\ZeroPHP\ZeroPHP\Users',
                'method' => 'showActivationResend',
            ),
            array(
                'title' => 'User Change Password',
                'path' => 'user/changepass',
                'arguments' => '',
                'class' => '\ZeroPHP\ZeroPHP\Users',
                'method' => 'showChangePassword',
            ),
            array(
                'title' => 'User Forgot Password',
                'path' => 'user/forgotpass',
                'arguments' => '',
                'class' => '\ZeroPHP\ZeroPHP\Users',
                'method' => 'showForgotPassword',
            ),
            array(
                'title' => 'User Reset Password',
                'path' => 'user/resetpass/%',
                'arguments' => '2',
                'class' => '\ZeroPHP\ZeroPHP\Users',
                'method' => 'showResetPassword',
            ),
        ));

        \DB::table('role')->insert(array(
            array(
                'title' => 'Anonymous User',
            ),
            array(
                'title' => 'Registered User',
            ),
            array(
                'title' => 'Administrator',
            ),
            array(
                'title' => 'Editor',
            ),
        ));


        //Set Variables
        zerophp_variable_set('ZEROPHP_CACHE_EXPIRE_TIME', 10);
    }

    private static function down_0_01() {
        // Drop Tables
        \Schema::drop('menu');
        \Schema::drop('block');
        \Schema::drop('hook');
        \Schema::drop('image_style');
    }
}