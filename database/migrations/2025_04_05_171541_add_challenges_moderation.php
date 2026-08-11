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
        Schema::table('challenges', function (Blueprint $table) {
            $table->boolean('frozen')->default(0)->nullable();
            $table->timestamp('frozen_at')->nullable();
        });

        Schema::table('stories', function (Blueprint $table) {
            $table->boolean('frozen')->default(0)->nullable();
            $table->boolean('banned')->default(0)->nullable();
            $table->string('banned_reason')->nullable();
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('challenges', function (Blueprint $table) {
            $table->dropColumn('frozen');
            $table->dropColumn('frozen_at');
        });

        Schema::table('stories', function (Blueprint $table) {
            $table->dropColumn('frozen');
            $table->dropColumn('banned');
            $table->dropColumn('banned_reason');
        });
    }
};
