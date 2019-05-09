<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateAppCommentsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('comments', function(Blueprint $table)
		{
			$table->increments('id');
			$table->integer('user_id')->unsigned()->index('comments_user_id_index');
			$table->text('content', 65535);
			$table->integer('source_id')->unsigned();
			$table->string('source_type');
			$table->integer('to_user_id')->unsigned()->nullable();
			$table->integer('level')->default(0);
			$table->integer('parent_id')->unsigned()->default(0)->index('comments_parent_id_index');
			$table->string('mentions')->nullable();
			$table->boolean('status')->default(1);
			$table->boolean('comment_type')->default(0)->index('comments_comment_type_index');
			$table->integer('supports')->default(0);
			$table->boolean('device')->default(1);
			$table->timestamps();
			$table->index(['source_id','source_type'], 'comments_source_id_source_type_index');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('app_comments');
	}

}
