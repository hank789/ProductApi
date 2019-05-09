<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateAppCategoriesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('categories', function(Blueprint $table)
		{
			$table->increments('id');
			$table->integer('parent_id')->default(0);
			$table->integer('grade')->default(1);
			$table->string('name');
			$table->string('icon')->nullable();
			$table->string('slug', 128)->unique('categories_slug_unique');
			$table->string('type', 64);
			$table->string('summary', 2048)->default('');
			$table->integer('sort')->default(0);
			$table->string('role_id', 64)->nullable();
			$table->smallInteger('status')->default(1);
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
		Schema::drop('app_categories');
	}

}
