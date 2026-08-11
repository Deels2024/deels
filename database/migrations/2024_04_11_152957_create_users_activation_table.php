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
        Schema::create('users_activation', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->unsigned();
            $table->enum('type', ['email', 'phone'])->default('email');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('ucaller_id')->nullable();
            $table->string('unique')->nullable();
            $table->string('token')->nullable();
            $table->text('verify_phone_data')->nullable();
            $table->boolean('is_verified')->nullable();
            $table->timestamps();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_activated')->default(1);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users_activation');

        Schema::table('users', function (Blueprint $table) {

            $table->dropColumn('is_activated');

        });
    }
};
