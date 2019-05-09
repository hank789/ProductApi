<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateAppSupportsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('supports', function(Blueprint $table)
		{
			$table->increments('id');
			$table->integer('user_id')->unsigned()->nullable()->index('supports_user_id');
			$table->integer('refer_user_id')->unsigned()->default(0)->index('supports_refer_user_id_index');
			$table->integer('supportable_id')->unsigned();
			$table->string('supportable_type');
			$table->timestamps();
			$table->index(['supportable_id','supportable_type'], 'supports_supportable_id_supportable_type_index');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('app_supports');
	}

}
