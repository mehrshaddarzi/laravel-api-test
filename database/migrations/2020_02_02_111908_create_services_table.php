<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateServicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('services', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('servicetype_id');
            $table->string('name');
            $table->string('coverphoto')->nullable();
            $table->string('icon')->nullable();
            $table->string('description')->nullable();
            $table->string('avg_price')->nullable();
            $table->string('commission_percentage')->nullable();
            $table->longText('commission_desc')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();

            $table->foreign('servicetype_id')->references('id')->on('servicetypes')->onDelete('cascade');
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
            Schema::table('services', function (Blueprint $table) {
                $table->dropForeign('services_servicetype_id_foreign');
            });
        }

        Schema::dropIfExists('services');
    }
}
