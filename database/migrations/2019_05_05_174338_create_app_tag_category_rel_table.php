<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateAppTagCategoryRelTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('tag_category_rel', function(Blueprint $table)
		{
			$table->increments('id');
			$table->integer('tag_id')->unsigned();
			$table->integer('category_id')->unsigned();
			$table->integer('status')->default(1)->index('tag_category_rel_status_index');
			$table->float('review_average_rate')->default(0.00);
			$table->integer('review_rate_sum')->default(0);
			$table->integer('reviews')->default(0);
			$table->integer('support_rate')->default(0);
			$table->integer('type')->default(0)->index('tag_category_rel_type_index');
			$table->dateTime('updated_at')->nullable();
			$table->unique(['tag_id','category_id'], 'tag_category_rel_tag_id_category_id_unique');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('app_tag_category_rel');
	}

}
