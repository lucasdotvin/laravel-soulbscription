<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('feature_tickets', function (Blueprint $table) {
            $table->id();
            $table->decimal('charges')->nullable();
            $table->timestamp('expired_at');
            $table->foreignIdFor(\LucasDotVin\Soulbscription\Models\Feature::class)->constrained()->cascadeOnDelete();
            $table->timestamps();

            if (config('soulbscription.models.subscriber.uses_uuid')) {
                $table->uuidMorphs('subscriber');
            } else {
                $table->numericMorphs('subscriber');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('feature_tickets');
    }
};
