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
        Schema::create('property_owners', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('email')->nullable()->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('role')->default('user');
            $table->string('phone_number')->nullable();
            $table->string('otp')->nullable();
            $table->dateTime('otp_send_time')->nullable();
            $table->boolean('is_admin')->default(false);
            $table->boolean('is_user_verified')->default(false);
            $table->string('lat')->nullable();
            $table->string('long')->nullable();
            $table->string('password')->nullable();
            $table->string("dob")->nullable();
            $table->string("provider")->nullable();
            $table->string("referal_code")->nullable();
            $table->string("is_new_user")->nullable();
            $table->string("current_points")->nullable();
 
 
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('property_owners');
    }
};