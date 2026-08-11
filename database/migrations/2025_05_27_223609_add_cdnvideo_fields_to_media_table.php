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
        Schema::table('media', function (Blueprint $table) {
            $table->text('hls_url')->nullable()->after('video_preview');
            $table->string('cdn_task_id')->nullable()->after('hls_url');
            $table->json('cdn_profiles')->nullable()->after('cdn_task_id'); // если нужно хранить инфу о профилях
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('media', function (Blueprint $table) {
            //
        });
    }
};
