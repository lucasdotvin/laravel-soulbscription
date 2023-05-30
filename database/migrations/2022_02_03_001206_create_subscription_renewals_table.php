<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('subscription_renewals', function (Blueprint $table): void {
            $table->id();
            $table->boolean('overdue');
            $table->boolean('renewal');
            $table->foreignIdFor(\LucasDotVin\Soulbscription\Models\Subscription::class);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('subscription_renewals');
    }
};
