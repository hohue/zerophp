<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ZerophpInstall extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		ZeroPHP\ZeroPHP\SystemInstall::up(Config::get('install.prev_version_zerophp_zerophp'));
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		ZeroPHP\ZeroPHP\SystemInstall::down(Config::get('install.prev_version_zerophp_zerophp'));
	}

}
