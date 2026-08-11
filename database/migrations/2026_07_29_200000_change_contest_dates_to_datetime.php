<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('challenges', function (Blueprint $table): void {
            $table->dateTime('date_from')->nullable()->change();
            $table->dateTime('date_to')->nullable()->change();
        });

        Schema::table('battles', function (Blueprint $table): void {
            $table->dateTime('date_from')->nullable()->change();
            $table->dateTime('date_to')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('challenges', function (Blueprint $table): void {
            $table->date('date_from')->nullable()->change();
            $table->date('date_to')->nullable()->change();
        });

        Schema::table('battles', function (Blueprint $table): void {
            $table->date('date_from')->nullable()->change();
            $table->date('date_to')->nullable()->change();
        });
    }
};
