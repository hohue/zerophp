<?php 
namespace ZeroPHP\Contact;

define('VERSION_ZEROPHP_CONTACT', 0.01);

class ContactInstall {
    public static function up($prev_version) {
        if ($prev_version < 0.01)    { self::up_0_01(); }
    }

    public static function down($prev_version) {
        if ($prev_version < 0.01)    { self::down_0_01(); }
    }

    private static function up_0_01() {
        if (! \Schema::hasTable('contact')) {
            \Schema::create('contact', function($table) {
                $table->increments('contact_id');
                $table->string('fullname', 128)->nullable();
                $table->string('email', 128)->nullable();
                $table->string('title', 256)->nullable();
                $table->text('content');
                $table->timestamps();
                $table->integer('created_by')->default(0)->unsigned();
            });
        }

        // Insert Default Data
        \DB::table('menu')->insert(array(
            array(
                'title' => 'Contact Us',
                'path' => 'contact',
                'class' => '\ZeroPHP\Contact\Contact',
                'method' => 'create',
            ),
            array(
                'title' => 'Contact List',
                'path' => 'contact/list',
                'class' => '\ZeroPHP\Contact\Contact',
                'method' => 'lst',
            ),
        ));
    }

    private static function down_0_01() {}
}