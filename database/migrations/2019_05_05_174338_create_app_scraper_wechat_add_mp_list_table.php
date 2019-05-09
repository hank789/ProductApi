<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateAppScraperWechatAddMpListTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('scraper_wechat_add_mp_list', function(Blueprint $table)
		{
			$table->increments('_id');
			$table->string('name', 50)->default('')->comment('要添加的公众号名称');
			$table->string('wx_hao', 50)->default('')->comment('公众号的微信号');
			$table->timestamps();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('app_scraper_wechat_add_mp_list');
	}

}
