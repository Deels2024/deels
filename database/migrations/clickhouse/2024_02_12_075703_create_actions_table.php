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
        static::write('
            CREATE TABLE actions (
                user_id UInt64,
                type String,
                model String,
                model_id Nullable(UInt64),
                tags Nullable(String),
                created_at DateTime,
            )
            ENGINE = MergeTree()
            ORDER BY (user_id, created_at)
        ');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        static::write('DROP TABLE actions');
    }
};
