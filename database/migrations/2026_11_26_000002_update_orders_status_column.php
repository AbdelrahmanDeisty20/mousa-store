<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ensure orders.status accepts all workflow statuses (pending, processing, paid, completed, cancelled)
     * across both MySQL and SQLite.
     */
    public function up(): void
    {
        if (Schema::hasTable('orders') && Schema::hasColumn('orders', 'status')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->string('status', 50)->default('pending')->change();
            });
        }
    }

    public function down(): void
    {
        // Keep string status column to preserve existing 'completed' order statuses
    }
};
