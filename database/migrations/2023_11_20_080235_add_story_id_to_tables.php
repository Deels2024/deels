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
        Schema::table('likes', function(Blueprint $table) {
            $table->integer('story_id')->after('campaign_id')->nullable();
        });
        Schema::table('comments', function(Blueprint $table) {
            $table->integer('story_id')->after('campaign_id')->nullable();
        });
        Schema::table('payments', function(Blueprint $table) {
            $table->integer('story_id')->after('campaign_id')->nullable();
        });
        Schema::table('media', function(Blueprint $table) {
            $table->integer('story_id')->nullable();
        });


    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('likes', function(Blueprint $table) {
            $table->dropColumn('story_id');
        });
        Schema::table('comments', function(Blueprint $table) {
            $table->dropColumn('story_id');
        });
        Schema::table('payments', function(Blueprint $table) {
            $table->dropColumn('story_id');
        });
        Schema::table('media', function(Blueprint $table) {
            $table->dropColumn('story_id');
        });
    }
};
