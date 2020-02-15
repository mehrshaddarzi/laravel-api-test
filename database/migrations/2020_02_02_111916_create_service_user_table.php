<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateServiceUserTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('service_user', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('service_id')->nullable();
            $table->unsignedInteger('user_id')->nullable();

            $table->foreign('service_id')->references('id')->on('services');
            $table->foreign('user_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (DB::getDriverName() != 'sqlite') {
            Schema::table('service_user', function (Blueprint $table) {
                $table->dropForeign('service_user_service_id_foreign');
                $table->dropForeign('service_user_user_id_foreign');
            });
        }

        Schema::dropIfExists('service_user');
    }
}
