<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    public function up(): void
    {
        Schema::create('service_balance_statistics', function (Blueprint $table): void {
            $table->bigIncrements('id');
            $table->decimal('ucaller_balance', 12, 2)->default(0);
            $table->decimal('sms_balance', 12, 2)->default(0);
            $table->decimal('proxy_balance', 12, 2)->default(0);
            $table->json('proxies')->nullable();
            $table->json('errors')->nullable();
            $table->timestamp('checked_at')->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_balance_statistics');
    }
};
