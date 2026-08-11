<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->unsignedTinyInteger('phone_prompt_stage')->default(0)->after('phone');
            $table->timestamp('next_phone_prompt_at')->nullable()->after('phone_prompt_stage')->index();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn(['phone_prompt_stage', 'next_phone_prompt_at']);
        });
    }
};
