<?php 
namespace ZeroPHP\Profile;

define('VERSION_ZEROPHP_PROFILE', 0.01);

class ProfileInstall {
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
        if (! \Schema::hasTable('profile')) {
            \Schema::create('profile', function($table) {
                $table->integer('id')->unsigned();
                $table->string('address', 256)->nullable();
                $table->integer('province_id')->unsigned()->nullable();
                $table->integer('district_id')->unsigned()->nullable();
                $table->string('mobile', 32)->nullable();
                $table->dateTime('birthday')->nullable();
                $table->integer('created_by')->unsigned()->nullable();
                $table->integer('updated_by')->unsigned()->nullable();

                $table->primary('id');

                $table->foreign('id')->references('id')->on('users')->onDelete('cascade');
                $table->foreign('province_id')->references('category_id')->on('category')->onDelete('SET NULL');
                $table->foreign('district_id')->references('category_id')->on('category')->onDelete('SET NULL');
                //$table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
                //$table->foreign('updated_by')->references('id')->on('users')->onDelete('SET NULL');
            });
        }

        // Insert Default Data
        \DB::table('menu')->insert(array(
            array(
                'title' => 'User Profile Update',
                'path' => 'profile/%/update',
                'class' => '\ZeroPHP\Profile\Profile',
                'method' => 'update',
                'arguments' => '1',
            ),
            array(
                'title' => 'User Profile',
                'path' => 'profile/%',
                'class' => '\ZeroPHP\Profile\Profile',
                'method' => 'read',
                'arguments' => '1',
            ),
        ));
    }

    private static function down_0_01() {}
}