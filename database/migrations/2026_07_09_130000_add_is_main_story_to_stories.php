<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('stories', function (Blueprint $table) {
            if (!Schema::hasColumn('stories', 'is_main_story')) {
                $table->boolean('is_main_story')->default(false)->after('battle_id');
            }
        });
    }

    public function down()
    {
        Schema::table('stories', function (Blueprint $table) {
            if (Schema::hasColumn('stories', 'is_main_story')) {
                $table->dropColumn('is_main_story');
            }
        });
    }
};
