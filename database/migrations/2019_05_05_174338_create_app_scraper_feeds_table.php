<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateAppScraperFeedsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('scraper_feeds', function(Blueprint $table)
		{
			$table->increments('id');
			$table->string('name', 191)->unique('scraper_feeds_name_unique');
			$table->string('source_type')->comment('文章来源格式,1:rss,2:atom');
			$table->string('source_link');
			$table->integer('group_id')->unsigned()->default(0)->index('scraper_feeds_group_id_index');
			$table->integer('user_id')->unsigned()->default(0)->index('scraper_feeds_user_id_index');
			$table->integer('is_auto_publish')->default(0);
			$table->string('keywords', 1024)->default('');
			$table->tinyInteger('status')->default(0);
			$table->softDeletes();
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
		Schema::drop('app_scraper_feeds');
	}

}
