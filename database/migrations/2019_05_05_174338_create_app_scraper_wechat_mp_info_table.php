<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateAppScraperWechatMpInfoTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('scraper_wechat_mp_info', function(Blueprint $table)
		{
			$table->increments('_id');
			$table->string('name', 50)->default('')->comment('公众号名称');
			$table->string('wx_hao', 20)->default('')->comment('公众号的微信号');
			$table->string('newrank_id', 64)->default('');
			$table->string('company', 100)->default('')->comment('主体名称');
			$table->string('description', 200)->default('')->comment('功能简介');
			$table->string('logo_url', 200)->default('')->comment('logo url');
			$table->string('qr_url', 200)->default('')->comment('二维码URL');
			$table->dateTime('create_time')->nullable()->comment('加入牛榜时间');
			$table->dateTime('update_time')->nullable()->comment('最后更新时间');
			$table->integer('rank_article_release_count')->default(0)->comment('群发次数');
			$table->integer('rank_article_count')->default(0)->comment('群发篇数');
			$table->integer('last_qunfa_id')->default(0)->comment('最后的群发ID');
			$table->dateTime('last_qufa_time')->nullable()->comment('最后一次群发的时间');
			$table->string('wz_url', 300)->default('')->comment('最近文章URL');
			$table->integer('group_id')->unsigned()->default(0)->index('scraper_wechat_mp_info_group_id_index');
			$table->integer('user_id')->unsigned()->default(0)->index('scraper_wechat_mp_info_user_id_index');
			$table->integer('is_auto_publish')->default(0);
			$table->tinyInteger('status')->default(0);
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('app_scraper_wechat_mp_info');
	}

}
