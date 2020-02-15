<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateServiceItemTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('service_item', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('service_id')->nullable();
            $table->unsignedInteger('user_id')->nullable();
            $table->string('name');
            $table->text('excerpt')->nullable();
            $table->longText('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('period')->default(0); //By month
            $table->string('price')->default(0);
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
        Schema::dropIfExists('service_item');
    }
}
