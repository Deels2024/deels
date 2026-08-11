<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('challenges', function (Blueprint $table): void {
            if (!Schema::hasColumn('challenges', 'reward_amount')) {
                $table->unsignedInteger('reward_amount')->nullable()->after('amount');
            }
            if (!Schema::hasColumn('challenges', 'winner_selection')) {
                $table->string('winner_selection')->nullable()->after('checkin');
            }
            if (!Schema::hasColumn('challenges', 'invite_user_ids')) {
                $table->json('invite_user_ids')->nullable()->after('winner_selection');
            }
        });

        Schema::table('battles', function (Blueprint $table): void {
            if (!Schema::hasColumn('battles', 'reward_amount')) {
                $table->unsignedInteger('reward_amount')->nullable()->after('amount');
            }
            if (!Schema::hasColumn('battles', 'winner_selection')) {
                $table->string('winner_selection')->nullable()->after('checkin');
            }
            if (!Schema::hasColumn('battles', 'invite_user_ids')) {
                $table->json('invite_user_ids')->nullable()->after('winner_selection');
            }
            if (!Schema::hasColumn('battles', 'called_user_id')) {
                $table->unsignedBigInteger('called_user_id')->nullable()->after('invite_user_ids');
            }
        });
    }

    public function down(): void
    {
        Schema::table('challenges', function (Blueprint $table): void {
            $columns = array_values(array_filter(
                ['reward_amount', 'winner_selection', 'invite_user_ids'],
                fn ($column) => Schema::hasColumn('challenges', $column)
            ));

            if ($columns) {
                $table->dropColumn($columns);
            }
        });

        Schema::table('battles', function (Blueprint $table): void {
            $columns = array_values(array_filter(
                ['reward_amount', 'winner_selection', 'invite_user_ids', 'called_user_id'],
                fn ($column) => Schema::hasColumn('battles', $column)
            ));

            if ($columns) {
                $table->dropColumn($columns);
            }
        });
    }
};
