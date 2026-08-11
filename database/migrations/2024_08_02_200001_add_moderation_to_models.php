<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('stories', function (Blueprint $table) {
            $table->json('moderation')->nullable();
            $table->boolean('ai_moderated')->default(0);
        });
        Schema::table('challenges', function (Blueprint $table) {
            $table->json('moderation')->nullable();
            $table->boolean('ai_moderated')->default(0);
        });
        Schema::table('campaigns', function (Blueprint $table) {
            $table->json('moderation')->nullable();
            $table->boolean('ai_moderated')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('stories', function (Blueprint $table) {
            $table->dropColumn('moderation');
            $table->dropColumn('ai_moderated');
        });
        Schema::table('challenges', function (Blueprint $table) {
            $table->dropColumn('moderation');
            $table->dropColumn('ai_moderated');
        });
        Schema::table('campaigns', function (Blueprint $table) {
            $table->dropColumn('moderation');
            $table->dropColumn('ai_moderated');
        });
    }
};
