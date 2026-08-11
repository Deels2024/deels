<?php

declare(strict_types=1);

namespace Tests\Feature\Support;

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

trait CreatesCharacterizationSchema
{
    protected function createCharacterizationSchema(): void
    {
        Schema::dropAllTables();

        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->string('name')->nullable();
            $table->string('username')->nullable();
            $table->string('email')->nullable();
            $table->string('password')->nullable();
            $table->string('avatar')->nullable();
            $table->string('gender')->nullable();
            $table->string('device_name')->nullable();
            $table->string('user_type')->nullable();
            $table->string('ip_address')->nullable();
            $table->json('user_data')->nullable();
            $table->json('meta_data')->nullable();
            $table->timestamp('banned_till')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('personal_access_tokens', function (Blueprint $table): void {
            $table->id();
            $table->morphs('tokenable');
            $table->string('name');
            $table->string('token', 64)->unique();
            $table->text('abilities')->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });

        Schema::create('media', function (Blueprint $table): void {
            $table->increments('id');
            $table->integer('user_id')->nullable();
            $table->string('name')->nullable();
            $table->string('slug')->nullable();
            $table->string('slug_ext')->nullable();
            $table->string('file_size')->nullable();
            $table->string('mime_type')->nullable();
            $table->string('folder')->nullable();
            $table->string('path')->nullable();
            $table->string('thumbnail')->nullable();
            $table->string('video_preview')->nullable();
            $table->integer('sort_order')->default(0)->nullable();
            $table->timestamps();
        });

        Schema::create('challenges', function (Blueprint $table): void {
            $table->id();
            $table->integer('user_id')->nullable();
            $table->integer('media_id')->nullable();
            $table->string('title')->nullable();
            $table->longText('description')->nullable();
            $table->boolean('active')->default(false);
            $table->boolean('declined')->default(false);
            $table->boolean('finished')->default(false);
            $table->timestamp('finished_at')->nullable();
            $table->boolean('frozen')->default(false);
            $table->boolean('started')->default(false);
            $table->integer('cost')->nullable();
            $table->json('moderation')->nullable();
            $table->boolean('ai_moderated')->default(false);
            $table->timestamp('finish')->nullable();
            $table->timestamp('blocked_at')->nullable();
            $table->integer('participants_count')->nullable();
            $table->string('visibility')->nullable();
            $table->string('rhythm')->nullable();
            $table->string('checkin')->nullable();
            $table->timestamp('date_from')->nullable();
            $table->timestamp('date_to')->nullable();
            $table->json('invite_user_ids')->nullable();
            $table->timestamps();
        });

        Schema::create('battles', function (Blueprint $table): void {
            $table->id();
            $table->integer('user_id')->nullable();
            $table->integer('media_id')->nullable();
            $table->integer('called_user_id')->nullable();
            $table->integer('loser_user_id')->nullable();
            $table->string('title')->nullable();
            $table->longText('description')->nullable();
            $table->boolean('active')->default(false);
            $table->boolean('declined')->default(false);
            $table->boolean('finished')->default(false);
            $table->timestamp('finished_at')->nullable();
            $table->boolean('frozen')->default(false);
            $table->boolean('started')->default(false);
            $table->integer('cost')->nullable();
            $table->json('moderation')->nullable();
            $table->boolean('ai_moderated')->default(false);
            $table->timestamp('finish')->nullable();
            $table->timestamp('blocked_at')->nullable();
            $table->integer('participants_count')->nullable();
            $table->string('visibility')->nullable();
            $table->string('checkin')->nullable();
            $table->timestamp('date_from')->nullable();
            $table->timestamp('date_to')->nullable();
            $table->json('invite_user_ids')->nullable();
            $table->timestamps();
        });

        Schema::create('contest_notification_publications', function (Blueprint $table): void {
            $table->id();
            $table->string('contest_type', 16);
            $table->unsignedBigInteger('contest_id');
            $table->unsignedInteger('version')->default(1);
            $table->timestamps();
            $table->unique(['contest_type', 'contest_id']);
        });

        Schema::create('contest_notification_deliveries', function (Blueprint $table): void {
            $table->id();
            $table->string('contest_type', 16);
            $table->unsignedBigInteger('contest_id');
            $table->unsignedBigInteger('user_id');
            $table->string('kind', 64);
            $table->timestamps();
            $table->unique(['contest_type', 'contest_id', 'user_id', 'kind']);
        });

        Schema::create('user_events', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('user_id')->index();
            $table->string('type', 64);
            $table->string('source_type', 32)->nullable();
            $table->unsignedBigInteger('source_id')->nullable();
            $table->string('result', 32)->nullable();
            $table->json('data');
            $table->timestamp('expires_at')->index();
            $table->timestamp('dismissed_at')->nullable();
            $table->timestamps();
            $table->unique(['user_id', 'type', 'source_type', 'source_id', 'result']);
        });

        Schema::create('campaigns', function (Blueprint $table): void {
            $table->increments('id');
            $table->integer('user_id')->nullable();
            $table->integer('category_id')->nullable();
            $table->string('title')->nullable();
            $table->string('slug')->nullable();
            $table->tinyInteger('status')->nullable();
            $table->integer('health')->default(0);
            $table->json('moderation')->nullable();
            $table->boolean('ai_moderated')->default(false);
            $table->timestamps();
        });

        Schema::create('stories', function (Blueprint $table): void {
            $table->id();
            $table->integer('user_id')->nullable();
            $table->integer('media_id')->nullable();
            $table->integer('campaign_id')->nullable();
            $table->integer('challenge_id')->nullable();
            $table->integer('battle_id')->nullable();
            $table->longText('description')->nullable();
            $table->json('data')->nullable();
            $table->boolean('active')->default(true);
            $table->boolean('declined')->default(false);
            $table->boolean('broken')->default(false);
            $table->boolean('is_main_story')->nullable();
            $table->boolean('is_useful')->default(false);
            $table->json('moderation')->nullable();
            $table->boolean('ai_moderated')->default(false);
            $table->boolean('is_converted')->default(false);
            $table->boolean('frozen')->default(false);
            $table->boolean('banned')->default(false);
            $table->boolean('paid')->default(false);
            $table->integer('amount')->default(0)->nullable();
            $table->timestamp('blocked_at')->nullable();
            $table->timestamp('withdrawn_at')->nullable();
            $table->timestamps();
        });

        Schema::create('contest_participations', function (Blueprint $table): void {
            $table->id();
            $table->string('contest_type', 16);
            $table->unsignedBigInteger('contest_id');
            $table->unsignedBigInteger('user_id');
            $table->string('status', 16)->default('active');
            $table->timestamps();
            $table->unique(['contest_type', 'contest_id', 'user_id']);
        });

        Schema::create('contest_reports', function (Blueprint $table): void {
            $table->id();
            $table->string('contest_type', 16);
            $table->unsignedBigInteger('contest_id');
            $table->unsignedBigInteger('user_id');
            $table->string('kind', 16);
            $table->decimal('value', 18, 4)->nullable();
            $table->unsignedBigInteger('story_id')->nullable();
            $table->dateTime('period_started_at');
            $table->dateTime('period_ended_at');
            $table->timestamps();
        });

        Schema::create('followables', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('user_id')->index();
            $table->string('followable_type');
            $table->unsignedBigInteger('followable_id');
            $table->timestamp('accepted_at')->nullable();
            $table->timestamps();
        });

        Schema::create('abuses', function (Blueprint $table): void {
            $table->id();
            $table->integer('user_id')->nullable();
            $table->integer('abused_by')->nullable();
            $table->boolean('blocked')->default(false);
            $table->timestamps();
        });

        Schema::create('likes', function (Blueprint $table): void {
            $table->id();
            $table->integer('user_id')->nullable();
            $table->integer('story_id')->nullable();
            $table->integer('comment_id')->nullable();
            $table->integer('campaign_id')->nullable();
            $table->string('ip_address')->nullable();
            $table->timestamps();
        });

        Schema::create('dislikes', function (Blueprint $table): void {
            $table->id();
            $table->integer('user_id')->nullable();
            $table->integer('story_id')->nullable();
            $table->integer('campaign_id')->nullable();
            $table->string('ip_address')->nullable();
            $table->timestamps();
        });

        Schema::create('views', function (Blueprint $table): void {
            $table->id();
            $table->integer('user_id')->nullable();
            $table->integer('story_id')->nullable();
            $table->integer('campaign_id')->nullable();
            $table->timestamps();
        });

        Schema::create('comments', function (Blueprint $table): void {
            $table->id();
            $table->integer('user_id')->nullable();
            $table->integer('campaign_id')->nullable();
            $table->integer('story_id')->nullable();
            $table->integer('comment_id')->nullable();
            $table->longText('body')->nullable();
            $table->longText('comment')->nullable();
            $table->string('author_name')->nullable();
            $table->string('author_email')->nullable();
            $table->string('author_ip')->nullable();
            $table->boolean('approved')->default(false);
            $table->timestamps();
        });

        Schema::create('tags', function (Blueprint $table): void {
            $table->id();
            $table->string('title')->nullable();
            $table->timestamps();
        });

        Schema::create('story_tag', function (Blueprint $table): void {
            $table->integer('story_id');
            $table->integer('tag_id');
        });

        Schema::create('wallets', function (Blueprint $table): void {
            $table->bigIncrements('id');
            $table->string('holder_type');
            $table->unsignedBigInteger('holder_id');
            $table->string('name');
            $table->string('slug')->index();
            $table->uuid('uuid')->unique();
            $table->string('description')->nullable();
            $table->json('meta')->nullable();
            $table->decimal('balance', 64, 0)->default(0);
            $table->unsignedSmallInteger('decimal_places')->default(2);
            $table->timestamps();
            $table->unique(['holder_type', 'holder_id', 'slug']);
        });

        Schema::create('transactions', function (Blueprint $table): void {
            $table->bigIncrements('id');
            $table->string('payable_type')->nullable();
            $table->unsignedBigInteger('payable_id')->nullable();
            $table->string('wallet_id')->nullable();
            $table->string('type')->nullable();
            $table->decimal('amount', 64, 0)->default(0);
            $table->boolean('confirmed')->default(true);
            $table->json('meta')->nullable();
            $table->uuid('uuid')->nullable();
            $table->timestamps();
        });
    }

    protected function createCharacterizationUserWithWallets(array $attributes): User
    {
        $email = $attributes['email'] ?? Str::uuid() . '@example.test';
        $user = User::create($attributes + [
            'email' => $email,
            'username' => Str::before($email, '@'),
        ]);

        foreach (['payments' => 'Payments', 'default' => 'Default'] as $slug => $name) {
            DB::table('wallets')->insert([
                'holder_type' => User::class,
                'holder_id' => $user->id,
                'name' => $name,
                'slug' => $slug,
                'uuid' => (string) Str::uuid(),
                'meta' => json_encode(['currency' => 'COINS']),
                'balance' => 0,
                'decimal_places' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return $user;
    }
}
