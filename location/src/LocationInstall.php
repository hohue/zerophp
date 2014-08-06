<?php 
namespace ZeroPHP\Location;

define('VERSION_ZEROPHP_LOCATION', 0.01);

class LocationInstall {
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
        \DB::table('menu')->insert(array(
            array(
                'title' => 'Get District',
                'path' => 'location/district',
                'class' => '\ZeroPHP\Location\Location',
                'method' => 'getDistrict',
            ),
        ));
    }

    private static function down_0_01() {}
}