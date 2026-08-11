<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_events', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('user_id')->index();
            $table->string('type', 64);
            $table->string('source_type', 32)->nullable();
            $table->unsignedBigInteger('source_id')->nullable();
            $table->string('result', 32)->nullable();
            $table->json('data');
            $table->timestamp('expires_at')->index();
            $table->timestamp('dismissed_at')->nullable();
            $table->timestamps();

            $table->unique(
                ['user_id', 'type', 'source_type', 'source_id', 'result'],
                'user_events_source_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_events');
    }
};
