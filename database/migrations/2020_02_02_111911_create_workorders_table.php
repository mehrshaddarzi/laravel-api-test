<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWorkordersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('workorders', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('user_id')->nullable();
            $table->unsignedBigInteger('orderitem_id')->nullable();
            $table->enum('status', [
                'PICKED',
                'SHIFTED',
                'DONE',
                'FAILED'
            ])->default('PICKED');
            $table->string('photo')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();

            $table->foreign('user_id')->references('id')->on('users');
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
            Schema::table('workorders', function (Blueprint $table) {
                $table->dropForeign('workorders_user_id_foreign');
                $table->dropForeign('workorders_orderitem_id_foreign');
            });
        }

        Schema::dropIfExists('workorders');
    }
}
