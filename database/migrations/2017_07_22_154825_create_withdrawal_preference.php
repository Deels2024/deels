<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWithdrawalPreference extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('withdrawal_preferences', function (Blueprint $table): void {
            $table->increments('id');
            $table->integer('user_id')->nullable();

            $table->string('default_withdrawal_account')->nullable();
            $table->string('paypal_email')->nullable();

            // Bank
            $table->string('bank_account_holders_name')->nullable();
            $table->string('bank_account_number')->nullable();
            $table->string('swift_code')->nullable();
            $table->string('bank_name_full')->nullable();
            $table->string('bank_branch_name')->nullable();
            $table->string('bank_branch_city')->nullable();
            $table->string('bank_branch_address')->nullable();
            $table->string('country_id')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('withdrawal_preferences');
    }
}
