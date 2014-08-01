<?php 
namespace ZeroPHP\Metadata;

define('VERSION_ZEROPHP_METADATA', 0.01);

class MetadataInstall {
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
        if (! \Schema::hasTable('metadata')) {
            \Schema::create('metadata', function($table) {
                $table->increments('metadata_id');
                $table->string('path', 256);
                $table->string('path_title', 256);
                $table->text('keywords');
                $table->text('description');
                $table->timestamps();
                $table->boolean('active')->default(1);

                $table->index('path');
            });
        }

        // Insert Default Data
        /*\DB::table('menu')->insert(array(
            array(
                'title' => 'User Profile Update',
                'path' => 'profile/%/update',
                'class' => 'ZeroPHP\\Profile\\Profile',
                'method' => 'crudUpdate',
                'arguments' => '1',
            ),
        ));*/
    }

    private static function down_0_01() {
        // Drop Tables
        //\Schema::drop('block');
    }
}