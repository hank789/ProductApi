<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateAppMediaTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('media', function(Blueprint $table)
		{
			$table->increments('id');
			$table->integer('model_id')->unsigned();
			$table->string('model_type');
			$table->string('collection_name');
			$table->string('name');
			$table->string('file_name');
			$table->string('mime_type')->nullable();
			$table->string('disk');
			$table->integer('size')->unsigned();
			$table->text('manipulations');
			$table->text('custom_properties');
			$table->integer('order_column')->unsigned()->nullable();
			$table->timestamps();
			$table->index(['model_id','model_type'], 'media_model_id_model_type_index');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('app_media');
	}

}
