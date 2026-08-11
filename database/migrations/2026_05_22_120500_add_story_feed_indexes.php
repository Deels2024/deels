<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('stories', function (Blueprint $table): void {
            $table->index(['active', 'declined', 'created_at'], 'stories_active_declined_created_at_idx');
            $table->index(['user_id', 'active', 'declined'], 'stories_user_active_declined_idx');
        });

        Schema::table('likes', function (Blueprint $table): void {
            $table->index(['user_id', 'story_id'], 'likes_user_story_idx');
            $table->index(['story_id', 'user_id'], 'likes_story_user_idx');
        });

        Schema::table('views', function (Blueprint $table): void {
            $table->index(['user_id', 'story_id'], 'views_user_story_idx');
            $table->index(['story_id', 'user_id'], 'views_story_user_idx');
        });
    }

    public function down(): void
    {
        Schema::table('stories', function (Blueprint $table): void {
            $table->dropIndex('stories_active_declined_created_at_idx');
            $table->dropIndex('stories_user_active_declined_idx');
        });

        Schema::table('likes', function (Blueprint $table): void {
            $table->dropIndex('likes_user_story_idx');
            $table->dropIndex('likes_story_user_idx');
        });

        Schema::table('views', function (Blueprint $table): void {
            $table->dropIndex('views_user_story_idx');
            $table->dropIndex('views_story_user_idx');
        });
    }
};
