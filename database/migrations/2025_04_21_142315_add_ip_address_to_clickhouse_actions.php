<?php

use PhpClickHouseLaravel\Migration;
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
        static::write('ALTER TABLE actions ADD COLUMN ip_address Nullable(String) AFTER model_id');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        static::write('ALTER TABLE actions DROP COLUMN ip_address');
    }
};
