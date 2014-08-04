<?php 
namespace ZeroPHP\Article;

define('VERSION_ZEROPHP_ARTICLE', 0.01);

class ArticleInstall {
    public static function up($prev_version) {
        if ($prev_version < 0.01)    { self::up_0_01(); }
    }

    public static function down($prev_version) {
        if ($prev_version < 0.01)    { self::down_0_01(); }
    }

    private static function up_0_01() {
        if (! \Schema::hasTable('article')) {
            \Schema::create('article', function($table) {
                $table->increments('article_id');
                $table->string('title', 256);
                $table->integer('category_id')->nullable()->unsigned();
                $table->string('image', 256)->nullable();
                $table->text('summary')->nullable();
                $table->longText('content');
                $table->boolean('active')->default(1);
                $table->timestamps();
                $table->integer('created_by')->default(0)->unsigned();
                $table->integer('updated_by')->default(0)->unsigned();
            });
        }

        // Insert Default Data
        \DB::table('menu')->insert(array(
            array(
                'title' => 'Article list',
                'path' => 'article/list',
                'arguments' => '',
                'class' => '\ZeroPHP\Article\Article',
                'method' => 'lst',
            ),
            array(
                'title' => 'Article create',
                'path' => 'article/create',
                'arguments' => '',
                'class' => '\ZeroPHP\Article\Article',
                'method' => 'create',
            ),
            array(
                'title' => 'Article read',
                'path' => 'article/%',
                'arguments' => '1',
                'class' => '\ZeroPHP\Article\Article',
                'method' => 'read',
            ),
            array(
                'title' => 'Article update',
                'path' => 'article/%/update',
                'arguments' => '1',
                'class' => '\ZeroPHP\Article\Article',
                'method' => 'update',
            ),
            array(
                'title' => 'Article delete',
                'path' => 'article/%/delete',
                'arguments' => '1',
                'class' => '\ZeroPHP\Article\Article',
                'method' => 'delete',
            ),
        ));
    }

    private static function down_0_01() {}
}