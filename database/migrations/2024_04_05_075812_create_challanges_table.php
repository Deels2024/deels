<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('challenges', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id')->nullable();
            $table->integer('media_id')->nullable();
            $table->longText('description')->nullable();
            $table->boolean('by_comments')->default(0);
            $table->boolean('by_likes')->default(0);
            $table->boolean('by_views')->default(0);
            $table->boolean('active')->default(0);
            $table->boolean('declined')->default(0);
            $table->integer('amount')->default(0)->nullable();
            $table->timestamp('finish')->nullable();
            $table->timestamp('blocked_at')->nullable();
            $table->string('reason')->nullable();
            $table->string('moderated')->nullable();
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
        Schema::dropIfExists('challenges');
    }
};
