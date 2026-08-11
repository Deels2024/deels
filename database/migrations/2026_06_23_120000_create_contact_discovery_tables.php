<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'phone_hash')) {
                $table->string('phone_hash', 64)->nullable()->after('phone')->index();
            }
        });

        Schema::dropIfExists('friend_suggestions');
        Schema::dropIfExists('user_vk_friends');
        Schema::dropIfExists('user_contact_hashes');
        Schema::dropIfExists('user_contact_imports');

        Schema::create('user_contact_imports', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('user_id');
            $table->string('status', 32)->default('pending')->index();
            $table->string('source', 32)->nullable();
            $table->timestamp('first_confirmed_at')->nullable();
            $table->timestamp('last_denied_at')->nullable();
            $table->timestamp('next_prompt_at')->nullable()->index();
            $table->timestamps();

            $table->unique('user_id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });

        Schema::create('user_contact_hashes', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('user_id');
            $table->string('phone_hash', 64);
            $table->timestamps();

            $table->unique(['user_id', 'phone_hash']);
            $table->index('phone_hash');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });

        Schema::create('user_vk_friends', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('user_id');
            $table->string('vk_id', 64);
            $table->timestamps();

            $table->unique(['user_id', 'vk_id']);
            $table->index('vk_id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });

        Schema::create('friend_suggestions', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('user_id');
            $table->unsignedInteger('suggested_user_id');
            $table->string('source', 32)->index();
            $table->timestamp('followed_at')->nullable();
            $table->timestamp('notified_at')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'suggested_user_id']);
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('suggested_user_id')->references('id')->on('users')->onDelete('cascade');
        });

        DB::table('users')
            ->whereNotNull('phone')
            ->whereNull('phone_hash')
            ->orderBy('id')
            ->chunkById(500, function ($users): void {
                foreach ($users as $user) {
                    DB::table('users')
                        ->where('id', $user->id)
                        ->update(['phone_hash' => $this->phoneHash($user->phone)]);
                }
            });
    }

    public function down()
    {
        Schema::dropIfExists('friend_suggestions');
        Schema::dropIfExists('user_vk_friends');
        Schema::dropIfExists('user_contact_hashes');
        Schema::dropIfExists('user_contact_imports');

        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'phone_hash')) {
                $table->dropColumn('phone_hash');
            }
        });
    }

    private function phoneHash(?string $phone): ?string
    {
        if (!$phone) {
            return null;
        }

        $digits = preg_replace('/\D+/', '', $phone);
        if (!$digits) {
            return null;
        }

        if (strlen($digits) === 10 && $digits[0] === '9') {
            $digits = '7' . $digits;
        }

        if (strlen($digits) === 11 && $digits[0] === '8') {
            $digits = '7' . substr($digits, 1);
        }

        if (strlen($digits) < 11 || strlen($digits) > 15 || $digits[0] === '0') {
            return null;
        }

        return hash('sha256', '+' . $digits);
    }
};
