<?php 
namespace ZeroPHP\Article;

define('VERSION_ZEROPHP_ARTICLE', 0.01001);

class ArticleInstall {
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
                'title' => 'Article create',
                'path' => 'article/create',
                'arguments' => '',
                'class' => 'ZeroPHP\\Article\\ArticleController',
                'method' => 'createForm',
            ),
            array(
                'title' => 'Article read',
                'path' => 'article/%',
                'arguments' => '1',
                'class' => 'ZeroPHP\\Article\\ArticleController',
                'method' => 'show',
            ),
            array(
                'title' => 'Article update',
                'path' => 'article/%/update',
                'arguments' => '1',
                'class' => 'ZeroPHP\\Article\\ArticleController',
                'method' => 'updateForm',
            ),
        ));
    }

    private static function down_0_01001() {
        \DB::table('menu')->whereIn('path', array(
            'article/create',
            'article/%',
            'article/%/update'
            ))->delete();
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
    }

    private static function down_0_01() {
        // Drop Tables
        //\Schema::drop('block');
    }
}