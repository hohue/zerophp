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
                $table->string('address', 256);
                $table->integer('local_id')->unsigned()->default(0);
                $table->integer('district_id')->unsigned()->default(0);
                $table->string('mobile', 32)->nullable();
                $table->timestamp('birthday');

                $table->primary('id');

                $table->foreign('id')->references('id')->on('users')->onDelete('cascade');
            });
        }

        // Insert Default Data
        \DB::table('menu')->insert(array(
            array(
                'title' => 'User Profile Update',
                'path' => 'profile/%/update',
                'class' => 'ZeroPHP\\Profile\\Profile',
                'method' => 'updateForm',
                'arguments' => '1',
            ),
        ));
    }

    private static function down_0_01() {
        // Drop Tables
        //\Schema::drop('block');
    }
}