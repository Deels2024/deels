<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeCampaignFields extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('campaigns', function (Blueprint $table): void {
            $table->float('goal')->change();
            $table->float('min_amount')->change();
            $table->float('max_amount')->change();
            $table->float('recommended_amount')->change();
            $table->float('campaign_owner_commission')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
    }
}
