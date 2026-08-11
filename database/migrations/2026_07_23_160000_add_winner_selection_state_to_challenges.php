<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('challenges', function (Blueprint $table): void {
            if (!Schema::hasColumn('challenges', 'winner_selection_status')) {
                $table->string('winner_selection_status')->nullable()->after('winner_selection');
            }

            if (!Schema::hasColumn('challenges', 'winner_selection_deadline')) {
                $table->dateTime('winner_selection_deadline')->nullable()->after('winner_selection_status');
            }

            if (!Schema::hasColumn('challenges', 'winner_selected_at')) {
                $table->dateTime('winner_selected_at')->nullable()->after('winner_selection_deadline');
            }

            if (!Schema::hasColumn('challenges', 'winner_decided_by_user_id')) {
                $table->integer('winner_decided_by_user_id')->nullable()->after('winner_selected_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('challenges', function (Blueprint $table): void {
            foreach ([
                'winner_decided_by_user_id',
                'winner_selected_at',
                'winner_selection_deadline',
                'winner_selection_status',
            ] as $column) {
                if (Schema::hasColumn('challenges', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
