<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOffersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('offers', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('orderitem_id')->nullable();
            $table->float('amount')->default(0);
            $table->timestamp('validity_time')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();

            $table->foreign('orderitem_id')->references('id')->on('orderitems');
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
            Schema::table('offers', function (Blueprint $table) {
                $table->dropForeign('offers_orderitem_id_foreign');
            });
        }

        Schema::dropIfExists('offers');
    }
}
