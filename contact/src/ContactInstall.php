<?php 
namespace ZeroPHP\Contact;

define('VERSION_ZEROPHP_CONTACT', 0.01);

class ContactInstall {
    public static function up($prev_version) {
        if ($prev_version < VERSION_ZEROPHP_CONTACT && VERSION_ZEROPHP_CONTACT <= 0.01) {
            self::up_0_01();
        }
    }

    public static function down($prev_version) {
        if ($prev_version < VERSION_ZEROPHP_CONTACT && VERSION_ZEROPHP_CONTACT <= 0.01) {
            self::down_0_01();
        }
    }

    private static function up_0_01() {
        if (! \Schema::hasTable('contact')) {
            \Schema::create('contact', function($table) {
                $table->increments('contact_id');
                $table->string('fullname', 128);
                $table->string('email', 128);
                $table->string('title', 256);
                $table->text('content');
                $table->timestamps();
                $table->integer('created_by')->default(0);
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