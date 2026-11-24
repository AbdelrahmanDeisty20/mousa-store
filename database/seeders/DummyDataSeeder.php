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

        // Check if file already exists in primary storage path
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
        $this->command->info("Starting comprehensive store data seeding...");

        // Disable foreign key checks for clean truncation
        Schema::disableForeignKeyConstraints();
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

        // 1. SEED BANNERS
        $this->command->info("\n--- Seeding Hero Banners ---");
        $heroBanners = [
            [
                'url' => 'https://images.unsplash.com/photo-1607082348824-0a96f2a4b9da?w=1200&auto=format&fit=crop',
                'title_ar' => 'عروض الصيف الكبرى خصومات تصل لـ 50%',
                'title_en' => 'Grand Summer Sale Up to 50% Off',
                'desc_ar' => 'اكتشف أفضل المنتجات بأفضل الأسعار وأعلى جودة في مصر',
                'desc_en' => 'Discover top quality products at unbeatable prices in Egypt',
            ],
            [
                'url' => 'https://images.unsplash.com/photo-1483985988355-763728e1935b?w=1200&auto=format&fit=crop',
                'title_ar' => 'تشكيلة الأزياء الجديدة لعام 2026',
                'title_en' => 'New Fashion Collection 2026',
                'desc_ar' => 'أرقى صيحات الموضة من أشهر الماركات العالمية بين يديك',
                'desc_en' => 'Top fashion trends from world renowned brands in your hand',
            ],
            [
                'url' => 'https://images.unsplash.com/photo-1441986300917-64674bd600d8?w=1200&auto=format&fit=crop',
                'title_ar' => 'أحدث الأجهزة الإلكترونية والتكنولوجيا',
                'title_en' => 'Latest Tech & Smart Electronics',
                'desc_ar' => 'تسوق أحدث الهواتف والسماعات والأجهزة الذكية بأفضل الأسعار',
                'desc_en' => 'Shop latest smartphones, headphones and gadgets at best prices',
            ],
        ];

        foreach ($heroBanners as $index => $b) {
            $filename = "banner_" . ($index + 1) . ".jpg";
            $this->downloadAndSaveImage($b['url'], 'banners', $filename);

            Banner::create([
                'image' => $filename,
                'title_ar' => $b['title_ar'],
                'title_en' => $b['title_en'],
                'short_desc_ar' => $b['desc_ar'],
                'short_desc_en' => $b['desc_en'],
            ]);
        }

        // 2. SEED PRODUCT BANNERS (ADVERTISEMENTS) - 3 BANNERS
        $this->command->info("\n--- Seeding Product Ad Banners ---");
        $adBanners = [
            [
                'url' => 'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?w=800&auto=format&fit=crop',
                'title_ar' => 'سماعات صوتية فاخرة عازلة للضوضاء',
                'title_en' => 'Premium Noise Cancelling Headphones',
                'desc_ar' => 'تجربة استماع نقية وصوت مجسم عالي الدقة',
                'desc_en' => 'Pure listening experience with high fidelity audio',
                'price' => 3499.00,
            ],
            [
                'url' => 'https://images.unsplash.com/photo-1523275335684-37898b6baf30?w=800&auto=format&fit=crop',
                'title_ar' => 'ساعات ذكية وأناقة بلا حدود',
                'title_en' => 'Smartwatches & Elegance Unlimited',
                'desc_ar' => 'تابع لياقتك وصحتك بأناقة ورقي',
                'desc_en' => 'Track your fitness and health with style',
                'price' => 2899.00,
            ],
            [
                'url' => 'https://images.unsplash.com/photo-1511707171634-5f897ff02aa9?w=800&auto=format&fit=crop',
                'title_ar' => 'آيفون 15 برو ماكس جديد بالتيتانيوم',
                'title_en' => 'New iPhone 15 Pro Max Titanium',
                'desc_ar' => 'أقوى هاتف في العالم مع كاميرا احترافية وشريحة A17 Pro الفائقة',
                'desc_en' => 'Powerful flagship phone with pro camera & A17 Pro chip',
                'price' => 54999.00,
            ],
        ];

        foreach ($adBanners as $index => $ad) {
            $filename = "ad_" . ($index + 1) . ".jpg";
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

        // 3. SEED CATEGORIES
        $this->command->info("\n--- Seeding Categories ---");
        $categoriesData = [
            [
                'name_ar' => 'إلكترونيات',
                'name_en' => 'Electronics',
                'url' => 'https://images.unsplash.com/photo-1498049860654-af1a5c566876?w=600&auto=format&fit=crop',
                'filename' => 'cat_electronics_178522.jpg',
            ],
            [
                'name_ar' => 'ملابس وأزياء',
                'name_en' => 'Fashion & Clothes',
                'url' => 'https://images.unsplash.com/photo-1445205170230-053b83016050?w=600&auto=format&fit=crop',
                'filename' => 'cat_fashion_1785224750.jpg',
            ],
            [
                'name_ar' => 'منزل ومطبخ',
                'name_en' => 'Home & Kitchen',
                'url' => 'https://images.unsplash.com/photo-1556911220-e15b29be8c8f?w=600&auto=format&fit=crop',
                'filename' => 'cat_home-kitchen_17852.jpg',
            ],
            [
                'name_ar' => 'عطور وتجميل',
                'name_en' => 'Perfumes & Beauty',
                'url' => 'https://images.unsplash.com/photo-1592945403244-b3fbafd7f539?w=600&auto=format&fit=crop',
                'filename' => 'cat_perfumes_178522475.jpg',
            ],
            [
                'name_ar' => 'ساعات ومقتنيات',
                'name_en' => 'Watches & Accessories',
                'url' => 'https://images.unsplash.com/photo-1523275335684-37898b6baf30?w=600&auto=format&fit=crop',
                'filename' => 'cat_watches_1785224750.jpg',
            ],
            [
                'name_ar' => 'ألعاب وأطفال',
                'name_en' => 'Toys & Kids',
                'url' => 'https://images.unsplash.com/photo-1566576912321-d58ddd7a6088?w=600&auto=format&fit=crop',
                'filename' => 'cat_toys_1785224750.jpg',
            ],
        ];

        $this->downloadAndSaveImage('https://images.unsplash.com/photo-1560343090-f0409e92791a?w=400&auto=format&fit=crop', 'categories', 'default.png');

        $createdCategories = [];
        foreach ($categoriesData as $index => $c) {
            $this->downloadAndSaveImage($c['url'], 'categories', $c['filename']);

            $cat = Category::create([
                'name_ar' => $c['name_ar'],
                'name_en' => $c['name_en'],
                'image' => $c['filename'],
                'sort_order' => $index + 1,
                'parent_id' => null,
            ]);

            $createdCategories[] = $cat;
        }

        // 4. SEED BRANDS
        $this->command->info("\n--- Seeding Brands ---");
        $brandsData = [
            ['name_ar' => 'آبل', 'name_en' => 'Apple', 'url' => 'https://images.unsplash.com/photo-1611186871348-b1ce696e52c9?w=400&auto=format&fit=crop', 'filename' => 'brand_apple.jpg'],
            ['name_ar' => 'نايكي', 'name_en' => 'Nike', 'url' => 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?w=400&auto=format&fit=crop', 'filename' => 'brand_nike.jpg'],
            ['name_ar' => 'سامسونج', 'name_en' => 'Samsung', 'url' => 'https://images.unsplash.com/photo-1610945265064-0e34e5519bbf?w=400&auto=format&fit=crop', 'filename' => 'brand_samsung.jpg'],
            ['name_ar' => 'زارا', 'name_en' => 'Zara', 'url' => 'https://images.unsplash.com/photo-1489987707025-afc232f7ea0f?w=400&auto=format&fit=crop', 'filename' => 'brand_zara.jpg'],
            ['name_ar' => 'سوني', 'name_en' => 'Sony', 'url' => 'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?w=400&auto=format&fit=crop', 'filename' => 'brand_sony.jpg'],
            ['name_ar' => 'رولكس', 'name_en' => 'Rolex', 'url' => 'https://images.unsplash.com/photo-1522335789203-aabd1fc54bc9?w=400&auto=format&fit=crop', 'filename' => 'brand_rolex.jpg'],
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

        // 5. SEED PRODUCTS (50 STANDARD PRODUCTS)
        $this->command->info("\n--- Seeding 50 Store Products ---");

        // Curated Product Images Pool
        $imageUrls = [
            'iphone' => 'https://images.unsplash.com/photo-1511707171634-5f897ff02aa9?w=600&auto=format&fit=crop',
            'sony_headphone' => 'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?w=600&auto=format&fit=crop',
            'nike_shoes' => 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?w=600&auto=format&fit=crop',
            'samsung_phone' => 'https://images.unsplash.com/photo-1610945265064-0e34e5519bbf?w=600&auto=format&fit=crop',
            'perfume' => 'https://images.unsplash.com/photo-1592945403244-b3fbafd7f539?w=600&auto=format&fit=crop',
            'rolex_watch' => 'https://images.unsplash.com/photo-1522335789203-aabd1fc54bc9?w=600&auto=format&fit=crop',
            'macbook' => 'https://images.unsplash.com/photo-1496181133206-80ce9b88a853?w=600&auto=format&fit=crop',
            'smartwatch' => 'https://images.unsplash.com/photo-1523275335684-37898b6baf30?w=600&auto=format&fit=crop',
            'cookware' => 'https://images.unsplash.com/photo-1556911220-e15b29be8c8f?w=600&auto=format&fit=crop',
            'sunglasses' => 'https://images.unsplash.com/photo-1572635196237-14b3f281503f?w=600&auto=format&fit=crop',
            'backpack' => 'https://images.unsplash.com/photo-1548036328-c9fa89d128fa?w=600&auto=format&fit=crop',
            'toy_car' => 'https://images.unsplash.com/photo-1566576912321-d58ddd7a6088?w=600&auto=format&fit=crop',
            'gadget' => 'https://images.unsplash.com/photo-1583394838336-acd977736f90?w=600&auto=format&fit=crop',
            'fashion' => 'https://images.unsplash.com/photo-1489987707025-afc232f7ea0f?w=600&auto=format&fit=crop',
            'speaker' => 'https://images.unsplash.com/photo-1545454675-3531b543be5d?w=600&auto=format&fit=crop',
        ];

        // Download base images once
        $imageFiles = [];
        foreach ($imageUrls as $key => $url) {
            $fn = "prod_img_{$key}.jpg";
            $this->downloadAndSaveImage($url, 'products', $fn);
            $imageFiles[$key] = $fn;
        }

        // Featured 6 products
        $featuredProductsSpec = [
            [
                'name_ar' => 'آيفون 15 برو ماكس 256 جيجا تيتانيوم',
                'name_en' => 'iPhone 15 Pro Max 256GB Titanium',
                'desc_ar' => 'هاتف آبل الرائد ببدن من التيتانيوم وشريحة A17 Pro الفائقة ومجموعة كاميرات احترافية',
                'desc_en' => 'Apple flagship smartphone featuring titanium design, A17 Pro chip and professional camera system',
                'brand' => 'Apple',
                'cat' => 0, // Electronics
                'img' => $imageFiles['iphone'],
                'price' => 56000.00,
                'attributes' => [
                    ['key_en' => 'Color', 'key_ar' => 'اللون', 'value_en' => 'Natural Titanium', 'value_ar' => 'تيتانيوم طبيعي'],
                    ['key_en' => 'Storage', 'key_ar' => 'المساحة', 'value_en' => '256GB', 'value_ar' => '256 جيجابايت'],
                ],
            ],
            [
                'name_ar' => 'سماعات سوني اللاسلكية WH-1000XM5',
                'name_en' => 'Sony WH-1000XM5 Wireless Headphones',
                'desc_ar' => 'سماعات رأس لاسلكية احترافية بتقنية عزل الضوضاء المتطورة وبطارية تدوم حتى 30 ساعة',
                'desc_en' => 'Industry leading noise canceling headphones with crystal clear sound and 30hr battery life',
                'brand' => 'Sony',
                'cat' => 0, // Electronics
                'img' => $imageFiles['sony_headphone'],
                'price' => 16500.00,
                'attributes' => [
                    ['key_en' => 'Color', 'key_ar' => 'اللون', 'value_en' => 'Black', 'value_ar' => 'أسود'],
                    ['key_en' => 'Connectivity', 'key_ar' => 'التوصيل', 'value_en' => 'Bluetooth 5.2', 'value_ar' => 'بلوتوث 5.2'],
                ],
            ],
            [
                'name_ar' => 'حذاء نايكي اير ماكس الرياضي',
                'name_en' => 'Nike Air Max Running Sneakers',
                'desc_ar' => 'حذاء رياضي عصري مريح يوفر امتصاص تام للصدمات ودعم كامل للقدم أثناء الجري',
                'desc_en' => 'Modern extremely comfortable running shoes featuring responsive cushioning and foot support',
                'brand' => 'Nike',
                'cat' => 1, // Fashion
                'img' => $imageFiles['nike_shoes'],
                'price' => 3800.00,
                'attributes' => [
                    ['key_en' => 'Color', 'key_ar' => 'اللون', 'value_en' => 'Red/Black', 'value_ar' => 'أحمر/أسود'],
                    ['key_en' => 'Size', 'key_ar' => 'المقاس', 'value_en' => '42', 'value_ar' => '42'],
                ],
            ],
            [
                'name_ar' => 'سامسونج جالاكسي S24 اولترا 512 جيجا',
                'name_en' => 'Samsung Galaxy S24 Ultra 512GB',
                'desc_ar' => 'هاتف سامسونج الخارق مع تقنيات الذكاء الاصطناعي Galaxy AI وقلم S Pen وشاشة AMOLED',
                'desc_en' => 'Ultimate Samsung flagship smartphone powered by Galaxy AI, integrated S Pen and 200MP camera',
                'brand' => 'Samsung',
                'cat' => 0, // Electronics
                'img' => $imageFiles['samsung_phone'],
                'price' => 52000.00,
                'attributes' => [
                    ['key_en' => 'Color', 'key_ar' => 'اللون', 'value_en' => 'Titanium Gray', 'value_ar' => 'رمادي تيتانيوم'],
                    ['key_en' => 'Storage', 'key_ar' => 'المساحة', 'value_en' => '512GB', 'value_ar' => '512 جيجابايت'],
                ],
            ],
            [
                'name_ar' => 'عطر فرنسي فاخر او دي بارفان 100 مل',
                'name_en' => 'Luxury French Eau De Parfum 100ml',
                'desc_ar' => 'عطر ساحر بتركيز عالي ونفحات عطرية جذابة تدوم طوال اليوم وتمنحك حضوراً مميزاً',
                'desc_en' => 'Enchanting long lasting French fragrance featuring sophisticated notes for an unforgettable presence',
                'brand' => 'Zara',
                'cat' => 3, // Perfumes
                'img' => $imageFiles['perfume'],
                'price' => 1800.00,
                'attributes' => [
                    ['key_en' => 'Volume', 'key_ar' => 'الحجم', 'value_en' => '100ml', 'value_ar' => '100 مل'],
                ],
            ],
            [
                'name_ar' => 'ساعة رولكس سبمارينر الفاخرة',
                'name_en' => 'Rolex Submariner Deluxe Edition',
                'desc_ar' => 'ساعة رجالية كلاسيكية فاخرة مصنعة من الفولاذ المقاوم للصدأ ومقاومة للماء حتى عمق 300 متر',
                'desc_en' => 'Luxury iconic diving watch crafted from stainless steel with exceptional precision and water resistance',
                'brand' => 'Rolex',
                'cat' => 4, // Watches
                'img' => $imageFiles['rolex_watch'],
                'price' => 195000.00,
                'attributes' => [
                    ['key_en' => 'Material', 'key_ar' => 'الخامة', 'value_en' => 'Stainless Steel', 'value_ar' => 'ستيل مقاوم للصدأ'],
                ],
            ],
        ];

        $allProducts = [];

        foreach ($featuredProductsSpec as $s) {
            $allProducts[] = [
                'name_ar' => $s['name_ar'],
                'name_en' => $s['name_en'],
                'desc_ar' => $s['desc_ar'],
                'desc_en' => $s['desc_en'],
                'brand' => $s['brand'],
                'cat' => $s['cat'],
                'img' => $s['img'],
                'price' => $s['price'],
                'discount' => 10,
                'attributes' => $s['attributes'],
            ];
        }

        // 44 Standard Store Products
        $uniqueRetailProducts = [
            ['ماك بوك اير M3 شاشة 13.6 بوصة', 'Apple MacBook Air M3 13.6-Inch', 'لابتوبات ماك بوك اير خفيف الوزن بشريحة M3 وبطارية 18 ساعة', 'Ultra thin laptop featuring M3 chip, Liquid Retina display and 18hr battery', 'Apple', 0, $imageFiles['macbook'], 49000.00],
            ['ساعة ابل الذكية السلسلة 9', 'Apple Watch Series 9 GPS 45mm', 'ساعة آبل الذكية مع شريحة S9 وميزة الضغط المزدوج واستشعار الأكسجين', 'Advanced Apple smartwatch with S9 SiP, double tap gesture and health sensors', 'Apple', 4, $imageFiles['smartwatch'], 19800.00],
            ['حذاء جري نايكي بيجاسوس 40', 'Nike Pegasus 40 Running Shoes', 'حذاء جري متطور يدعم القدم ومناسب للتمرين والمسافات الطويلة', 'Responsive everyday running shoes with neutral support and breathable mesh', 'Nike', 1, $imageFiles['nike_shoes'], 4200.00],
            ['شاشة سامسونج OLED 65 بوصة 4K', 'Samsung 65-Inch 4K Smart OLED TV', 'شاشة سامسونج اوليد ألوان فائقة وأعلى تباين مع معدل 120 هرتز', 'Stunning 65-inch 4K OLED Smart TV with Neural Quantum Processor', 'Samsung', 0, $imageFiles['samsung_phone'], 34500.00],
            ['سماعات ابل ايربودز برو الجيل الثاني', 'Apple AirPods Pro 2nd Gen USB-C', 'سماعات أبل اللاسلكية مع ميزة إلغاء الضوضاء النشط وصوت محيطي', 'Active Noise Cancellation and Transparency mode wireless earbuds', 'Apple', 0, $imageFiles['sony_headphone'], 9800.00],
            ['جاكيت جلد طبيعي رجالي زارا', 'Zara Men Real Leather Biker Jacket', 'جاكيت جلد طبيعي بقصة عصرية وبطانة مريحة جداً', 'Classic black real leather biker jacket with asymmetric zipper', 'Zara', 1, $imageFiles['fashion'], 4500.00],
            ['ماكينة اسبريسو ديلونجي ديديكا', 'DeLonghi Dedica Espresso Coffee Machine', 'ماكينة تحضير الاسبريسو والكبوتشينو الضغط 15 بار', 'Compact pump espresso machine with manual cappuccino system', 'Sony', 2, $imageFiles['cookware'], 8900.00],
            ['عطر شانيل بلو دو شانيل 100 مل', 'Bleu De Chanel Eau De Parfum 100ml', 'عطر أروما خشبية جذابة وأناقة فريدة للرجال', 'Aromatic-woody fragrance with a captivating trail for modern men', 'Zara', 3, $imageFiles['perfume'], 5400.00],
            ['ساعة رولكس ديت جست تيتانيوم', 'Rolex Datejust 41mm Automatic Watch', 'ساعة فاخرة كلاسيكية بسوار جوبيلي ومينا أزرق ملكي', 'Iconic luxury watch with Jubilee bracelet and blue dial', 'Rolex', 4, $imageFiles['rolex_watch'], 210000.00],
            ['قلاية هوائية فيليبس XXL سمارت', 'Philips Air Fryer XXL Smart 1.4kg', 'قلاية بدون زيت مع تقنية إزالة الدهون وشاشة رقمية', 'Fat Removal Technology air fryer with preset cooking programs', 'Samsung', 2, $imageFiles['cookware'], 7200.00],
            ['حذاء رياضي كاجوال نايكي جوردان', 'Nike Air Jordan 1 Retro High', 'حذاء رياضي كلاسيكي بتصميم أسطوري ومريح', 'High-top legendary sneaker with premium leather upper', 'Nike', 1, $imageFiles['nike_shoes'], 6800.00],
            ['لعبة طائرة درون بالريموت والكاميرا', '4K Camera Quadcopter RC Drone Toy', 'طائرة درون ذكية بكاميرا 4K وتثبيت طيران التلقائي', 'Foldable RC drone with 4K camera, altitude hold and Wi-Fi FPV', 'Sony', 5, $imageFiles['toy_car'], 3200.00],
            ['نظارة شمسية كلاسيكية زارا UV400', 'Zara Classic Aviator UV400 Sunglasses', 'نظارات شمسية طيارين إطار ذهبي وعدسات جودة عالية', 'Classic aviator style sunglasses with metal frame and UV protection', 'Zara', 1, $imageFiles['sunglasses'], 1100.00],
            ['حقيبة ظهر للابتوب جلد طبيعي', 'Premium Genuine Leather Laptop Backpack', 'حقيبة ظهر تتسع للابتوب 15.6 بوصة ومزودة بجيوب تنظيمية', 'Handcrafted genuine leather backpack with padded laptop sleeve', 'Zara', 1, $imageFiles['backpack'], 2400.00],
            ['ساعة سامسونج جالاكسي واتش 6 كلاسيك', 'Samsung Galaxy Watch 6 Classic 47mm', 'ساعة ذكية مع إطار يدور ميكانيكي واستشعار ضغط الدم والقلب', 'Smartwatch featuring rotating bezel, ECG and advanced sleep coaching', 'Samsung', 4, $imageFiles['smartwatch'], 11500.00],
            ['ماكينة حلاقة فيليبس سيريس 9000', 'Philips Shaver Series 9000 Wet & Dry', 'ماكينة حلاقة للوجه بتقنية الشفرات المزدوجة وحلاقة رطبة وجافة', 'Rotary electric shaver with SkinIQ technology for personal comfort', 'Sony', 0, $imageFiles['gadget'], 6300.00],
            ['طقم أواني طهي سيراميك 10 قطع', 'Non-Stick Ceramic Cookware Set 10-Pcs', 'طقم حلل طهي صحية خالية من PFOA وسهلة التنظيف', '10-Piece non-toxic ceramic coated pots and pans cookware set', 'Zara', 2, $imageFiles['cookware'], 5100.00],
            ['موزع عطر وفواحة ذكية بالزيوت', 'Smart Essential Oil Aroma Diffuser', 'فواحة ذكية بإضاءة LED ملونة وتقنية التراسونيك الهادئة', 'Ultrasonic quiet aroma therapy humidifier with color ambient lights', 'Zara', 3, $imageFiles['perfume'], 1250.00],
            ['تيشيرت نايكي دراي فت رياضي', 'Nike Dri-FIT Men Athletic T-Shirt', 'تيشيرت خفيف يمتص العرق ومناسب لتمارين الجيم', 'Moisture-wicking athletic short sleeve training t-shirt', 'Nike', 1, $imageFiles['fashion'], 1350.00],
            ['لعبة روبوت تفاعلي ذكي للأطفال', 'Interactive AI Smart Robot Toy for Kids', 'روبوت ذكي يتكلم ويلعب ويتحكم به بالحركة والصوت', 'Programmable voice-controlled dancing and talking toy robot', 'Sony', 5, $imageFiles['toy_car'], 2100.00],
            ['مكبر صوت سوني لاسلكي بأس قوي', 'Sony SRS-XG300 Portable Bluetooth Speaker', 'سماعة محمولة مع إضاءة حركية وصوت Bass متفجر', 'High-power portable wireless speaker with retractable handle', 'Sony', 0, $imageFiles['speaker'], 8400.00],
            ['آيباد اير الجيل الخامس 64 جيجا', 'Apple iPad Air 5th Gen 64GB Wi-Fi', 'آيباد اير بشريحة M1 وشاشة ليكويد رتينا 10.9 بوصة', 'Powerful iPad featuring M1 chip, Ultra Wide front camera with Center Stage', 'Apple', 0, $imageFiles['macbook'], 24000.00],
            ['فستان سهرة أنيق زارا كوليكشن', 'Zara Evening Elegance Satin Dress', 'فستان سهرة حريمي من الساتان الناعم بتصميم رائع', 'Fluid satin midi dress with cowl neckline and elegant fit', 'Zara', 1, $imageFiles['fashion'], 3600.00],
            ['طقم سكاكين مطبخ يابانية 6 قطع', 'Japanese Stainless Steel Kitchen Knife Set', 'سكاكين مطبخ حادة مصنعة من الستانلس الياباني الصلب', '6-Piece high carbon stainless steel razor sharp kitchen knife block set', 'Sony', 2, $imageFiles['cookware'], 2800.00],
            ['عطر ديور سوڤاج او دي بارفان 100مل', 'Dior Sauvage Eau De Parfum 100ml', 'عطر رجالي أيقوني بنفحات العود والبرغموت الساحرة', 'Powerful raw and noble woody aromatic fragrance for men', 'Zara', 3, $imageFiles['perfume'], 5100.00],
            ['ساعة رجالية كلاسيكية سيراميك', 'Men Executive Ceramic Quartz Watch', 'ساعة يد أنيقة بتقويم وميناء ضد الخدش', 'Luxury quartz watch with scratch-resistant sapphire crystal glass', 'Rolex', 4, $imageFiles['rolex_watch'], 4200.00],
            ['شاحن انكر لاسلكي سريع 3 في 1', 'Anker 3-in-1 Fast Wireless Charging Station', 'قاعدة شحن شاحن سريع للهاتف والساعة والسماعة معاً', '3-in-1 Foldable wireless charging stand for phone, watch and earbuds', 'Apple', 0, $imageFiles['gadget'], 2900.00],
            ['خلاط مولينكس سوبر دوبر 1.75 لتر', 'Moulinex Super Blender with Grinder', 'خلاط مطبخ قوي 500 واط مع مطحنتين للبهارات والمكسرات', '500W durable counter blender with two grinder accessories', 'Sony', 2, $imageFiles['cookware'], 2450.00],
            ['بنطلون شينو كاجوال زارا رجالي', 'Zara Men Casual Chino Trousers', 'بنطلون قطن سليم فيت مريح ومناسب للعمل والمناسبات', 'Comfort stretch cotton slim fit casual chino trousers', 'Zara', 1, $imageFiles['fashion'], 1850.00],
            ['حذاء نسائي أنيق كعب عالي زارا', 'Zara Women Elegant High Heel Pumps', 'حذاء كعب عالي بتصميم أنيق ومريح في الارتداء', 'Classic pointed toe stiletto high heel pumps for formal occasions', 'Zara', 1, $imageFiles['nike_shoes'], 2950.00],
            ['لعبة سيارة كهربائية للأطفال تركب', 'Kids Ride-On Electric Sports Car 12V', 'سيارة كهربائية للأطفال بالريموت ومحركين وسماعة موسيقى', '12V Battery powered ride-on car with remote control and MP3 player', 'Sony', 5, $imageFiles['toy_car'], 7800.00],
            ['كاميرات مراقبة يوفي بدون أسلاك', 'Eufy Cam 2C Wireless Security System', 'كاميرتين مراقبة HD بطارية 180 يوم بدون اشتراك ماهري', 'Wire-free HD security camera set with 180-day battery life', 'Sony', 0, $imageFiles['gadget'], 9200.00],
            ['طقم ملايات سرير مطرزة 5 قطع', 'Embroidered Luxury Bedding Set 5-Pcs', 'طقم سرير قطن مطرز ناعم ووان دافئة', '5-Piece embroidered satin cotton luxury double bed sheet set', 'Zara', 2, $imageFiles['cookware'], 2300.00],
            ['مفرمة لحم وخضار كهربائية 500 واط', 'Electric Food Chopper & Meat Grinder', 'مفرمة مطبخ وعاء استانلس 2 لتر 4 شفرات حادة', '500W Stainless steel bowl food processor and meat grinder', 'Samsung', 2, $imageFiles['cookware'], 1650.00],
            ['سماعة جي بي ال فليب 6 ضد الماء', 'JBL Flip 6 Portable Waterproof Speaker', 'سماعة بلوتوث محمولة بصوت دقيق ضد الماء والأتربة', 'Powerful 2-way speaker system IP67 waterproof Bluetooth speaker', 'Sony', 0, $imageFiles['speaker'], 4800.00],
            ['مكواه بخار عمودية للملابس 1800 واط', 'Garment Steamer Vertical Fabric Steamer', 'مكواة بخارية سريعة التسخين لإزالة التجاعيد بسهولة', 'Full-size garment steamer with removable water tank and hanger', 'Samsung', 2, $imageFiles['cookware'], 3100.00],
            ['حقيبة سفر كابينة 20 بوصة فايبر', 'Fiber Spinner Carry-On Luggage 20"', 'حقيبة سفر خفيفة وقوية بقفل أمان رقمي عجلات 360 درجة', '20-Inch hard shell spinner carry-on luggage with TSA lock', 'Zara', 1, $imageFiles['backpack'], 2600.00],
            ['مستحضر سيروم هيلارونيك أسيد للبشرة', 'Hyaluronic Acid Face Hydrating Serum', 'سيروم ترطيب مكثف ونضارة الفيتامينات للبشرة', 'Deeply hydrating face serum with pure hyaluronic acid and B5', 'Zara', 3, $imageFiles['perfume'], 850.00],
            ['طقم بيجامة قطن ناعم زارا نسائي', 'Zara Women Soft Cotton Pyjama Set', 'طقم بيجامة نوم مريحة وناعمة جداً قطن 100%', '2-Piece comfortable pure cotton loungewear pyjama sleep set', 'Zara', 1, $imageFiles['fashion'], 1750.00],
            ['محفظة جلد طبيعي رجالي حماية RFID', 'Genuine Leather Men Wallet RFID Protection', 'محفظة رجالية جلد طبيعي بجيوب متعددة وحماية البطاقات', 'Bifold genuine leather wallet with RFID blocking security', 'Zara', 1, $imageFiles['backpack'], 750.00],
            ['لعبة منزل الأحلام للأطفال مع اثاث', 'Dream Dollhouse Play Set with Furniture', 'منزل العاب أطفال ملون بقطع أثاث وشخصيات تفاعلية', '3-Story miniature dream house play set with accessories', 'Sony', 5, $imageFiles['toy_car'], 1950.00],
            ['باور بنك مغناطيسي انكر ماج سيف', 'Anker MagGo Magnetic Wireless Power Bank', 'شاحن مغناطيسي سلكي ولاسلكي لهواتف الآيفون', '5000mAh Magnetic snap-on wireless portable power bank', 'Apple', 0, $imageFiles['gadget'], 2200.00],
            ['ميزان مطبخ ديجيتال دقيق 5 كجم', 'Digital Precision Kitchen Scale 5kg', 'ميزان ديجيتال دقيق للمطبخ وإعداد الوجبات', 'High precision 1g/5kg electronic digital kitchen food scale', 'Samsung', 2, $imageFiles['cookware'], 550.00],
            ['سوار رياضي أنيق من الفولاذ للرجال', 'Men Stainless Steel Cuban Link Bracelet', 'سوار يد رجالي ستانلس ستيل غير قابل لتغير اللون', 'Heavy duty stainless steel curb Cuban link chain bracelet for men', 'Rolex', 4, $imageFiles['rolex_watch'], 950.00],
        ];

        foreach ($uniqueRetailProducts as $r) {
            $allProducts[] = [
                'name_ar' => $r[0],
                'name_en' => $r[1],
                'desc_ar' => $r[2],
                'desc_en' => $r[3],
                'brand' => $r[4],
                'cat' => $r[5],
                'img' => $r[6],
                'price' => $r[7],
                'discount' => rand(0, 1) ? rand(5, 25) : null,
                'attributes' => [
                    ['key_en' => 'Condition', 'key_ar' => 'الحالة', 'value_en' => 'New Original', 'value_ar' => 'جديد أصلي'],
                ],
            ];
        }

        // Insert all products
        $totalCount = 0;

        foreach ($allProducts as $idx => $item) {
            $brandObj = $createdBrands[$item['brand']] ?? null;
            $catObj = $createdCategories[$item['cat']] ?? null;

            $productData = [
                'name_ar' => $item['name_ar'],
                'name_en' => $item['name_en'],
                'desc_ar' => $item['desc_ar'],
                'desc_en' => $item['desc_en'],
                'brand_id' => $brandObj ? $brandObj->id : null,
            ];

            if ($catObj && Schema::hasColumn('products', 'category_id')) {
                $productData['category_id'] = $catObj->id;
            }

            $product = Product::create($productData);

            if ($catObj && Schema::hasTable('category_product')) {
                DB::table('category_product')->insert([
                    'product_id' => $product->id,
                    'category_id' => $catObj->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // Create Property (Variant)
            $propData = [
                'product_id' => $product->id,
                'main_image' => $item['img'],
                'images' => [$item['img']],
                'stock' => rand(20, 150),
                'price' => $item['price'],
            ];

            $property = Property::create($propData);

            // Create Attributes
            foreach ($item['attributes'] as $attr) {
                Attribute::create([
                    'property_id' => $property->id,
                    'key_en' => $attr['key_en'],
                    'key_ar' => $attr['key_ar'],
                    'value_en' => $attr['value_en'],
                    'value_ar' => $attr['value_ar'],
                ]);
            }

            // Create Offer if discount exists
            if (!empty($item['discount'])) {
                $offerData = [
                    'property_id' => $property->id,
                    'start' => now()->subMonths(6),
                    'end' => now()->addYears(5),
                ];

                if (Schema::hasColumn('offers', 'discount_price')) {
                    $offerData['discount_price'] = $item['discount'];
                }
                if (Schema::hasColumn('offers', 'discount')) {
                    $offerData['discount'] = $item['discount'];
                }

                Offer::create($offerData);
            }

            $totalCount++;
        }

        $this->command->info("\n========================================================");
        $this->command->info("🎉 SEEDING COMPLETED SUCCESSFULLY!");
        $this->command->info("Total Products Created: {$totalCount}");
        $this->command->info("========================================================\n");
    }
}
