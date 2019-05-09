<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateAppFeedbackTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('feedback', function(Blueprint $table)
		{
			$table->increments('id');
			$table->integer('user_id')->unsigned()->index('feedback_user_id_index');
			$table->integer('to_user_id')->unsigned()->default(0)->index('feedback_to_user_id_index');
			$table->integer('source_id')->unsigned();
			$table->string('source_type');
			$table->integer('star')->default(0);
			$table->string('content', 256)->nullable();
			$table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
			$table->index(['source_id','source_type'], 'feedback_source_id_source_type_index');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('app_feedback');
	}

}
