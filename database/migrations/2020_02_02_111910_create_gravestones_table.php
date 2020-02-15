<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGravestonesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('gravestones', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('orderitem_id')->nullable();
            $table->unsignedBigInteger('cemetery_id')->nullable();
            $table->longText('description')->nullable();
            $table->string('stonesize')->nullable();
            $table->string('grave_block')->nullable();
            $table->string('grave_column')->nullable();
            $table->string('grave_row')->nullable();
            $table->string('identifier_code')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();

            $table->foreign('orderitem_id')->references('id')->on('orderitems');
            $table->foreign('cemetery_id')->references('id')->on('cemeteries');
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
            Schema::table('gravestones', function (Blueprint $table) {
                $table->dropForeign('gravestones_cemetery_id_foreign');
                $table->dropForeign('gravestones_orderitem_id_foreign');
            });
        }

        Schema::dropIfExists('gravestones');
    }
}
