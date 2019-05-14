<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateAppScraperNewsInfoTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('scraper_news_info', function(Blueprint $table)
		{
			$table->increments('_id');
			$table->string('title', 1024)->default('')->comment('文章标题');
			$table->string('source_url', 1024)->nullable()->default('')->comment('原文地址');
			$table->string('cover_url', 1024)->nullable()->default('')->comment('封面图URL');
			$table->text('description', 65535)->nullable()->comment('文章摘要');
			$table->text('body', 65535);
			$table->dateTime('date_time')->nullable()->index('scraper_news_info_date_time_index')->comment('文章推送时间');
			$table->integer('mp_id')->default(0)->index('scraper_news_info_mp_id_index')->comment('对应的公众号ID');
			$table->integer('read_count')->default(0)->comment('阅读数');
			$table->integer('like_count')->default(0)->comment('点攒数');
			$table->integer('comment_count')->default(0)->comment('评论数');
			$table->string('content_url', 1024)->default('')->comment('文章永久地址');
			$table->string('mobile_url', 1024)->nullable()->default('')->comment('手机站url');
			$table->string('site_name')->nullable()->default('')->comment('站点名字');
			$table->string('author', 50)->nullable()->default('')->comment('作者');
			$table->integer('msg_index')->default(0)->comment('一次群发中的图文顺序 1是头条 ');
			$table->integer('copyright_stat')->default(0)->comment('11表示原创 其它表示非原创');
			$table->integer('qunfa_id')->default(0)->comment('群发消息ID');
			$table->integer('source_type')->default(1)->index('scraper_news_info_source_type_index')->comment('来源:1微信公众号,2feed');
			$table->integer('type')->default(0)->index('scraper_news_info_type')->comment('消息类型');
			$table->integer('topic_id')->unsigned()->default(0)->index('scraper_news_info_topic_id_index');
			$table->tinyInteger('status')->default(1)->index('scraper_news_info_status_index');
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
		Schema::drop('app_scraper_news_info');
	}

}
