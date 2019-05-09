<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateAppDownvotesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('downvotes', function(Blueprint $table)
		{
			$table->increments('id');
			$table->integer('user_id')->unsigned()->nullable();
			$table->integer('refer_user_id')->unsigned()->default(0)->index('downvotes_refer_user_id_index');
			$table->integer('source_id')->unsigned();
			$table->string('source_type');
			$table->timestamps();
			$table->index(['source_id','source_type'], 'downvotes_source_id_source_type_index');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('app_downvotes');
	}

}
