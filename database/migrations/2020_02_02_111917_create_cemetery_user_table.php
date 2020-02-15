<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCemeteryUserTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cemetery_user', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('cemetery_id')->nullable();
            $table->unsignedInteger('user_id')->nullable();

            $table->foreign('cemetery_id')->references('id')->on('cemeteries');
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
            Schema::table('cemetery_user', function (Blueprint $table) {
                $table->dropForeign('cemetery_user_cemetery_id_foreign');
                $table->dropForeign('cemetery_user_user_id_foreign');
            });
        }

        Schema::dropIfExists('cemetery_user');
    }
}
