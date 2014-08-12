<?php 
namespace ZeroPHP\ZeroPHP;

define('VERSION_ZEROPHP_ZEROPHP', 0.01);

class SystemInstall {
    public static function up($prev_version) {
        if ($prev_version < 0.01)    self::up_0_01();
    }

    public static function down($prev_version) {
        if ($prev_version < 0.01)    self::down_0_01();
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
                $table->string('class', 128)->nullable();
                $table->string('method', 128)->nullable();
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
                $table->string('real', 256);
                $table->string('alias', 256);
                $table->dateTime('created_at')->nullable();
                $table->dateTime('updated_at')->nullable();

                $table->index('real');
                $table->index('alias');
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
                $table->string('username', 128)->nullable();
                $table->string('email', 128)->nullable();
                $table->string('password', 128)->nullable();
                $table->boolean('active')->default(0);
                $table->rememberToken();
                $table->dateTime('last_activity')->nullable();
                $table->dateTime('created_at')->nullable();
                $table->dateTime('updated_at')->nullable();
                $table->softDeletes();

                $table->index('email');
            });
        }

        if (! \Schema::hasTable('activation')) {
            \Schema::create('activation', function($table) {
                $table->increments('activation_id');
                $table->string('hash', 128);
                $table->integer('destination_id')->unsigned()->nullable();
                $table->dateTime('expired')->nullable();
                $table->string('type', 32)->nullable();

                $table->index('hash');
            });
        }

        if (! \Schema::hasTable('hook')) {
            \Schema::create('hook', function($table) {
                $table->increments('hook_id');
                $table->string('title', 256);
                $table->string('hook_type', 32);
                $table->string('hook_condition', 128)->nullable();
                $table->string('class', 128);
                $table->string('method', 128);
                $table->tinyInteger('weight')->default(0);
                $table->boolean('active')->default(1);

                $table->index('hook_type');
            });
        }

        if (! \Schema::hasTable('image_style')) {
            \Schema::create('image_style', function($table) {
                $table->string('style', 32);
                $table->smallInteger('width')->nullable();
                $table->smallInteger('height')->nullable();
                $table->string('type', 32)->default('scale');
                $table->boolean('is_upsize')->default(0);

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
                $table->text('variable_value')->nullable();

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
                'method' => 'showForgotPasswordSuccess',
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
                'method' => 'showList',
            ),
            array(
                'title' => 'User create',
                'path' => 'user/create',
                'arguments' => '',
                'class' => '\ZeroPHP\ZeroPHP\Users',
                'method' => 'showCreate',
            ),
            array(
                'title' => 'User read',
                'path' => 'user/%',
                'arguments' => '1',
                'class' => '\ZeroPHP\ZeroPHP\Users',
                'method' => 'showRead',
            ),
            array(
                'title' => 'User update',
                'path' => 'user/%/update',
                'arguments' => '1',
                'class' => '\ZeroPHP\ZeroPHP\Users',
                'method' => 'showUpdate',
            ),
            array(
                'title' => 'User delete',
                'path' => 'user/%/delete',
                'arguments' => '1',
                'class' => '\ZeroPHP\ZeroPHP\Users',
                'method' => 'showDelete',
            ),
            array(
                'title' => 'Reset Password success',
                'path' => 'user/resetpass/success',
                'arguments' => '',
                'class' => '\ZeroPHP\ZeroPHP\Users',
                'method' => 'showResetPasswordSuccess',
            ),
            array(
                'title' => 'User activation resend success',
                'path' => 'user/activation/resend/success',
                'arguments' => '',
                'class' => '\ZeroPHP\ZeroPHP\Users',
                'method' => 'showActivationResendSuccess',
            ),
            array(
                'title' => 'User preview',
                'path' => 'user/%/preview',
                'arguments' => '1',
                'class' => '\ZeroPHP\ZeroPHP\Users',
                'method' => 'showPreview',
            ),
        ));

        if (!\DB::table('role')->where('title', 'Anonymous User')->first()) {
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
        }

        // Insert User 1
        if (!\DB::table('users')->where('id', 1)->first()) {
            $now = date('Y-m-d H:i:s');
            \DB::table('users')->insert(array(
                array(
                    'id' => 1,
                    'title' => 'Administrator',
                    'username' => 'admin@localhost.com',
                    'email' => 'admin@localhost.com',
                    'password' => \Hash::make('12345678'),
                    'active' => 1,
                    'created_at' => $now,
                    'updated_at' => $now,
                    'last_activity' => $now,
                ),
            ));
        }

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

        if (!\DB::table('variable')->where('variable_key', 'activation expired')->first()) {
            \DB::table('variable')->insert(array(
                'variable_key' => 'activation expired',
                'variable_value' => 172800,
            ));
        }

        if (!\DB::table('variable')->where('variable_key', 'image lazy load')->first()) {
            \DB::table('variable')->insert(array(
                'variable_key' => 'image lazy load',
                'variable_value' => 1,
            ));
        }

        if (!\DB::table('variable')->where('variable_key', 'datatables items per page')->first()) {
            \DB::table('variable')->insert(array(
                'variable_key' => 'datatables items per page',
                'variable_value' => 25,
            ));
        }

        if (!\DB::table('variable')->where('variable_key', 'datatables config searching')->first()) {
            \DB::table('variable')->insert(array(
                'variable_key' => 'datatables config searching',
                'variable_value' => 1,
            ));
        }

        if (!\DB::table('variable')->where('variable_key', 'datatables config ordering')->first()) {
            \DB::table('variable')->insert(array(
                'variable_key' => 'datatables config ordering',
                'variable_value' => 0,
            ));
        }

        if (!\DB::table('variable')->where('variable_key', 'datatables config paging')->first()) {
            \DB::table('variable')->insert(array(
                'variable_key' => 'datatables config paging',
                'variable_value' => 0,
            ));
        }

        if (!\DB::table('variable')->where('variable_key', 'datatables config info')->first()) {
            \DB::table('variable')->insert(array(
                'variable_key' => 'datatables config info',
                'variable_value' => 0,
            ));
        }

        if (!\DB::table('variable')->where('variable_key', 'form error message show in field')->first()) {
            \DB::table('variable')->insert(array(
                'variable_key' => 'form error message show in field',
                'variable_value' => 1,
            ));
        }

        if (!\DB::table('variable')->where('variable_key', 'users register email validation')->first()) {
            \DB::table('variable')->insert(array(
                'variable_key' => 'users register email validation',
                'variable_value' => 1,
            ));
        }

        if (!\DB::table('variable')->where('variable_key', 'user activation email subject')->first()) {
            \DB::table('variable')->insert(array(
                'variable_key' => 'user activation email subject',
                'variable_value' => 'Activation your account',
            ));
        }

        if (!\DB::table('variable')->where('variable_key', 'user forgotpass email subject')->first()) {
            \DB::table('variable')->insert(array(
                'variable_key' => 'user forgotpass email subject',
                'variable_value' => 'Reset your password',
            ));
        }

        if (!\DB::table('variable')->where('variable_key', 'ZEROPHP_CACHE_EXPIRE_TIME')->first()) {
            \DB::table('variable')->insert(array(
                'variable_key' => 'ZEROPHP_CACHE_EXPIRE_TIME',
                'variable_value' => 10,
            ));
        }

        if (!\DB::table('variable')->where('variable_key', 'paganization items')->first()) {
            \DB::table('variable')->insert(array(
                'variable_key' => 'paganization items',
                'variable_value' => 5,
            ));
        }

        if (!\DB::table('variable')->where('variable_key', 'file image rule')->first()) {
            \DB::table('variable')->insert(array(
                'variable_key' => 'file image rule',
                'variable_value' => 'mimes:jpeg,png,gif',
            ));
        }

        if (!\DB::table('variable')->where('variable_key', 'file path')->first()) {
            \DB::table('variable')->insert(array(
                'variable_key' => 'file path',
                'variable_value' => '/files',
            ));
        }

        if (!\DB::table('variable')->where('variable_key', 'file image background')->first()) {
            \DB::table('variable')->insert(array(
                'variable_key' => 'file image background',
                'variable_value' => '#ffffff',
            ));
        }

        if (!\DB::table('variable')->where('variable_key', 'file image quality')->first()) {
            \DB::table('variable')->insert(array(
                'variable_key' => 'file image quality',
                'variable_value' => '70',
            ));
        }

        if (!\DB::table('variable')->where('variable_key', 'install prev_version_zerophp_zerophp')->first()) {
            \DB::table('variable')->insert(array(
                'variable_key' => 'install prev_version_zerophp_zerophp',
                'variable_value' => 0.01,
            ));
        }
    }

    private static function down_0_01() {
        // Drop Tables
        \Schema::drop('menu');
        \Schema::drop('block');
        \Schema::drop('hook');

        \DB::table('image_style')->whereIn('style', array(
                'normal',
                'thumbnail',
            ))->delete();
    }
}