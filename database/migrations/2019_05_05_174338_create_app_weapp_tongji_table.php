<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateAppWeappTongjiTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('weapp_tongji', function(Blueprint $table)
		{
			$table->increments('id');
			$table->integer('user_oauth_id')->unsigned()->index('weapp_tongji_user_oauth_id_index');
			$table->string('page')->index('weapp_tongji_page_index')->comment('页面路径');
			$table->string('start_time', 13)->comment('进入时间，毫秒');
			$table->string('end_time', 13)->comment('离开时间，毫秒');
			$table->integer('stay_time')->default(0)->comment('停留时间,毫秒');
			$table->integer('event_id')->unsigned()->index('weapp_tongji_event_id_index')->comment('事件id');
			$table->string('scene', 64)->index('weapp_tongji_scene_index')->comment('场景值');
			$table->integer('product_id')->unsigned()->default(0)->index('weapp_tongji_product_id_index');
			$table->integer('from_user_id')->unsigned()->default(0)->index('weapp_tongji_from_user_id_index');
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
		Schema::drop('app_weapp_tongji');
	}

}
