<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateAppUserDataTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('user_data', function(Blueprint $table)
		{
			$table->integer('user_id')->unsigned()->primary();
			$table->integer('user_level')->default(1);
			$table->integer('coins')->unsigned()->default(0);
			$table->integer('credits')->unsigned()->default(0);
			$table->dateTime('registered_at')->nullable();
			$table->dateTime('last_visit')->nullable();
			$table->string('last_login_ip')->nullable();
			$table->string('latitude')->default('')->comment('纬度');
			$table->string('longitude')->default('')->comment('经度');
			$table->string('geohash')->default('')->index('user_data_geohash_index');
			$table->integer('questions')->unsigned()->default(0);
			$table->integer('articles')->unsigned()->default(0);
			$table->integer('answers')->unsigned()->default(0);
			$table->integer('adoptions')->unsigned()->default(0);
			$table->integer('supports')->unsigned()->default(0);
			$table->integer('followers')->unsigned()->default(0);
			$table->integer('views')->unsigned()->default(0);
			$table->tinyInteger('email_status')->default(0);
			$table->tinyInteger('mobile_status')->default(0);
			$table->tinyInteger('authentication_status')->default(0);
			$table->tinyInteger('is_company')->default(0);
			$table->tinyInteger('phone_public')->default(1);
			$table->tinyInteger('edu_public')->default(0);
			$table->tinyInteger('project_public')->default(0);
			$table->tinyInteger('job_public')->default(0);
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('app_user_data');
	}

}
