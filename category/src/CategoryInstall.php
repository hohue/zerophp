<?php 
namespace ZeroPHP\Category;

define('VERSION_ZEROPHP_CATEGORY', 0.01);

class CategoryInstall {
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
        if (! \Schema::hasTable('category_group')) {
            \Schema::create('category_group', function($table) {
                $table->string('category_group_id', 32);
                $table->string('title', 256);
                $table->tinyInteger('weight')->default(0);
                $table->boolean('active')->default(1);

                $table->primary('category_group_id');
            });
        }

        if (! \Schema::hasTable('category')) {
            \Schema::create('category', function($table) {
                $table->increments('category_id');
                $table->string('category_group_id', 32);
                $table->string('title', 256);
                $table->integer('parent')->default(0);
                $table->tinyInteger('weight')->default(0);
                $table->boolean('active')->default(1);

                $table->index('category_group_id');
                $table->index('parent');

                $table->foreign('category_group_id')->references('category_group_id')->on('category_group')->onDelete('cascade');
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