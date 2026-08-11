<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreatePostTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('posts', function (Blueprint $table): void {
            $table->increments('id');
            $table->integer('user_id')->nullable();
            $table->string('title')->nullable();
            $table->string('slug')->nullable();
            $table->longText('post_content')->nullable();
            $table->string('feature_image')->nullable();
            $table->enum('type', ['post', 'page'])->nullable();
            $table->tinyInteger('status')->nullable();
            $table->tinyInteger('show_in_header_menu')->nullable();
            $table->tinyInteger('show_in_footer_menu')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::drop('posts');
    }
}
