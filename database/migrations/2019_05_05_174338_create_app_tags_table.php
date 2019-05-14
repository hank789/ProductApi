<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateAppTagsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('tags', function(Blueprint $table)
		{
			$table->increments('id');
			$table->string('name', 128)->unique('tags_name_unique');
			$table->integer('category_id')->default(0);
			$table->string('logo', 128);
			$table->text('summary', 65535)->nullable();
			$table->text('description', 65535)->nullable();
			$table->integer('parent_id')->unsigned()->default(0)->index('tags_parent_id_index');
			$table->integer('followers')->unsigned()->default(0);
			$table->integer('reviews')->default(0);
			$table->tinyInteger('is_pro')->default(0);
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
		Schema::drop('app_tags');
	}

}
