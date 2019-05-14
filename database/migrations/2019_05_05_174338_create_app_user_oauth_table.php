<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateAppUserOauthTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('user_oauth', function(Blueprint $table)
		{
			$table->increments('id');
			$table->char('auth_type', 64)->index('user_oauth_auth_type_index');
			$table->char('nickname', 64);
			$table->char('avatar');
			$table->integer('user_id')->default(0)->index('user_oauth_user_id_index');
			$table->string('openid', 128)->index('user_oauth_openid_index');
			$table->string('unionid', 128)->nullable()->index('user_oauth_unionid_index');
			$table->string('access_token', 64);
			$table->string('refresh_token', 64)->nullable();
			$table->string('scope', 64)->nullable();
			$table->string('full_info', 2048)->nullable();
			$table->integer('expires_in');
			$table->tinyInteger('status')->default(1)->comment('状态:0未生效,1已生效');
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
		Schema::drop('app_user_oauth');
	}

}
