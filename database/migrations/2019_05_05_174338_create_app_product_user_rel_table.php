<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateAppProductUserRelTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('product_user_rel', function(Blueprint $table)
		{
			$table->increments('id');
			$table->integer('user_id')->unsigned()->default(0)->index('product_user_rel_user_id_index');
			$table->integer('tag_id')->unsigned()->default(0)->index('product_user_rel_tag_id_index');
			$table->tinyInteger('status')->default(1)->index('product_user_rel_status_index');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('app_product_user_rel');
	}

}
