<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('governorates')) {
            Schema::create('governorates', function (Blueprint $table) {
                $table->id();
                $table->string('name_ar');
                $table->string('name_en');
                $table->timestamps();
            });
        }

        if (Schema::hasTable('categories') && ! Schema::hasColumn('categories', 'sort_order')) {
            Schema::table('categories', function (Blueprint $table) {
                $table->integer('sort_order')->default(0)->after('image');
            });
        }

        if (Schema::hasTable('orders')) {
            Schema::table('orders', function (Blueprint $table) {
                if (Schema::hasColumn('orders', 'address_id')) {
                    $table->unsignedBigInteger('address_id')->nullable()->change();
                }
                if (! Schema::hasColumn('orders', 'user_name')) {
                    $table->string('user_name')->nullable()->after('user_id');
                }
                if (! Schema::hasColumn('orders', 'user_phone')) {
                    $table->string('user_phone')->nullable()->after('user_name');
                }
                if (! Schema::hasColumn('orders', 'user_address')) {
                    $table->text('user_address')->nullable()->after('user_phone');
                }
                if (! Schema::hasColumn('orders', 'subtotal')) {
                    $table->decimal('subtotal', 12, 2)->default(0)->after('user_address');
                }
                if (! Schema::hasColumn('orders', 'governorate_id')) {
                    $table->unsignedBigInteger('governorate_id')->nullable()->after('subtotal');
                }
            });
        }

        if (Schema::hasTable('settings')) {
            Schema::table('settings', function (Blueprint $table) {
                if (Schema::hasColumn('settings', 'tax')) {
                    $table->decimal('tax', 8, 2)->nullable()->default(0)->change();
                }
                if (! Schema::hasColumn('settings', 'governorate_id')) {
                    $table->unsignedBigInteger('governorate_id')->nullable()->after('tax');
                }
            });
        }

        if (Schema::hasTable('otps') && ! Schema::hasColumn('otps', 'reset_token')) {
            Schema::table('otps', function (Blueprint $table) {
                $table->string('reset_token')->nullable()->after('otp');
            });
        }

        if (Schema::hasTable('users') && ! Schema::hasColumn('users', 'otp')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('otp')->nullable()->after('phone');
            });
        }

        if (Schema::hasTable('notifications') && Schema::hasColumn('notifications', 'user_id')) {
            Schema::table('notifications', function (Blueprint $table) {
                $table->unsignedBigInteger('user_id')->nullable()->change();
            });
        }

        if (Schema::hasTable('properties')) {
            Schema::table('properties', function (Blueprint $table) {
                if (! Schema::hasColumn('properties', 'price')) {
                    $table->decimal('price', 10, 2)->nullable()->after('images');
                }
                if (! Schema::hasColumn('properties', 'deleted_at')) {
                    $table->softDeletes();
                }
            });
        }

        if (Schema::hasTable('offers') && ! Schema::hasColumn('offers', 'discount_price')) {
            Schema::table('offers', function (Blueprint $table) {
                $table->decimal('discount_price', 8, 2)->nullable()->after('property_id');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('categories') && Schema::hasColumn('categories', 'sort_order')) {
            Schema::table('categories', function (Blueprint $table) {
                $table->dropColumn('sort_order');
            });
        }

        Schema::dropIfExists('governorates');
    }
};
