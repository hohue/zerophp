<?php 
namespace ZeroPHP\Metadata;

define('VERSION_ZEROPHP_METADATA', 0.01);

class MetadataInstall {
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
        if (! \Schema::hasTable('metadata')) {
            \Schema::create('metadata', function($table) {
                $table->increments('metadata_id');
                $table->string('path', 256);
                $table->string('path_title', 256)->nullable();
                $table->text('keywords')->nullable();
                $table->text('description')->nullable();
                $table->dateTime('created_at')->nullable();
                $table->dateTime('updated_at')->nullable();
                $table->boolean('active')->default(1);

                $table->index('path');
            });
        }
    }

    private static function down_0_01() {}
}