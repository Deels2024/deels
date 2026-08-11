<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('challenges', function (Blueprint $table): void {
            $table->string('completion_status', 16)->nullable()->after('finished_at')->index();
        });

        Schema::table('battles', function (Blueprint $table): void {
            $table->string('completion_status', 16)->nullable()->after('finished_at')->index();
        });
    }

    public function down(): void
    {
        Schema::table('challenges', function (Blueprint $table): void {
            $table->dropColumn('completion_status');
        });

        Schema::table('battles', function (Blueprint $table): void {
            $table->dropColumn('completion_status');
        });
    }
};
