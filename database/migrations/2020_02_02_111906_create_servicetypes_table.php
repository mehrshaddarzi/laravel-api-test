<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateServicetypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('servicetypes', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('user_id');
            $table->string('name');
            $table->string('coverphoto')->nullable();
            $table->string('icon')->nullable();
            $table->longText('description')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('servicetypes');
    }
}
