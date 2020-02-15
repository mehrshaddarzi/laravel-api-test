<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCitiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cities', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('region_id')->nullable();
            $table->string('name');
            $table->string('lat')->nullable();
            $table->string('long')->nullable();
            $table->string('address')->nullable();
            $table->string('photo')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();

            $table->foreign('region_id')->references('id')->on('regions')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cities');
    }
}
