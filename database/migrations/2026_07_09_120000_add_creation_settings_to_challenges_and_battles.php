<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('challenges', function (Blueprint $table) {
            if (!Schema::hasColumn('challenges', 'date_from')) {
                $table->dateTime('date_from')->nullable()->after('start');
            }
            if (!Schema::hasColumn('challenges', 'date_to')) {
                $table->dateTime('date_to')->nullable()->after('date_from');
            }
            if (!Schema::hasColumn('challenges', 'participants_count')) {
                $table->integer('participants_count')->nullable()->after('min_participants');
            }
            if (!Schema::hasColumn('challenges', 'visibility')) {
                $table->string('visibility')->nullable()->after('participants_count');
            }
            if (!Schema::hasColumn('challenges', 'rhythm')) {
                $table->string('rhythm')->nullable()->after('visibility');
            }
            if (!Schema::hasColumn('challenges', 'checkin')) {
                $table->string('checkin')->nullable()->after('rhythm');
            }
        });

        Schema::table('battles', function (Blueprint $table) {
            if (!Schema::hasColumn('battles', 'date_from')) {
                $table->dateTime('date_from')->nullable()->after('start');
            }
            if (!Schema::hasColumn('battles', 'date_to')) {
                $table->dateTime('date_to')->nullable()->after('date_from');
            }
            if (!Schema::hasColumn('battles', 'participants_count')) {
                $table->integer('participants_count')->nullable()->after('min_participants');
            }
            if (!Schema::hasColumn('battles', 'visibility')) {
                $table->string('visibility')->nullable()->after('participants_count');
            }
            if (!Schema::hasColumn('battles', 'checkin')) {
                $table->string('checkin')->nullable()->after('visibility');
            }
        });
    }

    public function down()
    {
        Schema::table('battles', function (Blueprint $table) {
            foreach (['date_from', 'date_to', 'participants_count', 'visibility', 'checkin'] as $column) {
                if (Schema::hasColumn('battles', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::table('challenges', function (Blueprint $table) {
            foreach (['date_from', 'date_to', 'participants_count', 'visibility', 'rhythm', 'checkin'] as $column) {
                if (Schema::hasColumn('challenges', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
