<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateAppSubmissionsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('submissions', function(Blueprint $table)
		{
			$table->increments('id');
			$table->string('slug')->unique('submissions_slug_unique');
			$table->string('title', 6000);
			$table->string('type', 16)->index('submissions_type_index');
			$table->text('data');
			$table->string('rate', 16)->nullable()->default('0')->index('submissions_rate_index');
			$table->boolean('rate_star')->default(0);
			$table->integer('user_id')->unsigned()->index('submissions_user_id_index');
			$table->integer('status')->unsigned()->default(1)->index('submissions_status_index');
			$table->integer('top')->default(0)->index('submissions_top_index');
			$table->boolean('hide')->default(0);
			$table->integer('is_recommend')->default(0)->index('submissions_is_recommend_index');
			$table->boolean('public')->default(1)->index('submissions_public_index');
			$table->integer('views')->unsigned()->default(0);
			$table->integer('group_id')->unsigned()->default(0)->index('submissions_group_id_index');
			$table->integer('support_type')->unsigned()->default(1);
			$table->integer('category_id')->unsigned()->index('submissions_category_id_index');
			$table->integer('author_id')->default(0)->index('submissions_author_id_index');
			$table->integer('upvotes')->default(0);
			$table->integer('downvotes')->default(0);
			$table->integer('comments_number')->default(0);
			$table->integer('collections')->unsigned()->default(0);
			$table->integer('share_number')->default(0);
			$table->dateTime('approved_at')->nullable();
			$table->softDeletes();
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
		Schema::drop('app_submissions');
	}

}
