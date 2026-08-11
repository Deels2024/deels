<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('campaigns', function (Blueprint $table): void {
            $table->index(['status', 'created_at'], 'campaigns_status_created_at_idx');
            $table->index(['is_staff_picks', 'status', 'created_at'], 'campaigns_staff_status_created_at_idx');
            $table->index(['is_funded', 'status', 'created_at'], 'campaigns_funded_status_created_at_idx');
            $table->index(['category_id', 'status', 'created_at'], 'campaigns_category_status_created_at_idx');
            $table->index(['end_date', 'status'], 'campaigns_end_date_status_idx');
        });

        Schema::table('payments', function (Blueprint $table): void {
            $table->index(['campaign_id', 'status'], 'payments_campaign_status_idx');
            $table->index(['status', 'campaign_id'], 'payments_status_campaign_idx');
            $table->index(['campaign_id', 'created_at'], 'payments_campaign_created_at_idx');
        });
    }

    public function down(): void
    {
        Schema::table('campaigns', function (Blueprint $table): void {
            $table->dropIndex('campaigns_status_created_at_idx');
            $table->dropIndex('campaigns_staff_status_created_at_idx');
            $table->dropIndex('campaigns_funded_status_created_at_idx');
            $table->dropIndex('campaigns_category_status_created_at_idx');
            $table->dropIndex('campaigns_end_date_status_idx');
        });

        Schema::table('payments', function (Blueprint $table): void {
            $table->dropIndex('payments_campaign_status_idx');
            $table->dropIndex('payments_status_campaign_idx');
            $table->dropIndex('payments_campaign_created_at_idx');
        });
    }
};
