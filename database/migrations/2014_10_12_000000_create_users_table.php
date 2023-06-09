<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('users')) {
            Schema::create('users', function (Blueprint $table) {
                $table->id();
                $table->string('firstName', 40)->nullable();
                $table->string('middleName', 40)->nullable();
                $table->string('lastName', 40)->nullable();
                $table->string('profilPhoto', 255)->nullable();
                $table->string('email')->unique();
                $table->string('mobile', 18)->unique();
                $table->timestamp("dateOfBirth")->nullable();
                $table->enum("gender", ['Male', "Female", "Other"])->nullable();
                $table->timestamp('email_verified_at')->nullable();
                $table->timestamp('mobile_verified_at')->nullable();
                $table->string('password');
                $table->rememberToken();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
};
