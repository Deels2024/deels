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
        Schema::create('game_sessions', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id')->index();
            $table->enum('game', ['chests', 'wheel'])->index()->default('chests');
            $table->enum('status', ['started','win', 'fail', 'aborted'])->default('started');
            $table->integer('tries')->default(0)->nullable();
            $table->integer('prize')->default(0)->nullable();
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
        Schema::dropIfExists('game_sessions');
    }
};
