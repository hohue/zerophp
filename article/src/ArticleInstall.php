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
                $table->longText('content')->nullable();
                $table->boolean('active')->default(1);
                $table->dateTime('created_at')->nullable();
                $table->dateTime('updated_at')->nullable();
                $table->integer('created_by')->nullable()->unsigned();
                $table->integer('updated_by')->nullable()->unsigned();

                $table->foreign('category_id')->references('category_id')->on('category')->onDelete('SET NULL');
                $table->foreign('created_by')->references('id')->on('users')->onDelete('SET NULL');
                $table->foreign('updated_by')->references('id')->on('users')->onDelete('SET NULL');
            });
        }

        if (\Schema::hasColumn('article', 'category_id')) {
            \Schema::table('article', function($table){
                $table->dropForeign('article_category_id_foreign');
                $table->dropColumn('category_id');
            });
        }

        // Insert Default Data
        \DB::table('menu')->insert(array(
            array(
                'title' => 'Article list',
                'path' => 'article/list',
                'arguments' => '',
                'class' => '\ZeroPHP\Article\Article',
                'method' => 'showList',
            ),
            array(
                'title' => 'Article create',
                'path' => 'article/create',
                'arguments' => '',
                'class' => '\ZeroPHP\Article\Article',
                'method' => 'showCreate',
            ),
            array(
                'title' => 'Article clone',
                'path' => 'article/clone',
                'arguments' => '',
                'class' => '\ZeroPHP\Article\Article',
                'method' => 'showClone',
            ),
            array(
                'title' => 'Article read',
                'path' => 'article/%',
                'arguments' => '1',
                'class' => '\ZeroPHP\Article\Article',
                'method' => 'showRead',
            ),
            array(
                'title' => 'Article update',
                'path' => 'article/%/update',
                'arguments' => '1',
                'class' => '\ZeroPHP\Article\Article',
                'method' => 'showUpdate',
            ),
            array(
                'title' => 'Article delete',
                'path' => 'article/%/delete',
                'arguments' => '1',
                'class' => '\ZeroPHP\Article\Article',
                'method' => 'showDelete',
            ),
        ));
    }

    private static function down_0_01() {}
}