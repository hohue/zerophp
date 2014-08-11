<?php 
namespace ZeroPHP\Breadcrumb;

define('VERSION_ZEROPHP_BREADCRUMB', 0.01);

class BreadcrumbInstall {
    public static function up($prev_version) {
        if ($prev_version < 0.01)    { self::up_0_01(); }
    }

    public static function down($prev_version) {
        if ($prev_version < 0.01)    { self::down_0_01(); }
    }

    private static function up_0_01() {
        if (! \Schema::hasTable('breadcrumb')) {
            \Schema::create('breadcrumb', function($table) {
                $table->increments('breadcrumb_id');
                $table->string('path', 256);
                $table->string('class', 128);
                $table->string('method', 128);
                $table->string('arguments', 128)->nullable();
                $table->boolean('active')->default(1);
            });
        }

        // Insert Default Data
        \DB::table('menu')->insert(array(
            array(
                'title' => 'Breadcrumb list',
                'path' => 'breadcrumb/list',
                'arguments' => '',
                'class' => '\ZeroPHP\Breadcrumb\Breadcrumb',
                'method' => 'showList',
            ),
            array(
                'title' => 'Breadcrumb create',
                'path' => 'breadcrumb/create',
                'arguments' => '',
                'class' => '\ZeroPHP\Breadcrumb\Breadcrumb',
                'method' => 'showCreate',
            ),
            array(
                'title' => 'Breadcrumb clone',
                'path' => 'breadcrumb/clone',
                'arguments' => '',
                'class' => '\ZeroPHP\Breadcrumb\Breadcrumb',
                'method' => 'showClone',
            ),
            array(
                'title' => 'Breadcrumb update',
                'path' => 'breadcrumb/%/update',
                'arguments' => '1',
                'class' => '\ZeroPHP\Breadcrumb\Breadcrumb',
                'method' => 'showUpdate',
            ),
            array(
                'title' => 'Breadcrumb delete',
                'path' => 'breadcrumb/%/delete',
                'arguments' => '1',
                'class' => '\ZeroPHP\Breadcrumb\Breadcrumb',
                'method' => 'showDelete',
            ),
        ));
    }

    private static function down_0_01() {}
}