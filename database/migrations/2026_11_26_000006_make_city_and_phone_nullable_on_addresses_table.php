<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('addresses')) {
            Schema::table('addresses', function (Blueprint $table) {
                if (Schema::hasColumn('addresses', 'city')) {
                    $table->string('city')->nullable()->change();
                }
                if (Schema::hasColumn('addresses', 'phone')) {
                    $table->string('phone')->nullable()->change();
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('addresses')) {
            Schema::table('addresses', function (Blueprint $table) {
                if (Schema::hasColumn('addresses', 'city')) {
                    $table->string('city')->nullable(false)->change();
                }
                if (Schema::hasColumn('addresses', 'phone')) {
                    $table->string('phone')->nullable(false)->change();
                }
            });
        }
    }
};
