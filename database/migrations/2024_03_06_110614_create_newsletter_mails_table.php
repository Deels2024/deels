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
        Schema::create('newsletter_mails', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('newsletter_id')->nullable();
            $table->integer('subscriber_id')->nullable();
            $table->string('token')->nullable();
            $table->string('email')->nullable();
            $table->enum('status', ['pending', 'success', 'fail'])->default('pending');
            $table->text('data')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('newsletter_mails');
    }
};
