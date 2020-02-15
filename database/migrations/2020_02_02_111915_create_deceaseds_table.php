<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDeceasedsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('deceaseds', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('gravestone_id')->nullable();
            $table->string('first_name');
            $table->string('last_name');
            $table->date('birth_date')->nullable();
            $table->date('death_date')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();

            $table->foreign('gravestone_id')->references('id')->on('gravestones');
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
            Schema::table('deceaseds', function (Blueprint $table) {
                $table->dropForeign('deceaseds_gravestone_id_foreign');
            });
        }

        Schema::dropIfExists('deceaseds');
    }
}
