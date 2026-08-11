<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('battles', function (Blueprint $table): void {
            if (!Schema::hasColumn('battles', 'rhythm')) {
                $table->string('rhythm')->nullable()->after('visibility');
            }
        });
    }

    public function down(): void
    {
        Schema::table('battles', function (Blueprint $table): void {
            if (Schema::hasColumn('battles', 'rhythm')) {
                $table->dropColumn('rhythm');
            }
        });
    }
};
