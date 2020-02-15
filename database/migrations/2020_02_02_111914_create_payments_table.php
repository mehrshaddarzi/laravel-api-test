<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('invoice_id')->nullable();
            $table->enum('method', [
                'BAMBORA',
                'STRIPE',
                'CASH',
                'INVOICE'
            ]);
            $table->float('amount')->nullable();
            $table->string('reference')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();

            $table->foreign('invoice_id')->references('id')->on('invoices');
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
            Schema::table('payments', function (Blueprint $table) {
                $table->dropForeign('payments_invoice_id_foreign');
            });
        }

        Schema::dropIfExists('payments');
    }
}
