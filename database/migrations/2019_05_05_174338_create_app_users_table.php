<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateAppUsersTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('users', function(Blueprint $table)
		{
			$table->increments('id');
			$table->integer('role_id')->nullable();
			$table->string('uuid', 64)->nullable()->unique('users_uuid_unique');
			$table->integer('comment_karma')->default(0);
			$table->integer('submission_karma')->default(0);
			$table->integer('is_expert')->unsigned()->default(0);
			$table->string('rc_code', 8)->nullable()->unique('users_rc_code_unique');
			$table->integer('rc_uid')->nullable();
			$table->string('name');
			$table->string('realname')->default('');
			$table->string('email', 128)->nullable()->index('users_email_index');
			$table->string('mobile', 24)->nullable()->unique('users_mobile_unique');
			$table->string('password', 64);
			$table->boolean('gender')->nullable();
			$table->integer('info_complete_percent')->default(0);
			$table->string('avatar')->nullable();
			$table->date('birthday')->nullable();
			$table->string('province', 12)->nullable();
			$table->string('city', 12)->nullable();
			$table->string('hometown_province', 12)->nullable();
			$table->string('hometown_city', 12)->nullable();
			$table->string('address_detail')->default('');
			$table->string('company')->default('');
			$table->string('title')->nullable();
			$table->text('description', 65535)->nullable();
			$table->boolean('status')->default(1);
			$table->boolean('source')->default(0)->comment('注册来源');
			$table->string('site_notifications')->nullable();
			$table->string('email_notifications')->nullable();
			$table->string('remember_token', 100)->nullable();
			$table->string('last_login_token')->nullable()->comment('上次登录token');
			$table->timestamps();
			$table->string('current_app_version', 32)->default('1.0.0');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('app_users');
	}

}
