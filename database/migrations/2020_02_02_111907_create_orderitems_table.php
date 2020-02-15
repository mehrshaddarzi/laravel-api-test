<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOrderitemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('orderitems', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('order_id')->nullable();
            $table->unsignedBigInteger('service_item_id')->nullable();
            $table->string('amount');
            $table->longtext('description')->nullable();
            $table->timestamp('due_date')->nullable();
            $table->boolean('is_custom_offer');
            $table->enum('offer_status', [
                'DRAFT',
                'SENT',
                'APPROVED',
                'MODIFY',
                'REJECT'
            ])->default('DRAFT');
            $table->integer('sales_commission');
            $table->enum('billing_type', [
                'ONETIME',
                'ANNUALY',
                'MONTHLY',
                'QUARTERLY'
            ])->default('ONETIME');
            $table->enum('status', [
                'NEW',
                'SCHEDULED',
                'INPROGRESS',
                'DONE',
                'PENDING',
                'CANCELLED'
            ])->default('NEW');
            $table->string('photo')->nullable();
            $table->string('icon')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();

            $table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');
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
            Schema::table('orderitems', function (Blueprint $table) {
                $table->dropForeign('orderitems_order_id_foreign');
            });
        }

        Schema::dropIfExists('orderitems');
    }
}
