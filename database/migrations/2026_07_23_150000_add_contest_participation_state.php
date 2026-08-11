<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stories', function (Blueprint $table): void {
            $table->timestamp('withdrawn_at')->nullable()->after('blocked_at')->index();
        });

        Schema::create('contest_participations', function (Blueprint $table): void {
            $table->id();
            $table->string('contest_type', 16);
            $table->unsignedBigInteger('contest_id');
            $table->unsignedBigInteger('user_id');
            $table->string('status', 16)->default('active');
            $table->timestamps();

            $table->unique(['contest_type', 'contest_id', 'user_id'], 'contest_participation_unique');
            $table->index(['contest_type', 'contest_id', 'status'], 'contest_participation_status');
        });

        Schema::table('battles', function (Blueprint $table): void {
            $table->unsignedBigInteger('loser_user_id')->nullable()->after('called_user_id')->index();
        });
    }

    public function down(): void
    {
        Schema::table('battles', function (Blueprint $table): void {
            $table->dropColumn('loser_user_id');
        });
        Schema::dropIfExists('contest_participations');
        Schema::table('stories', function (Blueprint $table): void {
            $table->dropColumn('withdrawn_at');
        });
    }
};
