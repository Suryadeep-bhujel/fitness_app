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
        Schema::create('otp_lists', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("user_id")->nullable(); // use for logged in user 
            $table->integer("otp")->index();
            $table->string("username")->nullable()->index(); // use for not logged in user
            $table->timestamps(); 
            $table->foreign("user_id")->on("users")->references("id")->onDelete("CASCADE");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('otp_lists');
    }
};
