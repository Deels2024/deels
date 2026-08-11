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
        Schema::create('tg_messages', function (Blueprint $table) {
            $table->increments('id');
            $table->string('chat_id')->nullable();
            $table->string('user_id')->nullable();
            $table->string('username')->nullable();
            $table->string('firstname')->nullable();
            $table->string('lastname')->nullable();
            $table->integer('bot_message_id')->nullable();
            $table->longText('last_message')->nullable();
            $table->string('command')->nullable();
            $table->string('lang')->nullable();
            $table->integer('uses')->default(0);
            $table->timestamp('use_at')->nullable();
            $table->timestamp('support_at')->nullable();
            $table->boolean('active')->default(1);
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
        Schema::dropIfExists('tg_messages');
    }
};
