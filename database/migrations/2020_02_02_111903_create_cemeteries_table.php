<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCemeteriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cemeteries', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('city_id')->nullable();
            $table->string('name');
            $table->string('lat')->nullable();
            $table->string('long')->nullable();
            $table->string('address')->nullable();
            $table->string('photo')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();

            $table->foreign('city_id')->references('id')->on('cities')->onDelete('cascade');
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
            Schema::table('cemeteries', function (Blueprint $table) {
                $table->dropForeign('cemeteries_city_id_foreign');
            });
        }

        Schema::dropIfExists('cemeteries');
    }
}
