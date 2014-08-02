<?php 
namespace ZeroPHP\Contact;

define('VERSION_ZEROPHP_CONTACT', 0.01001);

class ContactInstall {
    public static function up($prev_version) {
        if ($prev_version < 0.01)    { self::up_0_01(); }
        if ($prev_version < 0.01001) { self::up_0_01001(); }
    }

    public static function down($prev_version) {
        if ($prev_version < 0.01)    { self::down_0_01(); }
        if ($prev_version < 0.01001) { self::down_0_01001(); }
    }

    private static function up_0_01001() {
        // Insert Default Data
        \DB::table('menu')->insert(array(
            array(
                'title' => 'Contact Us',
                'path' => 'contact',
                'class' => '\ZeroPHP\Contact\Contact',
                'method' => 'create',
            ),
        ));
    }

    private static function down_0_01001() {
        \DB::table('menu')->whereIn('path', array(
            'contact'
            ))->delete();
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
    }

    private static function down_0_01() {
        // Drop Tables
        //\Schema::drop('block');
    }
}