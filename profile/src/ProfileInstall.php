<?php 
namespace ZeroPHP\Profile;

define('VERSION_ZEROPHP_PROFILE', 0.01);

class ProfileInstall {
    public static function up($prev_version) {
        if ($prev_version < VERSION_ZEROPHP_PROFILE && VERSION_ZEROPHP_PROFILE <= 0.01) {
            self::up_0_01();
        }
    }

    public static function down($prev_version) {
        if ($prev_version < VERSION_ZEROPHP_PROFILE && VERSION_ZEROPHP_PROFILE <= 0.01) {
            self::down_0_01();
        }
    }

    private static function up_0_01() {

        // Insert Default Data
        \DB::table('menu')->insert(array(
            array(
                'title' => 'User Profile Update',
                'path' => 'profile/%/update',
                'class' => 'ZeroPHP\\Profile\\Profile',
                'method' => 'crudUpdate',
                'arguments' => '1',
            ),
        ));
    }

    private static function down_0_01() {
        // Drop Tables
        //\Schema::drop('block');
    }
}