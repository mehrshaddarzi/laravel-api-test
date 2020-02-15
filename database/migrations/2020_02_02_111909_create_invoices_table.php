<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateInvoicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('user_id')->nullable();
            $table->unsignedBigInteger('orderitem_id')->nullable();
            $table->timestamp('invoice_date')->nullable();
            $table->string('invoice_number');
            $table->string('total_amount');
            $table->string('vat_amount')->default(0);
            $table->string('description')->nullable();
            $table->enum('status', [
                'SENT',
                'PAID',
                'OVERDUE',
                'PARTIAL',
                'VOID'
            ])->default('SENT');
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
            Schema::table('invoices', function (Blueprint $table) {
                $table->dropForeign('invoices_order_id_foreign');
                $table->dropForeign('invoices_orderitem_id_foreign');
            });
        }

        Schema::dropIfExists('invoices');
    }
}
