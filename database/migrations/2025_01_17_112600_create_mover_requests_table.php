<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('mover_requests', function (Blueprint $table) {
            $table->id();
            $table->string('car_type');
            $table->string('moved_item');
            $table->string('pickup_address');
            $table->string('pickup_lat');
            $table->string('pickup_long');
            $table->string('dropoff_address');
            $table->string('dropoff_lat');
            $table->string('dropoff_long');
            $table->string("price");
            $table->string("payment_method");
            $table->string("status")->default('pending');
            $table->dateTime("pickup_date");
            $table->dateTime("dropoff_date")->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('app_user_id')->nullable()->constrained();
            $table->foreignId('user_id')->nullable()->constrained();
            $table->text("admin_notes")->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mover_requests');
    }
};
