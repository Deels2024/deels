<?php

use PhpClickHouseLaravel\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        static::write('ALTER TABLE actions ADD COLUMN description Nullable(String) AFTER model_id');
        static::write('ALTER TABLE actions ADD COLUMN title Nullable(String) AFTER model_id');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        static::write('ALTER TABLE actions DROP COLUMN description');
        static::write('ALTER TABLE actions DROP COLUMN title');
    }
};
