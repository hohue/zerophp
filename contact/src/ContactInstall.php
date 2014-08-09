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
                $table->dateTime('created_at')->nullable();
                $table->dateTime('updated_at')->nullable();
                $table->integer('created_by')->nullable()->unsigned();

                $table->foreign('created_by')->references('id')->on('users')->onDelete('SET NULL');
            });
        }

        // Insert Default Data
        \DB::table('menu')->insert(array(
            array(
                'title' => 'Contact list',
                'path' => 'contact/list',
                'arguments' => '',
                'class' => '\ZeroPHP\Contact\Contact',
                'method' => 'showList',
            ),
            array(
                'title' => 'Contact Us',
                'path' => 'contact',
                'arguments' => '',
                'class' => '\ZeroPHP\Contact\Contact',
                'method' => 'showCreate',
            ),
            array(
                'title' => 'Contact read',
                'path' => 'contact/%',
                'arguments' => '1',
                'class' => '\ZeroPHP\Contact\Contact',
                'method' => 'showRead',
            ),
            array(
                'title' => 'Contact delete',
                'path' => 'contact/%/delete',
                'arguments' => '1',
                'class' => '\ZeroPHP\Contact\Contact',
                'method' => 'showDelete',
            ),
        ));
    }

    private static function down_0_01() {}
}