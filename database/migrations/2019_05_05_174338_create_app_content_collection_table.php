<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateAppContentCollectionTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('content_collection', function(Blueprint $table)
		{
			$table->increments('id');
			$table->boolean('content_type')->default(0)->index('content_collection_content_type_index');
			$table->boolean('sort')->default(0)->index('content_collection_sort_index');
			$table->integer('source_id')->unsigned()->default(0)->index('content_collection_source_id_index');
			$table->text('content');
			$table->boolean('status')->default(1)->index('content_collection_status_index');
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
		Schema::drop('app_content_collection');
	}

}
