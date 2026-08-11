<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contest_notification_publications', function (Blueprint $table): void {
            $table->id();
            $table->string('contest_type', 16);
            $table->unsignedBigInteger('contest_id');
            $table->unsignedInteger('version')->default(1);
            $table->timestamps();
            $table->unique(['contest_type', 'contest_id'], 'contest_notification_publication_unique');
        });

        Schema::create('contest_notification_deliveries', function (Blueprint $table): void {
            $table->id();
            $table->string('contest_type', 16);
            $table->unsignedBigInteger('contest_id');
            $table->unsignedBigInteger('user_id');
            $table->string('kind', 64);
            $table->timestamps();
            $table->unique(
                ['contest_type', 'contest_id', 'user_id', 'kind'],
                'contest_notification_delivery_unique'
            );
        });

        $this->rememberAlreadyPublishedContests('challenges', 'challenge');
        $this->rememberAlreadyPublishedContests('battles', 'battle');
    }

    public function down(): void
    {
        Schema::dropIfExists('contest_notification_deliveries');
        Schema::dropIfExists('contest_notification_publications');
    }

    private function rememberAlreadyPublishedContests(string $table, string $type): void
    {
        if (!Schema::hasTable($table)) {
            return;
        }

        DB::table($table)
            ->where('active', true)
            ->where('declined', false)
            ->orderBy('id')
            ->chunkById(500, function ($contests) use ($type): void {
                foreach ($contests as $contest) {
                    DB::table('contest_notification_publications')->insertOrIgnore([
                        'contest_type' => $type,
                        'contest_id' => $contest->id,
                        'version' => 1,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    foreach ($this->decodeIds($contest->invite_user_ids ?? null) as $userId) {
                        DB::table('contest_notification_deliveries')->insertOrIgnore([
                            'contest_type' => $type,
                            'contest_id' => $contest->id,
                            'user_id' => $userId,
                            'kind' => 'invite',
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }

                    if ($type === 'battle' && !empty($contest->called_user_id)) {
                        DB::table('contest_notification_deliveries')->insertOrIgnore([
                            'contest_type' => $type,
                            'contest_id' => $contest->id,
                            'user_id' => (int) $contest->called_user_id,
                            'kind' => 'call',
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }
            });
    }

    private function decodeIds($value): array
    {
        $ids = is_string($value) ? json_decode($value, true) : $value;

        return collect((array) $ids)
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->unique()
            ->values()
            ->all();
    }
};
