<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('user_fcm_tokens') && Schema::hasColumn('user_fcm_tokens', 'user_id')) {
            Schema::table('user_fcm_tokens', function (Blueprint $table) {
                $table->unsignedBigInteger('user_id')->nullable()->change();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('user_fcm_tokens') && Schema::hasColumn('user_fcm_tokens', 'user_id')) {
            Schema::table('user_fcm_tokens', function (Blueprint $table) {
                $table->unsignedBigInteger('user_id')->nullable(false)->change();
            });
        }
    }
};
