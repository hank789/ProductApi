<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateAppUserTagsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('user_tags', function(Blueprint $table)
		{
			$table->increments('id');
			$table->integer('user_id')->unsigned()->index('user_tags_user_id_index');
			$table->integer('tag_id')->unsigned()->index('user_tags_tag_id_index');
			$table->integer('questions')->unsigned()->default(0);
			$table->integer('articles')->unsigned()->default(0);
			$table->integer('answers')->unsigned()->default(0);
			$table->integer('supports')->unsigned()->default(0);
			$table->integer('adoptions')->unsigned()->default(0);
			$table->integer('skills')->unsigned()->default(0);
			$table->integer('industries')->unsigned()->default(0);
			$table->timestamps();
			$table->integer('views')->unsigned()->default(0);
			$table->integer('region')->unsigned()->default(0);
			$table->integer('role')->unsigned()->default(0);
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('app_user_tags');
	}

}
