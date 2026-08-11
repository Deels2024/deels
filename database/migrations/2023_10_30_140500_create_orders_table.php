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
        Schema::create('orders', function (Blueprint $table) {
            $table->increments('id');
            $table->float('amount');
            $table->integer('user_id');
            $table->string('model')->index();
            $table->string('type')->nullable();
            $table->string('payment_id')->nullable();
            $table->text('payment_url')->nullable();
            $table->string('order_id')->nullable();
            $table->unsignedInteger('model_id');
            $table->smallInteger('status')->default(0);
            $table->string('rebill_id')->nullable();
            $table->index(['user_id', 'model_id']);
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
        Schema::dropIfExists('orders');
    }
};
