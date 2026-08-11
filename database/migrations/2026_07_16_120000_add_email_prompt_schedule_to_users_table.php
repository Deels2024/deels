<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->unsignedTinyInteger('email_prompt_stage')->default(0)->after('email');
            $table->timestamp('next_email_prompt_at')->nullable()->after('email_prompt_stage')->index();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn(['email_prompt_stage', 'next_email_prompt_at']);
        });
    }
};
