<?php

namespace Database\Seeders;

use App\Models\Contact;
use App\Models\Governorate;
use App\Models\Setting;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class GovernorateSeeder extends Seeder
{
    /**
     * Seed Egyptian governorates with per-governorate shipping rates and default store contact.
     */
    public function run(): void
    {
        $governorates = [
            ['name_ar' => 'القاهرة', 'name_en' => 'Cairo', 'shipping' => 50.00],
            ['name_ar' => 'الجيزة', 'name_en' => 'Giza', 'shipping' => 50.00],
            ['name_ar' => 'الإسكندرية', 'name_en' => 'Alexandria', 'shipping' => 65.00],
            ['name_ar' => 'القليوبية', 'name_en' => 'Qalyubia', 'shipping' => 55.00],
            ['name_ar' => 'الدقهلية', 'name_en' => 'Dakahlia', 'shipping' => 70.00],
            ['name_ar' => 'الشرقية', 'name_en' => 'Sharqia', 'shipping' => 65.00],
            ['name_ar' => 'الغربية', 'name_en' => 'Gharbia', 'shipping' => 70.00],
            ['name_ar' => 'المنوفية', 'name_en' => 'Monufia', 'shipping' => 65.00],
            ['name_ar' => 'البحيرة', 'name_en' => 'Beheira', 'shipping' => 70.00],
            ['name_ar' => 'كفر الشيخ', 'name_en' => 'Kafr El Sheikh', 'shipping' => 75.00],
            ['name_ar' => 'دمياط', 'name_en' => 'Damietta', 'shipping' => 75.00],
            ['name_ar' => 'بورسعيد', 'name_en' => 'Port Said', 'shipping' => 75.00],
            ['name_ar' => 'الإسماعيلية', 'name_en' => 'Ismailia', 'shipping' => 70.00],
            ['name_ar' => 'السويس', 'name_en' => 'Suez', 'shipping' => 70.00],
            ['name_ar' => 'الفيوم', 'name_en' => 'Fayoum', 'shipping' => 70.00],
            ['name_ar' => 'بني سويف', 'name_en' => 'Beni Suef', 'shipping' => 75.00],
            ['name_ar' => 'المنيا', 'name_en' => 'Minya', 'shipping' => 85.00],
            ['name_ar' => 'أسيوط', 'name_en' => 'Asyut', 'shipping' => 90.00],
            ['name_ar' => 'سوهاج', 'name_en' => 'Sohag', 'shipping' => 95.00],
            ['name_ar' => 'قنا', 'name_en' => 'Qena', 'shipping' => 100.00],
            ['name_ar' => 'الأقصر', 'name_en' => 'Luxor', 'shipping' => 105.00],
            ['name_ar' => 'أسوان', 'name_en' => 'Aswan', 'shipping' => 110.00],
            ['name_ar' => 'البحر الأحمر', 'name_en' => 'Red Sea', 'shipping' => 110.00],
            ['name_ar' => 'مطروح', 'name_en' => 'Matrouh', 'shipping' => 105.00],
            ['name_ar' => 'الوادي الجديد', 'name_en' => 'New Valley', 'shipping' => 120.00],
            ['name_ar' => 'شمال سيناء', 'name_en' => 'North Sinai', 'shipping' => 120.00],
            ['name_ar' => 'جنوب سيناء', 'name_en' => 'South Sinai', 'shipping' => 115.00],
        ];

        $hasTaxColumn = Schema::hasColumn('settings', 'tax');

        foreach ($governorates as $item) {
            $gov = Governorate::updateOrCreate(
                ['name_en' => $item['name_en']],
                ['name_ar' => $item['name_ar']]
            );

            $setting = Setting::firstOrNew(['governorate_id' => $gov->id]);
            $setting->shipping = $item['shipping'];
            if ($hasTaxColumn && $setting->tax === null) {
                $setting->tax = 0;
            }
            $setting->save();
        }

        if (Schema::hasTable('contacts') && Contact::count() === 0) {
            Contact::create([
                'email' => 'support@mousastore.com',
                'address_ar' => 'مدينة نصر، القاهرة، مصر',
                'address_en' => 'Nasr City, Cairo, Egypt',
                'phone' => '+201000000000',
                'whatsapp' => '+201000000000',
                'facebook' => 'https://facebook.com/mousastore',
            ]);
        }
    }
}
