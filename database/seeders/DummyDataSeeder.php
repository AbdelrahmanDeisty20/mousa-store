<?php

namespace Database\Seeders;

use App\Models\Attribute;
use App\Models\Banner;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Offer;
use App\Models\Product;
use App\Models\ProductBanner;
use App\Models\Property;
use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;

class DummyDataSeeder extends Seeder
{
    /**
     * Download an image safely and store it in target public/storage directories.
     */
    private function downloadAndSaveImage(string $url, string $folder, string $filename): string
    {
        $storagePaths = [
            storage_path("app/public/{$folder}"),
            public_path("storage/{$folder}"),
            public_path("storage/app/public/{$folder}"),
        ];

        if ($folder === 'categories') {
            $storagePaths[] = public_path("storage/categories");
        }

        foreach ($storagePaths as $path) {
            if (!File::exists($path)) {
                File::makeDirectory($path, 0777, true, true);
            }
        }

        $primaryFile = "{$storagePaths[0]}/{$filename}";
        if (File::exists($primaryFile) && filesize($primaryFile) > 0) {
            return $filename;
        }

        try {
            $response = Http::withoutVerifying()
                ->timeout(10)
                ->get($url);

            if ($response->successful()) {
                $content = $response->body();

                foreach ($storagePaths as $path) {
                    File::put("{$path}/{$filename}", $content);
                }

                $this->command->info("✓ Downloaded: {$folder}/{$filename}");
                return $filename;
            }
        } catch (\Throwable $e) {
            $this->command->warn("Failed downloading {$url}: " . $e->getMessage());
        }

        return $filename;
    }

    public function run(): void
    {
        $this->command->info("Starting fresh Mousa Store seeding (catalog, multi-variant prices & reviews)...");

        Schema::disableForeignKeyConstraints();
        Review::truncate();
        Offer::truncate();
        Attribute::truncate();
        Property::truncate();
        if (Schema::hasTable('category_product')) {
            DB::table('category_product')->truncate();
        }
        Product::truncate();
        Brand::truncate();
        Category::truncate();
        Banner::truncate();
        ProductBanner::truncate();
        Schema::enableForeignKeyConstraints();

        // 1. HERO BANNERS
        $this->command->info("\n--- Seeding Mousa Store Hero Banners ---");
        $heroBanners = [
            [
                'url' => 'https://images.unsplash.com/photo-1593642632559-0c6d3fc62b89?w=1200&auto=format&fit=crop',
                'title_ar' => 'موسى ستور — عالم اللابتوب والتقنية الحديثة',
                'title_en' => 'Mousa Store — Pro Laptops & Workstation Deals',
                'desc_ar' => 'تشكيلة جديدة من أجهزة الأداء العالي بأسعار تنافسية وضمان معتمد',
                'desc_en' => 'Fresh lineup of high-performance laptops with official warranty',
            ],
            [
                'url' => 'https://images.unsplash.com/photo-1519389950473-47ba0277781c?w=1200&auto=format&fit=crop',
                'title_ar' => 'تجهيزات المكتب الذكي والإكسسوارات الأصلية',
                'title_en' => 'Smart Desk Setup & Original Accessories',
                'desc_ar' => 'كيبوردات ميكانيكية وماوسات احترافية وشاشات بدقة عالية',
                'desc_en' => 'Mechanical keyboards, pro mice, and ultra-sharp monitors for work & play',
            ],
            [
                'url' => 'https://images.unsplash.com/photo-1526170375885-4d8ecf77b99f?w=1200&auto=format&fit=crop',
                'title_ar' => 'كاميرات التصوير الاحترافي والصوتيات النقية',
                'title_en' => 'Pro Cameras, Studio Audio & Creator Gear',
                'desc_ar' => 'كل ما يحتاجه صناع المحتوى والمصورون في مكان واحد بخصومات حصرية',
                'desc_en' => 'Everything content creators need with exclusive seasonal discounts',
            ],
        ];

        foreach ($heroBanners as $index => $b) {
            $filename = 'ms_banner_' . ($index + 1) . '.jpg';
            $this->downloadAndSaveImage($b['url'], 'banners', $filename);

            Banner::create([
                'image' => $filename,
                'title_ar' => $b['title_ar'],
                'title_en' => $b['title_en'],
                'short_desc_ar' => $b['desc_ar'],
                'short_desc_en' => $b['desc_en'],
            ]);
        }

        // 2. PRODUCT AD BANNERS
        $this->command->info("\n--- Seeding Product Ad Banners ---");
        $adBanners = [
            [
                'url' => 'https://images.unsplash.com/photo-1587829741301-dc798b83add3?w=800&auto=format&fit=crop',
                'title_ar' => 'كيبورد لوجيتك ميكانيكال احترافي MX',
                'title_en' => 'Logitech MX Mechanical Wireless Keyboard',
                'desc_ar' => 'استجابة فائقة مع إضاءة ذكية وبطارية تدوم طويلاً للبرمجة والعمل',
                'desc_en' => 'Tactile quiet switches, smart backlighting, and multi-device pairing',
                'price' => 5450.00,
            ],
            [
                'url' => 'https://images.unsplash.com/photo-1516035069371-29a1b244cc32?w=800&auto=format&fit=crop',
                'title_ar' => 'كاميرا كانون EOS R50 لتصوير المحتوى',
                'title_en' => 'Canon EOS R50 Mirrorless Vlogging Kit',
                'desc_ar' => 'تصوير 4K نقي مع تركيز تلقائي سريع وعدسة كيت 18-45mm',
                'desc_en' => 'Crisp 4K video capture, Dual Pixel AF, and compact RF-S 18-45mm lens',
                'price' => 33900.00,
            ],
            [
                'url' => 'https://images.unsplash.com/photo-1546868871-7041f2a55e12?w=800&auto=format&fit=crop',
                'title_ar' => 'ساعة شاومي واتش S3 الذكية بإطار متغير',
                'title_en' => 'Xiaomi Watch S3 AMOLED Smartwatch',
                'desc_ar' => 'شاشة AMOLED ساطعة، GPS مدمج، ومقاومة للماء حتى 5ATM',
                'desc_en' => 'Vibrant AMOLED display, built-in dual-band GPS, and 15-day battery life',
                'price' => 6250.00,
            ],
        ];

        foreach ($adBanners as $index => $ad) {
            $filename = 'ms_ad_' . ($index + 1) . '.jpg';
            $this->downloadAndSaveImage($ad['url'], 'advertisements', $filename);

            ProductBanner::create([
                'image' => $filename,
                'title_ar' => $ad['title_ar'],
                'title_en' => $ad['title_en'],
                'desc_ar' => $ad['desc_ar'],
                'desc_en' => $ad['desc_en'],
                'price' => $ad['price'],
            ]);
        }

        // 3. CATEGORIES (6 Main + 6 Subcategories)
        $this->command->info("\n--- Seeding Categories & Subcategories ---");
        $categoriesData = [
            [
                'key' => 'laptops',
                'name_ar' => 'لابتوب وكمبيوتر',
                'name_en' => 'Laptops & Computing',
                'url' => 'https://images.unsplash.com/photo-1517336714731-489689fd1ca8?w=600&auto=format&fit=crop',
                'filename' => 'ms_cat_laptops.jpg',
                'children' => [
                    ['name_ar' => 'أجهزة لابتوب للأعمال', 'name_en' => 'Business Laptops'],
                    ['name_ar' => 'لابتوب ألعاب وجيمنج', 'name_en' => 'Gaming Laptops'],
                ],
            ],
            [
                'key' => 'phones',
                'name_ar' => 'هواتف وتابلت',
                'name_en' => 'Smartphones & Tablets',
                'url' => 'https://images.unsplash.com/photo-1598327105666-5b89351aff97?w=600&auto=format&fit=crop',
                'filename' => 'ms_cat_phones.jpg',
                'children' => [
                    ['name_ar' => 'هواتف أندرويد الرائدة', 'name_en' => 'Flagship Android Phones'],
                    ['name_ar' => 'أجهزة تابلت للدراسة', 'name_en' => 'Study & Drawing Tablets'],
                ],
            ],
            [
                'key' => 'accessories',
                'name_ar' => 'مستلزمات المكتب والجيمنج',
                'name_en' => 'Desk & Gaming Gear',
                'url' => 'https://images.unsplash.com/photo-1587829741301-dc798b83add3?w=600&auto=format&fit=crop',
                'filename' => 'ms_cat_deskgear.jpg',
                'children' => [
                    ['name_ar' => 'كيبورد وماوس احترافي', 'name_en' => 'Keyboards & Mice'],
                ],
            ],
            [
                'key' => 'cameras',
                'name_ar' => 'كاميرات وصوتيات',
                'name_en' => 'Cameras & Studio Audio',
                'url' => 'https://images.unsplash.com/photo-1516035069371-29a1b244cc32?w=600&auto=format&fit=crop',
                'filename' => 'ms_cat_cameras.jpg',
                'children' => [
                    ['name_ar' => 'كاميرات وعدسات', 'name_en' => 'Cameras & Lenses'],
                ],
            ],
            [
                'key' => 'power',
                'name_ar' => 'شواحن وباور بنك',
                'name_en' => 'Chargers & Power Banks',
                'url' => 'https://images.unsplash.com/photo-1609091839311-d5365f9ff1c5?w=600&auto=format&fit=crop',
                'filename' => 'ms_cat_power.jpg',
                'children' => [],
            ],
            [
                'key' => 'wearables',
                'name_ar' => 'ساعات ذكية وحقائب',
                'name_en' => 'Wearables & Backpacks',
                'url' => 'https://images.unsplash.com/photo-1546868871-7041f2a55e12?w=600&auto=format&fit=crop',
                'filename' => 'ms_cat_wearables.jpg',
                'children' => [],
            ],
        ];

        $this->downloadAndSaveImage('https://images.unsplash.com/photo-1560343090-f0409e92791a?w=400&auto=format&fit=crop', 'categories', 'default.png');

        $createdCategories = [];
        $sortCounter = 1;
        foreach ($categoriesData as $c) {
            $this->downloadAndSaveImage($c['url'], 'categories', $c['filename']);

            $parent = Category::create([
                'name_ar' => $c['name_ar'],
                'name_en' => $c['name_en'],
                'image' => $c['filename'],
                'sort_order' => $sortCounter++,
                'parent_id' => null,
            ]);

            $createdCategories[$c['key']] = $parent;

            foreach ($c['children'] as $child) {
                Category::create([
                    'name_ar' => $child['name_ar'],
                    'name_en' => $child['name_en'],
                    'image' => $c['filename'],
                    'sort_order' => $sortCounter++,
                    'parent_id' => $parent->id,
                ]);
            }
        }

        // 4. BRANDS
        $this->command->info("\n--- Seeding Brands ---");
        $brandsData = [
            ['name_ar' => 'لينوفو', 'name_en' => 'Lenovo', 'url' => 'https://images.unsplash.com/photo-1593642632559-0c6d3fc62b89?w=400&auto=format&fit=crop', 'filename' => 'ms_brand_lenovo.jpg'],
            ['name_ar' => 'لوجيتك', 'name_en' => 'Logitech', 'url' => 'https://images.unsplash.com/photo-1527814050087-3793815479db?w=400&auto=format&fit=crop', 'filename' => 'ms_brand_logitech.jpg'],
            ['name_ar' => 'أنكر', 'name_en' => 'Anker', 'url' => 'https://images.unsplash.com/photo-1609091839311-d5365f9ff1c5?w=400&auto=format&fit=crop', 'filename' => 'ms_brand_anker.jpg'],
            ['name_ar' => 'شاومي', 'name_en' => 'Xiaomi', 'url' => 'https://images.unsplash.com/photo-1598327105666-5b89351aff97?w=400&auto=format&fit=crop', 'filename' => 'ms_brand_xiaomi.jpg'],
            ['name_ar' => 'أسوس', 'name_en' => 'Asus', 'url' => 'https://images.unsplash.com/photo-1603302576837-37561b2e2302?w=400&auto=format&fit=crop', 'filename' => 'ms_brand_asus.jpg'],
            ['name_ar' => 'كانون', 'name_en' => 'Canon', 'url' => 'https://images.unsplash.com/photo-1516035069371-29a1b244cc32?w=400&auto=format&fit=crop', 'filename' => 'ms_brand_canon.jpg'],
        ];

        $createdBrands = [];
        foreach ($brandsData as $b) {
            $this->downloadAndSaveImage($b['url'], 'brands', $b['filename']);

            $brand = Brand::create([
                'name_ar' => $b['name_ar'],
                'name_en' => $b['name_en'],
                'image' => $b['filename'],
            ]);

            $createdBrands[$b['name_en']] = $brand;
        }

        // 5. DOWNLOAD PRODUCT IMAGES POOL
        $this->command->info("\n--- Downloading Fresh Product Images ---");
        $imageUrls = [
            'laptop_pro' => 'https://images.unsplash.com/photo-1593642632559-0c6d3fc62b89?w=600&auto=format&fit=crop',
            'laptop_gaming' => 'https://images.unsplash.com/photo-1603302576837-37561b2e2302?w=600&auto=format&fit=crop',
            'monitor' => 'https://images.unsplash.com/photo-1527443224154-c4a3942d3acf?w=600&auto=format&fit=crop',
            'phone_flagship' => 'https://images.unsplash.com/photo-1598327105666-5b89351aff97?w=600&auto=format&fit=crop',
            'tablet' => 'https://images.unsplash.com/photo-1544244015-0df4b3ffc6b0?w=600&auto=format&fit=crop',
            'keyboard' => 'https://images.unsplash.com/photo-1587829741301-dc798b83add3?w=600&auto=format&fit=crop',
            'mouse' => 'https://images.unsplash.com/photo-1527814050087-3793815479db?w=600&auto=format&fit=crop',
            'headset' => 'https://images.unsplash.com/photo-1546435770-a3e426bf472b?w=600&auto=format&fit=crop',
            'earbuds' => 'https://images.unsplash.com/photo-1590658268037-6bf12165a8df?w=600&auto=format&fit=crop',
            'camera' => 'https://images.unsplash.com/photo-1516035069371-29a1b244cc32?w=600&auto=format&fit=crop',
            'microphone' => 'https://images.unsplash.com/photo-1590602847861-f357a9332bbc?w=600&auto=format&fit=crop',
            'powerbank' => 'https://images.unsplash.com/photo-1609091839311-d5365f9ff1c5?w=600&auto=format&fit=crop',
            'watch' => 'https://images.unsplash.com/photo-1546868871-7041f2a55e12?w=600&auto=format&fit=crop',
            'backpack' => 'https://images.unsplash.com/photo-1553062407-98eeb64c6a62?w=600&auto=format&fit=crop',
        ];

        $imageFiles = [];
        foreach ($imageUrls as $key => $url) {
            $fn = "ms_prod_{$key}.jpg";
            $this->downloadAndSaveImage($url, 'products', $fn);
            $imageFiles[$key] = $fn;
        }

        // 6. SEED NEW PRODUCTS WITH MULTIPLE VARIANTS, VARIED PRICES & OFFERS
        $this->command->info("\n--- Seeding 30 Fresh Store Products ---");

        $productsSpec = [
            [
                'name_ar' => 'لابتوب لينوفو ثينك باد X1 كاربون الجيل 12',
                'name_en' => 'Lenovo ThinkPad X1 Carbon Gen 12',
                'desc_ar' => 'لابتوب أعمال فائق الخفة بهيكل كاربون ومعالج Core Ultra وشاشة OLED مضادة للانعكاس',
                'desc_en' => 'Ultra-light carbon fiber business laptop powered by Intel Core Ultra and 2.8K OLED panel',
                'brand' => 'Lenovo',
                'cat' => 'laptops',
                'img' => $imageFiles['laptop_pro'],
                'variants' => [
                    [
                        'price' => 58900.00,
                        'stock' => 18,
                        'discount' => 10,
                        'attributes' => [
                            ['key_en' => 'Color', 'key_ar' => 'اللون', 'value_en' => 'Black', 'value_ar' => 'أسود'],
                            ['key_en' => 'Storage', 'key_ar' => 'السعة', 'value_en' => '512GB SSD / 16GB RAM', 'value_ar' => '512 جيجا / رام 16'],
                        ],
                    ],
                    [
                        'price' => 67500.00,
                        'stock' => 12,
                        'discount' => 8,
                        'attributes' => [
                            ['key_en' => 'Color', 'key_ar' => 'اللون', 'value_en' => 'Deep Black', 'value_ar' => 'أسود داكن'],
                            ['key_en' => 'Storage', 'key_ar' => 'السعة', 'value_en' => '1TB SSD / 32GB RAM', 'value_ar' => '1 تيرا / رام 32'],
                        ],
                    ],
                ],
            ],
            [
                'name_ar' => 'لابتوب أسوس ROG Strix G16 للألعاب',
                'name_en' => 'Asus ROG Strix G16 Gaming Laptop',
                'desc_ar' => 'أداء خارق للألعاب والتصميم بكارت RTX 4070 وشاشة 165Hz بتبريد ثلاثي المراوح',
                'desc_en' => 'High-refresh 165Hz gaming beast with NVIDIA RTX graphics and Tri-Fan thermal cooling',
                'brand' => 'Asus',
                'cat' => 'laptops',
                'img' => $imageFiles['laptop_gaming'],
                'variants' => [
                    [
                        'price' => 61200.00,
                        'stock' => 14,
                        'discount' => 12,
                        'attributes' => [
                            ['key_en' => 'Color', 'key_ar' => 'اللون', 'value_en' => 'Eclipse Gray', 'value_ar' => 'رمادي غامق'],
                            ['key_en' => 'Graphics', 'key_ar' => 'كارت الشاشة', 'value_en' => 'RTX 4060', 'value_ar' => 'RTX 4060'],
                        ],
                    ],
                    [
                        'price' => 72800.00,
                        'stock' => 9,
                        'discount' => null,
                        'attributes' => [
                            ['key_en' => 'Color', 'key_ar' => 'اللون', 'value_en' => 'Volt Green', 'value_ar' => 'أخضر نيون'],
                            ['key_en' => 'Graphics', 'key_ar' => 'كارت الشاشة', 'value_en' => 'RTX 4070', 'value_ar' => 'RTX 4070'],
                        ],
                    ],
                ],
            ],
            [
                'name_ar' => 'هاتف شاومي 14 ألترا بكاميرا لايكا',
                'name_en' => 'Xiaomi 14 Ultra Leica Pro Edition',
                'desc_ar' => 'كاميرا رباعية بعدسات لايكا الاحترافية وشاشة AMOLED 120Hz وشحن فائق السرعة 90 واط',
                'desc_en' => 'Quad Leica optical lens smartphone with WQHD+ AMOLED 120Hz display and 90W HyperCharge',
                'brand' => 'Xiaomi',
                'cat' => 'phones',
                'img' => $imageFiles['phone_flagship'],
                'variants' => [
                    [
                        'price' => 41500.00,
                        'stock' => 24,
                        'discount' => 15,
                        'attributes' => [
                            ['key_en' => 'Color', 'key_ar' => 'اللون', 'value_en' => 'Black', 'value_ar' => 'أسود'],
                            ['key_en' => 'Storage', 'key_ar' => 'السعة', 'value_en' => '256GB', 'value_ar' => '256 جيجا'],
                        ],
                    ],
                    [
                        'price' => 46900.00,
                        'stock' => 16,
                        'discount' => 10,
                        'attributes' => [
                            ['key_en' => 'Color', 'key_ar' => 'اللون', 'value_en' => 'White', 'value_ar' => 'أبيض'],
                            ['key_en' => 'Storage', 'key_ar' => 'السعة', 'value_en' => '512GB', 'value_ar' => '512 جيجا'],
                        ],
                    ],
                ],
            ],
            [
                'name_ar' => 'كيبورد لوجيتك MX Mechanical اللاسلكي',
                'name_en' => 'Logitech MX Mechanical Wireless Keyboard',
                'desc_ar' => 'لوحة مفاتيح ميكانيكية منخفضة الارتفاع للربط مع 3 أجهزة وإضاءة خلفية ذكية',
                'desc_en' => 'Low-profile tactile mechanical keyboard with Easy-Switch for up to 3 devices',
                'brand' => 'Logitech',
                'cat' => 'accessories',
                'img' => $imageFiles['keyboard'],
                'variants' => [
                    [
                        'price' => 5450.00,
                        'stock' => 35,
                        'discount' => 20,
                        'attributes' => [
                            ['key_en' => 'Color', 'key_ar' => 'اللون', 'value_en' => 'Graphite', 'value_ar' => 'جرافيت'],
                            ['key_en' => 'Size', 'key_ar' => 'المقاس', 'value_en' => 'Mini (Tenkeyless)', 'value_ar' => 'ميني مدمج'],
                        ],
                    ],
                    [
                        'price' => 6150.00,
                        'stock' => 22,
                        'discount' => 15,
                        'attributes' => [
                            ['key_en' => 'Color', 'key_ar' => 'اللون', 'value_en' => 'Graphite', 'value_ar' => 'جرافيت'],
                            ['key_en' => 'Size', 'key_ar' => 'المقاس', 'value_en' => 'Full Size', 'value_ar' => 'حجم كامل'],
                        ],
                    ],
                ],
            ],
            [
                'name_ar' => 'كاميرا كانون EOS R50 مع عدسة 18-45mm',
                'name_en' => 'Canon EOS R50 Mirrorless Camera Kit',
                'desc_ar' => 'كاميرا ميرورليس مثالية للتصوير الفوتوغرافي وصناعة الفيديوهات بدقة 4K بدون اقتصاص',
                'desc_en' => 'Lightweight mirrorless creator camera with 24.2MP APS-C sensor and uncropped 4K video',
                'brand' => 'Canon',
                'cat' => 'cameras',
                'img' => $imageFiles['camera'],
                'variants' => [
                    [
                        'price' => 33900.00,
                        'stock' => 15,
                        'discount' => 10,
                        'attributes' => [
                            ['key_en' => 'Color', 'key_ar' => 'اللون', 'value_en' => 'Black', 'value_ar' => 'أسود'],
                            ['key_en' => 'Edition', 'key_ar' => 'الإصدار', 'value_en' => 'Single Lens Kit', 'value_ar' => 'عدسة واحدة'],
                        ],
                    ],
                    [
                        'price' => 39500.00,
                        'stock' => 10,
                        'discount' => null,
                        'attributes' => [
                            ['key_en' => 'Color', 'key_ar' => 'اللون', 'value_en' => 'White', 'value_ar' => 'أبيض'],
                            ['key_en' => 'Edition', 'key_ar' => 'الإصدار', 'value_en' => 'Creator Mic Kit', 'value_ar' => 'طقم صناع المحتوى'],
                        ],
                    ],
                ],
            ],
            [
                'name_ar' => 'باور بنك أنكر برايم 20000mAh بقوة 200 واط',
                'name_en' => 'Anker Prime 20,000mAh 200W Power Bank',
                'desc_ar' => 'شاحن محمول فائق القوة يشحن جهازي لابتوب في وقت واحد مع شاشة رقمية ذكية',
                'desc_en' => 'High-capacity portable charger with 200W dual USB-C output and smart OLED status display',
                'brand' => 'Anker',
                'cat' => 'power',
                'img' => $imageFiles['powerbank'],
                'variants' => [
                    [
                        'price' => 4250.00,
                        'stock' => 45,
                        'discount' => 18,
                        'attributes' => [
                            ['key_en' => 'Color', 'key_ar' => 'اللون', 'value_en' => 'Black', 'value_ar' => 'أسود'],
                            ['key_en' => 'Capacity', 'key_ar' => 'السعة', 'value_en' => '20000mAh', 'value_ar' => '20000 مللي أمبير'],
                        ],
                    ],
                    [
                        'price' => 5600.00,
                        'stock' => 28,
                        'discount' => 12,
                        'attributes' => [
                            ['key_en' => 'Color', 'key_ar' => 'اللون', 'value_en' => 'Silver', 'value_ar' => 'فضي'],
                            ['key_en' => 'Capacity', 'key_ar' => 'السعة', 'value_en' => '27650mAh', 'value_ar' => '27650 مللي أمبير'],
                        ],
                    ],
                ],
            ],
            [
                'name_ar' => 'سماعة أنكر ساوندكور Q45 عازلة للضوضاء',
                'name_en' => 'Anker Soundcore Space Q45 ANC Headphones',
                'desc_ar' => 'عزل ضوضاء متكيف حتى 98% وصوت Hi-Res لاسلكي وبطارية تدوم 50 ساعة',
                'desc_en' => 'Adaptive ANC headphones with LDAC Hi-Res sound and up to 50 hours of playtime',
                'brand' => 'Anker',
                'cat' => 'cameras',
                'img' => $imageFiles['headset'],
                'variants' => [
                    [
                        'price' => 4890.00,
                        'stock' => 40,
                        'discount' => 25,
                        'attributes' => [
                            ['key_en' => 'Color', 'key_ar' => 'اللون', 'value_en' => 'Black', 'value_ar' => 'أسود'],
                        ],
                    ],
                    [
                        'price' => 4990.00,
                        'stock' => 25,
                        'discount' => 20,
                        'attributes' => [
                            ['key_en' => 'Color', 'key_ar' => 'اللون', 'value_en' => 'Blue', 'value_ar' => 'أزرق'],
                        ],
                    ],
                ],
            ],
            [
                'name_ar' => 'ساعة شاومي واتش S3 الذكية GPS',
                'name_en' => 'Xiaomi Watch S3 AMOLED Smartwatch',
                'desc_ar' => 'شاشة دائرية AMOLED 1.43 بوصة مع إطار قابل للتبديل وتتبع رياضي دقيق',
                'desc_en' => '1.43-inch AMOLED display with interchangeable bezel, HyperOS and 150+ workout modes',
                'brand' => 'Xiaomi',
                'cat' => 'wearables',
                'img' => $imageFiles['watch'],
                'variants' => [
                    [
                        'price' => 6250.00,
                        'stock' => 30,
                        'discount' => 15,
                        'attributes' => [
                            ['key_en' => 'Color', 'key_ar' => 'اللون', 'value_en' => 'Black', 'value_ar' => 'أسود'],
                        ],
                    ],
                    [
                        'price' => 6450.00,
                        'stock' => 19,
                        'discount' => null,
                        'attributes' => [
                            ['key_en' => 'Color', 'key_ar' => 'اللون', 'value_en' => 'Silver', 'value_ar' => 'فضي'],
                        ],
                    ],
                ],
            ],
        ];

        $singleVariantProducts = [
            ['لابتوب لينوفو يوجا سليم 7 برو', 'Lenovo Yoga Slim 7 Pro 14"', 'لابتوب نحيف بشاشة 2.8K 90Hz ومعالج قوي للمصممين والمبرمجين', 'Slim aluminum ultrabook with 90Hz 2.8K display and fast PCIe 4.0 SSD', 'Lenovo', 'laptops', $imageFiles['laptop_pro'], 44800.00, 10, ['Color' => ['ar' => 'رمادي', 'en' => 'Slate Gray']]],
            ['شاشة أسوس ProArt 27 بوصة 4K', 'Asus ProArt Display PA279CRV 27" 4K', 'شاشة مخصصة للمصممين بدقة ألوان 99% DCI-P3 ومنفذ USB-C 96W', 'Factory-calibrated 4K UHD USB-C creator monitor with 99% DCI-P3 coverage', 'Asus', 'laptops', $imageFiles['monitor'], 24900.00, null, ['Size' => ['ar' => '27 بوصة', 'en' => '27 Inch']]],
            ['شاشة ألعاب أسوس TUF 27 بوصة 180Hz', 'Asus TUF Gaming VG27AQ3A 180Hz Monitor', 'شاشة IPS سريعة الاستجابة 1ms ودعم G-Sync لأعلى سلاسة في الألعاب', 'Fast IPS QHD gaming monitor with 180Hz refresh rate and 1ms response time', 'Asus', 'laptops', $imageFiles['monitor'], 13750.00, 12, ['Size' => ['ar' => '27 بوصة', 'en' => '27 Inch']]],
            ['لابتوب لينوفو IdeaPad Slim 3 i5', 'Lenovo IdeaPad Slim 3 Core i5', 'لابتوب عملي للجامعة والعمل اليومي برام 16 جيجا وهارد 512 SSD', 'Everyday student & office laptop with Intel Core i5, 16GB RAM, and 512GB SSD', 'Lenovo', 'laptops', $imageFiles['laptop_pro'], 22400.00, 8, ['Storage' => ['ar' => '512 جيجا', 'en' => '512GB']]],
            ['هاتف شاومي Redmi Note 13 Pro Plus', 'Xiaomi Redmi Note 13 Pro+ 5G', 'شاشة منحنية 1.5K وكاميرا 200 ميجابكسل وشاحن 120 واط داخل العلبة', 'Curved 1.5K AMOLED phone with 200MP OIS camera and 120W HyperCharge', 'Xiaomi', 'phones', $imageFiles['phone_flagship'], 17650.00, 14, ['Storage' => ['ar' => '512 جيجا', 'en' => '512GB']]],
            ['تابلت شاومي Pad 6 شاشة 11 بوصة', 'Xiaomi Pad 6 11-Inch WQHD+ Tablet', 'تابلت بمعالج Snapdragon 870 وشاشة 144Hz مناسب للرسم والمحاضرات', 'Snapdragon 870 tablet with 144Hz aluminum body and quad Dolby speakers', 'Xiaomi', 'phones', $imageFiles['tablet'], 14900.00, null, ['Color' => ['ar' => 'أزرق سماوي', 'en' => 'Mist Blue']]],
            ['تابلت لينوفو Tab P12 مع القلم', 'Lenovo Tab P12 with Pen Plus 12.7"', 'شاشة واسعة 3K مع قلم مرفق للكتابة والمذاكرة ومشاهدة الأفلام', '12.7-inch 3K display tablet bundled with stylus pen and JBL quad speakers', 'Lenovo', 'phones', $imageFiles['tablet'], 16300.00, 10, ['Storage' => ['ar' => '256 جيجا', 'en' => '256GB']]],
            ['ماوس لوجيتك MX Master 3S اللاسلكي', 'Logitech MX Master 3S Performance Mouse', 'ماوس مريح بضغطات هادئة وحساس 8000 DPI يعمل على الزجاج', 'Ergonomic productivity mouse with MagSpeed wheel and 8000 DPI darkfield sensor', 'Logitech', 'accessories', $imageFiles['mouse'], 4150.00, 15, ['Color' => ['ar' => 'أسود', 'en' => 'Black']]],
            ['ماوس ألعاب لوجيتك G Pro X Superlight 2', 'Logitech G Pro X Superlight 2 Mouse', 'ماوس إي-سبورتس خفيف الوزن 60 جرام فقط بمستشعر HERO 2', 'Ultra-lightweight 60g esports wireless mouse with LIGHTFORCE hybrid switches', 'Logitech', 'accessories', $imageFiles['mouse'], 6400.00, null, ['Color' => ['ar' => 'أبيض', 'en' => 'White']]],
            ['كيبورد أسوس ROG Azoth Custom للألعاب', 'Asus ROG Azoth 75% Custom Keyboard', 'كيبورد ميكانيكي قابل للتخصيص مع شاشة OLED صغيرة وتوصيل ثلاثي الأوضاع', '75% custom wireless gaming keyboard with OLED display and hot-swap switches', 'Asus', 'accessories', $imageFiles['keyboard'], 9200.00, 10, ['Color' => ['ar' => 'أسود', 'en' => 'Black']]],
            ['سماعة ألعاب لوجيتك G733 RGB', 'Logitech G733 Lightspeed Wireless Headset', 'سماعة ألعاب خفيفة الوزن بإضاءة RGB وميكروفون Blue VO!CE نقي', 'Lightweight wireless gaming headset with suspension headband and DTS Headphone:X', 'Logitech', 'accessories', $imageFiles['headset'], 5750.00, 20, ['Color' => ['ar' => 'أسود', 'en' => 'Black']]],
            ['ميكروفون بودكاست لوجيتك Blue Yeti USB', 'Logitech Blue Yeti USB Studio Microphone', 'ميكروفون احترافي لتسجيل البودكاست والاجتماعات بـ 4 أنماط التقاط صوت', 'Studio-quality USB condenser mic with 4 pickup patterns and real-time monitoring', 'Logitech', 'cameras', $imageFiles['microphone'], 5900.00, null, ['Color' => ['ar' => 'رمادي', 'en' => 'Slate']]],
            ['كاميرا ويب لوجيتك Brio 4K Pro', 'Logitech Brio 4K Ultra HD Webcam', 'كاميرا ويب بدقة 4K HDR وتصحيح إضاءة تلقائي لاجتماعات العمل والبث', '4K Ultra HD webcam with RightLight 3 HDR and dual noise-canceling mics', 'Logitech', 'cameras', $imageFiles['camera'], 7400.00, 12, ['Resolution' => ['ar' => '4K HDR', 'en' => '4K HDR']]],
            ['سماعة إيربودز أنكر Liberty 4 NC', 'Anker Soundcore Liberty 4 NC Earbuds', 'عزل ضوضاء قوي وسماعات داخل الأذن بشحن لاسلكي وبطارية 50 ساعة', 'True wireless earbuds with adaptive ANC, wireless charging case and crisp mic', 'Anker', 'cameras', $imageFiles['earbuds'], 3250.00, 22, ['Color' => ['ar' => 'أزرق كحلي', 'en' => 'Navy Blue']]],
            ['سماعة شاومي Redmi Buds 5 Pro', 'Xiaomi Redmi Buds 5 Pro ANC Earbuds', 'إيربودز اقتصادية بصوت نقي وعزل ضوضاء نشط وتوصيل بجهازين معاً', 'Dual-driver Hi-Res wireless earbuds with 52dB ANC and low-latency gaming mode', 'Xiaomi', 'cameras', $imageFiles['earbuds'], 2350.00, 15, ['Color' => ['ar' => 'أبيض', 'en' => 'White']]],
            ['كاميرا كانون EOS M50 Mark II للفلوجز', 'Canon EOS M50 Mark II Content Creator', 'كاميرا خفيفة تدعم البث المباشر العمودي مع شاشة لمس قابلة للدوران', 'Compact creator camera with vertical 4K video support and vari-angle touchscreen', 'Canon', 'cameras', $imageFiles['camera'], 28400.00, 8, ['Color' => ['ar' => 'أسود', 'en' => 'Black']]],
            ['عدسة كانون RF 50mm f/1.8 بورتريه', 'Canon RF 50mm F1.8 STM Lens', 'عدسة عزل الخلفية الشهيرة لصور البورتريه الحادة في الإضاءة المنخفضة', 'Compact fast prime portrait lens with quiet STM autofocus and wide f/1.8 aperture', 'Canon', 'cameras', $imageFiles['camera'], 8600.00, null, ['Mount' => ['ar' => 'Canon RF', 'en' => 'Canon RF']]],
            ['شاحن أنكر 737 GaNPrime بقوة 120 واط', 'Anker 737 120W 3-Port GaN Charger', 'شاحن جداري صغير الحجم بـ 3 مخارج يشحن اللابتوب والهاتف بسرعة فائقة', 'Compact GaN fast wall charger with 2 USB-C + 1 USB-A ports and PowerIQ 4.0', 'Anker', 'power', $imageFiles['powerbank'], 2650.00, 18, ['Power' => ['ar' => '120 واط', 'en' => '120W']]],
            ['محطة شحن أنكر MagGo 3 في 1 قابلة للطي', 'Anker MagGo 3-in-1 Qi2 Charging Pad', 'قاعدة شحن مغناطيسية سريعة 15W للهاتف والساعة والسماعة بوقت واحد', 'Qi2 15W foldable magnetic wireless charging station for phone, watch, and buds', 'Anker', 'power', $imageFiles['powerbank'], 3850.00, null, ['Color' => ['ar' => 'أبيض', 'en' => 'White']]],
            ['كابل أنكر PowerLine III USB-C 100W', 'Anker PowerLine III USB-C 100W Cable 1.8m', 'كابل مضفّر فائق التحمل لشحن اللابتوب والهواتف بطول 1.8 متر', 'Braided high-durability USB-C to USB-C 100W fast charging cable (1.8m)', 'Anker', 'power', $imageFiles['powerbank'], 490.00, null, ['Length' => ['ar' => '1.8 متر', 'en' => '1.8m']]],
            ['حقيبة لابتوب لينوفو أوربان مقاومة للماء', 'Lenovo Urban B530 15.6" Backpack', 'شنطة ظهر عملية للابتوب حتى 15.6 بوصة بجيوب منظمة وخامة طاردة للماء', 'Water-repellent commuter backpack fitting 15.6" laptops with anti-theft pocket', 'Lenovo', 'wearables', $imageFiles['backpack'], 1450.00, 15, ['Color' => ['ar' => 'رمادي فحمي', 'en' => 'Charcoal Gray']]],
            ['شنطة أسوس ROG Ranger للألعاب 17 بوصة', 'Asus ROG Ranger BP2701 Gaming Backpack', 'حقيبة ظهر فاخرة للابتوبات الكبيرة مع حماية مبطنة للصدمات ومنفذ خارجي', 'Shock-absorbent large 17" gaming laptop backpack with weatherproof zippers', 'Asus', 'wearables', $imageFiles['backpack'], 3100.00, null, ['Size' => ['ar' => '17 بوصة', 'en' => '17 Inch']]],
            ['سوار شاومي Smart Band 9 الرياضي', 'Xiaomi Smart Band 9 Fitness Tracker', 'سوار ذكي خفيف بشاشة 60Hz وبطارية تدوم حتى 21 يوماً ومقاومة للماء', 'Sleek aluminum fitness tracker with 1200-nit screen and 21-day battery life', 'Xiaomi', 'wearables', $imageFiles['watch'], 1890.00, 20, ['Color' => ['ar' => 'أسود منتصف الليل', 'en' => 'Midnight Black']]],
            ['ماوس باد لوجيتك Desk Mat عريض للمكتب', 'Logitech Studio Series Large Desk Mat', 'ماوس باد قماش مضاد للانسكاب بحواف مخيطة لتحكم مثالي بالماوس', 'Spill-resistant anti-fray desk mat offering smooth glide and clean desk styling', 'Logitech', 'accessories', $imageFiles['keyboard'], 680.00, null, ['Color' => ['ar' => 'رمادي داكن', 'en' => 'Dark Gray']]],
        ];

        foreach ($singleVariantProducts as $p) {
            $attrs = [];
            foreach ($p[9] as $keyEn => $valPair) {
                $keyAr = match ($keyEn) {
                    'Color' => 'اللون',
                    'Size' => 'المقاس',
                    'Storage' => 'السعة',
                    'Resolution' => 'الدقة',
                    'Mount' => 'نوع العدسة',
                    'Power' => 'القدرة',
                    'Length' => 'الطول',
                    default => $keyEn,
                };
                $attrs[] = [
                    'key_en' => $keyEn,
                    'key_ar' => $keyAr,
                    'value_en' => $valPair['en'],
                    'value_ar' => $valPair['ar'],
                ];
            }

            $productsSpec[] = [
                'name_ar' => $p[0],
                'name_en' => $p[1],
                'desc_ar' => $p[2],
                'desc_en' => $p[3],
                'brand' => $p[4],
                'cat' => $p[5],
                'img' => $p[6],
                'variants' => [
                    [
                        'price' => $p[7],
                        'stock' => rand(15, 95),
                        'discount' => $p[8],
                        'attributes' => $attrs,
                    ],
                ],
            ];
        }

        $createdProducts = [];
        $totalVariants = 0;

        foreach ($productsSpec as $item) {
            $brandObj = $createdBrands[$item['brand']] ?? null;
            $catObj = $createdCategories[$item['cat']] ?? null;

            $productData = [
                'name_ar' => $item['name_ar'],
                'name_en' => $item['name_en'],
                'desc_ar' => $item['desc_ar'],
                'desc_en' => $item['desc_en'],
                'brand_id' => $brandObj?->id,
            ];

            if ($catObj && Schema::hasColumn('products', 'category_id')) {
                $productData['category_id'] = $catObj->id;
            }

            $product = Product::create($productData);
            $createdProducts[] = $product;

            if ($catObj && Schema::hasTable('category_product')) {
                DB::table('category_product')->insert([
                    'product_id' => $product->id,
                    'category_id' => $catObj->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            foreach ($item['variants'] as $variantSpec) {
                $property = Property::create([
                    'product_id' => $product->id,
                    'main_image' => $item['img'],
                    'images' => [$item['img']],
                    'stock' => $variantSpec['stock'],
                    'price' => $variantSpec['price'],
                ]);
                $totalVariants++;

                foreach ($variantSpec['attributes'] as $attr) {
                    Attribute::create([
                        'property_id' => $property->id,
                        'key_en' => $attr['key_en'],
                        'key_ar' => $attr['key_ar'],
                        'value_en' => $attr['value_en'],
                        'value_ar' => $attr['value_ar'],
                    ]);
                }

                if (!empty($variantSpec['discount'])) {
                    $offerData = [
                        'property_id' => $property->id,
                        'start' => now()->subDays(10),
                        'end' => now()->addYears(2),
                    ];

                    if (Schema::hasColumn('offers', 'discount_price')) {
                        $offerData['discount_price'] = $variantSpec['discount'];
                    }
                    if (Schema::hasColumn('offers', 'discount')) {
                        $offerData['discount'] = $variantSpec['discount'];
                    }

                    Offer::create($offerData);
                }
            }
        }

        // 7. SEED SAMPLE REVIEWER ACCOUNTS & PRODUCT REVIEWS
        $this->command->info("\n--- Seeding Customer Accounts & Product Reviews ---");
        $reviewersSpec = [
            ['first_name' => 'ياسمين', 'last_name' => 'محمود', 'email' => 'yasmin.customer@mousastore.test', 'phone' => '01011223344'],
            ['first_name' => 'كريم', 'last_name' => 'المنصور', 'email' => 'karim.customer@mousastore.test', 'phone' => '01122334455'],
            ['first_name' => 'سارة', 'last_name' => 'الديب', 'email' => 'sara.customer@mousastore.test', 'phone' => '01233445566'],
            ['first_name' => 'طارق', 'last_name' => 'نبيل', 'email' => 'tarek.customer@mousastore.test', 'phone' => '01544556677'],
        ];

        $reviewerUsers = [];
        foreach ($reviewersSpec as $rUser) {
            $reviewerUsers[] = User::updateOrCreate(
                ['email' => $rUser['email']],
                [
                    'first_name' => $rUser['first_name'],
                    'last_name' => $rUser['last_name'],
                    'phone' => $rUser['phone'],
                    'user_type' => 'user',
                    'is_verfived' => true,
                    'password' => 'Mousa@2026',
                ]
            );
        }

        $reviewSamples = [
            ['rate' => '5', 'comment' => 'منتج ممتاز جداً والتغليف وصل سليم في نفس اليوم، أنصح بالشراء من موسى ستور.'],
            ['rate' => '5', 'comment' => 'Great build quality and fast delivery! Totally worth the price.'],
            ['rate' => '4', 'comment' => 'الأداء ممتاز وسريع لكن كنت أتمنى خصم أكبر شوية، بشكل عام تجربة رائعة.'],
            ['rate' => '5', 'comment' => 'جودة أصلية وضمان حقيقي، فرق واضح مقارنة بالمنتجات التقليدية.'],
            ['rate' => '4', 'comment' => 'Very comfortable for daily work and battery backup matches the specs.'],
            ['rate' => '5', 'comment' => 'ممتاز وسعره منافس جداً في السوق، شكراً لفريق موسى ستور على المتابعة.'],
            ['rate' => '3', 'comment' => 'كويس ويؤدي الغرض بس التوصيل اتأخر يوم واحد عن الموعد.'],
            ['rate' => '5', 'comment' => 'Clean design, authentic packaging, and works out of the box seamlessly.'],
        ];

        $totalReviews = 0;
        foreach ($createdProducts as $prodIdx => $prod) {
            // Give multi-variant flagship products 3 reviews, others 1-2 reviews
            $reviewsForProduct = $prodIdx < 8 ? 3 : ($prodIdx % 2 === 0 ? 2 : 1);

            for ($r = 0; $r < $reviewsForProduct; $r++) {
                $userObj = $reviewerUsers[($prodIdx + $r) % count($reviewerUsers)];
                $sample = $reviewSamples[($prodIdx * 3 + $r) % count($reviewSamples)];

                Review::create([
                    'product_id' => $prod->id,
                    'user_id' => $userObj->id,
                    'comment' => $sample['comment'],
                    'rate' => $sample['rate'],
                ]);
                $totalReviews++;
            }
        }

        $this->command->info("\n========================================================");
        $this->command->info("🎉 MOUSA STORE SEEDING COMPLETED SUCCESSFULLY!");
        $this->command->info("Products: " . count($createdProducts) . " | Variants: {$totalVariants} | Reviews: {$totalReviews}");
        $this->command->info("========================================================\n");
    }
}
