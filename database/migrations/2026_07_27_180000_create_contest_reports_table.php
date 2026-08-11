<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contest_reports', function (Blueprint $table): void {
            $table->id();
            $table->string('contest_type', 16);
            $table->unsignedBigInteger('contest_id');
            $table->unsignedBigInteger('user_id');
            $table->string('kind', 16);
            $table->decimal('value', 18, 4)->nullable();
            $table->unsignedBigInteger('story_id')->nullable();
            $table->dateTime('period_started_at');
            $table->dateTime('period_ended_at');
            $table->timestamps();

            $table->index(
                ['contest_type', 'contest_id', 'user_id', 'period_started_at'],
                'contest_reports_period'
            );
            $table->index('story_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contest_reports');
    }
};
