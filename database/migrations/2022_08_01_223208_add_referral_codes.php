<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class AddReferralCodes extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->string('referral_code');
            $table->string('invite_referral_code')->index()->nullable();
        });

        User::query()->each(function (User $user): void {
            $user->update(['referral_code' => Str::uuid()->toString()]);
        });

        Schema::table('users', function (Blueprint $table): void {
            $table->unique(['referral_code']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
    }
}
