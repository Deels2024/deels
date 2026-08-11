<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->unsignedTinyInteger('suspicious_violations')->default(0)->after('is_suspicious');
            $table->boolean('suspicious_moderation_pending')->default(false)->after('suspicious_violations')->index();
            $table->timestamp('suspicious_moderation_requested_at')->nullable()->after('suspicious_moderation_pending');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropIndex(['suspicious_moderation_pending']);
            $table->dropColumn([
                'suspicious_violations',
                'suspicious_moderation_pending',
                'suspicious_moderation_requested_at',
            ]);
        });
    }
};
