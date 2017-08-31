<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUploadsTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('uploads', function (Blueprint $table) {
			$table->increments('id');
			$table->string('name');
			$table->string('type');
			$table->integer('size')->default(0);
			$table->string('token')->unique();
			$table->integer('created_by')->unsigned()->nullable();;
			$table->integer('updated_by')->unsigned()->nullable();;
			$table->timestamps();

			$table->foreign('created_by')
				->references('id')
				->on('users')
				->onDelete('set null');

			$table->foreign('updated_by')
				->references('id')
				->on('users')
				->onDelete('set null');
		});

		Schema::table('uploads', function (Blueprint $table) {
			$table->integer('parent_id')->nullable()->unsigned();

			$table->foreign('parent_id')
				->references('id')
				->on('uploads')
				->onUpdate('cascade')
				->onDelete('cascade');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('uploads', function (Blueprint $table) {
			$table->dropForeign(['parent_id']);
		});
		Schema::dropIfExists('uploads');
	}
}
