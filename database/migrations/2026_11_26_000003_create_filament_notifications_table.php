<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Separate table for Filament dashboard bell/database notifications so the
     * customer-facing `notifications` table used by `/api/v1/notifications` stays untouched.
     */
    public function up(): void
    {
        if (! Schema::hasTable('filament_notifications')) {
            Schema::create('filament_notifications', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->string('type');
                $table->morphs('notifiable');
                $table->text('data');
                $table->timestamp('read_at')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('filament_notifications');
    }
};
