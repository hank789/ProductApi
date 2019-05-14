<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateAppTaggablesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('taggables', function(Blueprint $table)
		{
			$table->increments('id');
			$table->integer('tag_id')->unsigned()->index('taggables_tag_id_index');
			$table->integer('taggable_id')->unsigned();
			$table->string('taggable_type');
			$table->timestamps();
			$table->tinyInteger('is_display')->default(1)->index('taggables_is_display_index');
			$table->index(['taggable_id','taggable_type'], 'taggables_taggable_id_taggable_type_index');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('app_taggables');
	}

}
