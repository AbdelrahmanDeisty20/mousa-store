<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('coupons')) {
            Schema::create('coupons', function (Blueprint $table) {
                $table->id();
                $table->string('code', 50)->unique();
                $table->string('discount_type', 20)->default('fixed');
                $table->decimal('discount_value', 10, 2);
                $table->decimal('min_order_amount', 10, 2)->nullable();
                $table->decimal('max_discount_amount', 10, 2)->nullable();
                $table->unsignedInteger('usage_limit')->nullable();
                $table->unsignedInteger('used_count')->default(0);
                $table->timestamp('starts_at')->nullable();
                $table->timestamp('expires_at')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('payment_methods')) {
            Schema::create('payment_methods', function (Blueprint $table) {
                $table->id();
                $table->string('code', 50)->unique();
                $table->string('name_ar', 100);
                $table->string('name_en', 100);
                $table->string('description_ar')->nullable();
                $table->string('description_en')->nullable();
                $table->boolean('is_active')->default(true);
                $table->integer('sort_order')->default(0);
                $table->json('config')->nullable();
                $table->timestamps();
            });
        }

        if (Schema::hasTable('payment_methods') && DB::table('payment_methods')->count() === 0) {
            $now = now();
            DB::table('payment_methods')->insert([
                [
                    'code' => 'cash',
                    'name_ar' => 'الدفع عند الاستلام',
                    'name_en' => 'Cash on Delivery',
                    'description_ar' => 'ادفع نقدًا عند استلام الطلب',
                    'description_en' => 'Pay with cash upon delivery',
                    'is_active' => true,
                    'sort_order' => 1,
                    'config' => null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                [
                    'code' => 'credit_card',
                    'name_ar' => 'البطاقة البنكية',
                    'name_en' => 'Credit / Debit Card',
                    'description_ar' => 'الدفع بالفيزا أو الماستركارد',
                    'description_en' => 'Pay securely via Visa or Mastercard',
                    'is_active' => true,
                    'sort_order' => 2,
                    'config' => null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                [
                    'code' => 'vodafone_cash',
                    'name_ar' => 'فودافون كاش',
                    'name_en' => 'Vodafone Cash',
                    'description_ar' => 'الدفع عبر محفظة فودافون كاش برقم مرجعي',
                    'description_en' => 'Pay using your Vodafone Cash wallet',
                    'is_active' => true,
                    'sort_order' => 3,
                    'config' => null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                [
                    'code' => 'fawry',
                    'name_ar' => 'فوري',
                    'name_en' => 'Fawry',
                    'description_ar' => 'ادفع عبر كود فوري في أي فرع',
                    'description_en' => 'Pay using a Fawry reference code',
                    'is_active' => true,
                    'sort_order' => 4,
                    'config' => null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                [
                    'code' => 'valu',
                    'name_ar' => 'فاليو',
                    'name_en' => 'valU',
                    'description_ar' => 'تقسيط عبر valU',
                    'description_en' => 'Installments via valU',
                    'is_active' => false,
                    'sort_order' => 5,
                    'config' => null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                [
                    'code' => 'sympl',
                    'name_ar' => 'سيمبل',
                    'name_en' => 'Sympl',
                    'description_ar' => 'ادفع لاحقًا بدون فوائد عبر Sympl',
                    'description_en' => 'Buy now, pay later via Sympl',
                    'is_active' => false,
                    'sort_order' => 6,
                    'config' => null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
            ]);
        }

        if (Schema::hasTable('settings') && ! Schema::hasColumn('settings', 'estimated_days')) {
            Schema::table('settings', function (Blueprint $table) {
                $table->string('estimated_days', 50)->nullable()->default('2-5')->after('shipping');
            });
        }

        if (Schema::hasTable('payments') && Schema::hasColumn('payments', 'method')) {
            Schema::table('payments', function (Blueprint $table) {
                $table->string('method', 50)->default('cash')->change();
            });
        }

        if (Schema::hasTable('orders')) {
            Schema::table('orders', function (Blueprint $table) {
                if (Schema::hasColumn('orders', 'user_id')) {
                    $table->unsignedBigInteger('user_id')->nullable()->change();
                }
                if (! Schema::hasColumn('orders', 'user_email')) {
                    $table->string('user_email')->nullable()->after('user_phone');
                }
                if (! Schema::hasColumn('orders', 'marketing_opt_in')) {
                    $table->boolean('marketing_opt_in')->default(false)->after('user_address');
                }
                if (! Schema::hasColumn('orders', 'coupon_id')) {
                    $table->unsignedBigInteger('coupon_id')->nullable()->after('subtotal');
                }
                if (! Schema::hasColumn('orders', 'coupon_code')) {
                    $table->string('coupon_code', 50)->nullable()->after('coupon_id');
                }
                if (! Schema::hasColumn('orders', 'discount_amount')) {
                    $table->decimal('discount_amount', 12, 2)->default(0)->after('coupon_code');
                }
                if (! Schema::hasColumn('orders', 'tax_amount')) {
                    $table->decimal('tax_amount', 12, 2)->default(0)->after('discount_amount');
                }
                if (! Schema::hasColumn('orders', 'shipping_amount')) {
                    $table->decimal('shipping_amount', 12, 2)->default(0)->after('tax_amount');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('orders')) {
            Schema::table('orders', function (Blueprint $table) {
                foreach (['user_email', 'marketing_opt_in', 'coupon_id', 'coupon_code', 'discount_amount', 'tax_amount', 'shipping_amount'] as $col) {
                    if (Schema::hasColumn('orders', $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }

        if (Schema::hasTable('settings') && Schema::hasColumn('settings', 'estimated_days')) {
            Schema::table('settings', function (Blueprint $table) {
                $table->dropColumn('estimated_days');
            });
        }

        Schema::dropIfExists('payment_methods');
        Schema::dropIfExists('coupons');
    }
};
