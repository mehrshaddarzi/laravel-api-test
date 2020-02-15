<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('email')->unique();
            $table->string('username')->nullable()->index();
            $table->string('password');
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('phone')->nullable();
            $table->string('avatar')->nullable();
            $table->string('address')->nullable();
            $table->string('physical_address')->nullable();
            $table->string('website')->nullable();
            $table->unsignedInteger('country_id')->nullable();
            $table->string('city')->nullable();
            $table->string('street')->nullable();
            $table->string('zipcode')->nullable();
            $table->unsignedInteger('role_id');
            $table->bigInteger('parent_id')->default(0);
            $table->string('company_name')->nullable();
            $table->string('company_registration_number')->nullable();
            $table->date('birthday')->nullable();
            $table->timestamp('last_login')->nullable();
            $table->string('status', 20)->index();
            $table->integer('two_factor_country_code')->nullable();
            $table->integer('two_factor_phone')->nullable();
            $table->text('two_factor_options')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->rememberToken();
            $table->timestamps();

            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('users');
    }
}
