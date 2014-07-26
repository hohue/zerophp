<?php 
namespace ZeroPHP\Article;

define('VERSION_ZEROPHP_ARTICLE', 0.01);

class ArticleInstall {
    public static function up($prev_version) {
        if ($prev_version < VERSION_ZEROPHP_ARTICLE && VERSION_ZEROPHP_ARTICLE <= 0.01) {
            self::up_0_01();
        }
    }

    public static function down($prev_version) {
        if ($prev_version < VERSION_ZEROPHP_ARTICLE && VERSION_ZEROPHP_ARTICLE <= 0.01) {
            self::down_0_01();
        }
    }

    private static function up_0_01() {
        if (! \Schema::hasTable('article')) {
            \Schema::create('article', function($table) {
                $table->increments('article_id');
                $table->string('title', 256);
                $table->string('image', 256);
                $table->longText('content');
                $table->boolean('active')->default(1);
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