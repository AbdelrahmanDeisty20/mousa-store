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
        $this->command->info("Starting fresh Mousa Store Sportswear seeding...");

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

        // 1. SPORTSWEAR HERO BANNERS
        $this->command->info("\n--- Seeding Sportswear Hero Banners ---");
        $heroBanners = [
            [
                'url' => 'https://images.unsplash.com/photo-1517836357463-d25dfeac3438?w=1200&auto=format&fit=crop',
                'title_ar' => 'موسى ستور — أقوى تشكيلة ملابس رياضية 2026',
                'title_en' => 'Mousa Store — New 2026 Athletic Apparel',
                'desc_ar' => 'ملابس جيم وجري بخامات دراي-فت عالية التهوية وأسعار مميزة',
                'desc_en' => 'Breathable Dri-FIT gym and running wear built for peak performance',
            ],
            [
                'url' => 'https://images.unsplash.com/photo-1534438327276-14e5300c3a48?w=1200&auto=format&fit=crop',
                'title_ar' => 'تجهيزات التمرين والجيم الاحترافية',
                'title_en' => 'Pro Training & Gym Gear Collection',
                'desc_ar' => 'تيشيرتات سليم فيت، شورتات، وتريننجات كاملة لكل التمارين',
                'desc_en' => 'Compression tops, lightweight shorts, and full tracksuits for every workout',
            ],
            [
                'url' => 'https://images.unsplash.com/photo-1556906781-9a412961c28c?w=1200&auto=format&fit=crop',
                'title_ar' => 'أحذية الجري وأطقم الكرة بخصومات حتى 30%',
                'title_en' => 'Running Sneakers & Football Kits Up to 30% Off',
                'desc_ar' => 'اكتشف أحدث إصدارات الأحذية الرياضية الأصلية وأطقم المباريات',
                'desc_en' => 'Explore lightweight running sneakers and breathable match-day jerseys',
            ],
        ];

        foreach ($heroBanners as $index => $b) {
            $filename = 'sport_banner_' . ($index + 1) . '.jpg';
            $this->downloadAndSaveImage($b['url'], 'banners', $filename);

            Banner::create([
                'image' => $filename,
                'title_ar' => $b['title_ar'],
                'title_en' => $b['title_en'],
                'short_desc_ar' => $b['desc_ar'],
                'short_desc_en' => $b['desc_en'],
            ]);
        }

        // 2. SPORTSWEAR AD BANNERS
        $this->command->info("\n--- Seeding Sportswear Product Ad Banners ---");
        $adBanners = [
            [
                'url' => 'https://images.unsplash.com/photo-1556821840-3a63f95609a7?w=800&auto=format&fit=crop',
                'title_ar' => 'هودي تدريب أديداس تك فليس شتوي',
                'title_en' => 'Adidas Tech Fleece Training Hoodie',
                'desc_ar' => 'خامة قطنية دافئة وخفيفة الوزن مثالية للجري الصباحي والتمرين',
                'desc_en' => 'Warm lightweight fleece engineered for outdoor training and daily comfort',
                'price' => 2150.00,
            ],
            [
                'url' => 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?w=800&auto=format&fit=crop',
                'title_ar' => 'حذاء نايكي بيغاسوس 41 للجري لمسافات طويلة',
                'title_en' => 'Nike Pegasus 41 Road Running Shoes',
                'desc_ar' => 'نعل فوم مزدوج لامتصاص الصدمات وتهوية ممتازة للقدم',
                'desc_en' => 'Responsive ReactX foam midsole with breathable engineered mesh upper',
                'price' => 4850.00,
            ],
            [
                'url' => 'https://images.unsplash.com/photo-1518611012118-696072aa579a?w=800&auto=format&fit=crop',
                'title_ar' => 'طقم أكتيف وير نسائي مكون من قطعتين',
                'title_en' => '2-Piece Seamless Women Activewear Set',
                'desc_ar' => 'مرونة عالية وقصة مريحة لليوجا والكارديو والتمارين اليومية',
                'desc_en' => 'High-stretch seamless squat-proof leggings and supportive top',
                'price' => 1790.00,
            ],
        ];

        foreach ($adBanners as $index => $ad) {
            $filename = 'sport_ad_' . ($index + 1) . '.jpg';
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

        // 3. SPORTSWEAR CATEGORIES (6 Main + 6 Subcategories)
        $this->command->info("\n--- Seeding Sportswear Categories ---");
        $categoriesData = [
            [
                'key' => 'mens',
                'name_ar' => 'ملابس رياضية رجالي',
                'name_en' => "Men's Sportswear",
                'url' => 'https://images.unsplash.com/photo-1521572163474-6864f9cf17ab?w=600&auto=format&fit=crop',
                'filename' => 'sport_cat_mens.jpg',
                'children' => [
                    ['name_ar' => 'تيشيرتات وتوبس الجيم', 'name_en' => 'Gym T-Shirts & Tops'],
                    ['name_ar' => 'تريننج وبنطلونات رياضية', 'name_en' => 'Tracksuits & Joggers'],
                ],
            ],
            [
                'key' => 'womens',
                'name_ar' => 'ملابس رياضية حريمي',
                'name_en' => "Women's Activewear",
                'url' => 'https://images.unsplash.com/photo-1518611012118-696072aa579a?w=600&auto=format&fit=crop',
                'filename' => 'sport_cat_womens.jpg',
                'children' => [
                    ['name_ar' => 'ليجنز وتوبس رياضية', 'name_en' => 'Leggings & Sports Tops'],
                    ['name_ar' => 'أطقم يوجا وبيلاتس', 'name_en' => 'Yoga & Pilates Sets'],
                ],
            ],
            [
                'key' => 'shoes',
                'name_ar' => 'أحذية رياضية وجري',
                'name_en' => 'Running & Training Shoes',
                'url' => 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?w=600&auto=format&fit=crop',
                'filename' => 'sport_cat_shoes.jpg',
                'children' => [
                    ['name_ar' => 'أحذية جري ومشي', 'name_en' => 'Running Sneakers'],
                    ['name_ar' => 'أحذية كرة قدم وتدريب', 'name_en' => 'Football & Gym Shoes'],
                ],
            ],
            [
                'key' => 'jerseys',
                'name_ar' => 'تيشيرتات وأطقم كرة القدم',
                'name_en' => 'Football Kits & Jerseys',
                'url' => 'https://images.unsplash.com/photo-1579952363873-27f3bade9f55?w=600&auto=format&fit=crop',
                'filename' => 'sport_cat_jerseys.jpg',
                'children' => [],
            ],
            [
                'key' => 'outerwear',
                'name_ar' => 'هوديز وجواكيت رياضية',
                'name_en' => 'Sport Hoodies & Jackets',
                'url' => 'https://images.unsplash.com/photo-1556821840-3a63f95609a7?w=600&auto=format&fit=crop',
                'filename' => 'sport_cat_hoodies.jpg',
                'children' => [],
            ],
            [
                'key' => 'gear',
                'name_ar' => 'شنط وإكسسوارات الجيم',
                'name_en' => 'Gym Bags & Accessories',
                'url' => 'https://images.unsplash.com/photo-1553062407-98eeb64c6a62?w=600&auto=format&fit=crop',
                'filename' => 'sport_cat_gear.jpg',
                'children' => [],
            ],
        ];

        $this->downloadAndSaveImage('https://images.unsplash.com/photo-1489987707025-afc232f7ea0f?w=400&auto=format&fit=crop', 'categories', 'default.png');

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

        // 4. SPORTSWEAR BRANDS
        $this->command->info("\n--- Seeding Sportswear Brands ---");
        $brandsData = [
            ['name_ar' => 'نايكي', 'name_en' => 'Nike', 'url' => 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?w=400&auto=format&fit=crop', 'filename' => 'sport_brand_nike.jpg'],
            ['name_ar' => 'أديداس', 'name_en' => 'Adidas', 'url' => 'https://images.unsplash.com/photo-1556906781-9a412961c28c?w=400&auto=format&fit=crop', 'filename' => 'sport_brand_adidas.jpg'],
            ['name_ar' => 'بوما', 'name_en' => 'Puma', 'url' => 'https://images.unsplash.com/photo-1460353581641-37baddab0fa2?w=400&auto=format&fit=crop', 'filename' => 'sport_brand_puma.jpg'],
            ['name_ar' => 'أندر آرمور', 'name_en' => 'Under Armour', 'url' => 'https://images.unsplash.com/photo-1517836357463-d25dfeac3438?w=400&auto=format&fit=crop', 'filename' => 'sport_brand_ua.jpg'],
            ['name_ar' => 'ريبوك', 'name_en' => 'Reebok', 'url' => 'https://images.unsplash.com/photo-1600185365926-3a2ce3cdb9eb?w=400&auto=format&fit=crop', 'filename' => 'sport_brand_reebok.jpg'],
            ['name_ar' => 'نيو بالانس', 'name_en' => 'New Balance', 'url' => 'https://images.unsplash.com/photo-1552902865-b72c031ac5ea?w=400&auto=format&fit=crop', 'filename' => 'sport_brand_nb.jpg'],
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

        // 5. SPORTSWEAR IMAGES POOL
        $this->command->info("\n--- Downloading Sportswear Product Images ---");
        $imageUrls = [
            'tshirt' => 'https://images.unsplash.com/photo-1521572163474-6864f9cf17ab?w=600&auto=format&fit=crop',
            'hoodie' => 'https://images.unsplash.com/photo-1556821840-3a63f95609a7?w=600&auto=format&fit=crop',
            'joggers' => 'https://images.unsplash.com/photo-1552902865-b72c031ac5ea?w=600&auto=format&fit=crop',
            'womens_set' => 'https://images.unsplash.com/photo-1518611012118-696072aa579a?w=600&auto=format&fit=crop',
            'jacket' => 'https://images.unsplash.com/photo-1591047139829-d91aecb6caea?w=600&auto=format&fit=crop',
            'shoe_red' => 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?w=600&auto=format&fit=crop',
            'shoe_white' => 'https://images.unsplash.com/photo-1600185365926-3a2ce3cdb9eb?w=600&auto=format&fit=crop',
            'shoe_run' => 'https://images.unsplash.com/photo-1460353581641-37baddab0fa2?w=600&auto=format&fit=crop',
            'jersey' => 'https://images.unsplash.com/photo-1579952363873-27f3bade9f55?w=600&auto=format&fit=crop',
            'gym_bag' => 'https://images.unsplash.com/photo-1553062407-98eeb64c6a62?w=600&auto=format&fit=crop',
            'cap' => 'https://images.unsplash.com/photo-1588850561407-ed78c282e89b?w=600&auto=format&fit=crop',
            'rack' => 'https://images.unsplash.com/photo-1489987707025-afc232f7ea0f?w=600&auto=format&fit=crop',
        ];

        $imageFiles = [];
        foreach ($imageUrls as $key => $url) {
            $fn = "sport_prod_{$key}.jpg";
            $this->downloadAndSaveImage($url, 'products', $fn);
            $imageFiles[$key] = $fn;
        }

        // 6. SEED 32 SPORTSWEAR PRODUCTS (WITH MULTI-VARIANT COLORS/SIZES & VARIED PRICES)
        $this->command->info("\n--- Seeding 32 Sportswear Products ---");

        $productsSpec = [
            [
                'name_ar' => 'تريننج رياضي نايكي دراي-فت أكاديمي كامل',
                'name_en' => 'Nike Dri-FIT Academy Full Tracksuit',
                'desc_ar' => 'تريننج رجالي قطعتين بتقنية طرد العرق وقصة سليم فيت مثالية للتمرين والجري',
                'desc_en' => 'Two-piece men training tracksuit featuring moisture-wicking Dri-FIT fabric and zip pockets',
                'brand' => 'Nike',
                'cat' => 'mens',
                'img' => $imageFiles['joggers'],
                'variants' => [
                    [
                        'price' => 2850.00,
                        'stock' => 30,
                        'discount' => 15,
                        'attributes' => [
                            ['key_en' => 'Color', 'key_ar' => 'اللون', 'value_en' => 'Black', 'value_ar' => 'أسود'],
                            ['key_en' => 'Size', 'key_ar' => 'المقاس', 'value_en' => 'Medium', 'value_ar' => 'متوسط'],
                        ],
                    ],
                    [
                        'price' => 2950.00,
                        'stock' => 24,
                        'discount' => 10,
                        'attributes' => [
                            ['key_en' => 'Color', 'key_ar' => 'اللون', 'value_en' => 'Navy Blue', 'value_ar' => 'كحلي'],
                            ['key_en' => 'Size', 'key_ar' => 'المقاس', 'value_en' => 'Large', 'value_ar' => 'كبير'],
                        ],
                    ],
                ],
            ],
            [
                'name_ar' => 'حذاء جري نايكي اير زوم بيغاسوس 41',
                'name_en' => 'Nike Air Zoom Pegasus 41 Running Shoes',
                'desc_ar' => 'حذاء جري خفيف الوزن بوحدة Air Zoom مزدوجة لامتصاص الصدمات على الأسفلت والتراك',
                'desc_en' => 'Lightweight road running sneaker with dual Air Zoom units and breathable mesh upper',
                'brand' => 'Nike',
                'cat' => 'shoes',
                'img' => $imageFiles['shoe_red'],
                'variants' => [
                    [
                        'price' => 4850.00,
                        'stock' => 20,
                        'discount' => 12,
                        'attributes' => [
                            ['key_en' => 'Color', 'key_ar' => 'اللون', 'value_en' => 'Red', 'value_ar' => 'أحمر'],
                            ['key_en' => 'Size', 'key_ar' => 'المقاس', 'value_en' => '42', 'value_ar' => '42'],
                        ],
                    ],
                    [
                        'price' => 5150.00,
                        'stock' => 15,
                        'discount' => 10,
                        'attributes' => [
                            ['key_en' => 'Color', 'key_ar' => 'اللون', 'value_en' => 'Black', 'value_ar' => 'أسود'],
                            ['key_en' => 'Size', 'key_ar' => 'المقاس', 'value_en' => '44', 'value_ar' => '44'],
                        ],
                    ],
                ],
            ],
            [
                'name_ar' => 'تيشيرت جيم أديداس أيروريدي سليم فيت',
                'name_en' => 'Adidas AEROREADY Training Slim T-Shirt',
                'desc_ar' => 'تيشيرت تدريب عالي التهوية بخامة شبكية تمنع احتباس الحرارة أثناء رفع الأثقال والكارديو',
                'desc_en' => 'Moisture-absorbing workout tee with underarm gussets for unrestricted range of motion',
                'brand' => 'Adidas',
                'cat' => 'mens',
                'img' => $imageFiles['tshirt'],
                'variants' => [
                    [
                        'price' => 890.00,
                        'stock' => 55,
                        'discount' => 20,
                        'attributes' => [
                            ['key_en' => 'Color', 'key_ar' => 'اللون', 'value_en' => 'White', 'value_ar' => 'أبيض'],
                            ['key_en' => 'Size', 'key_ar' => 'المقاس', 'value_en' => 'Large', 'value_ar' => 'كبير'],
                        ],
                    ],
                    [
                        'price' => 940.00,
                        'stock' => 40,
                        'discount' => 15,
                        'attributes' => [
                            ['key_en' => 'Color', 'key_ar' => 'اللون', 'value_en' => 'Black', 'value_ar' => 'أسود'],
                            ['key_en' => 'Size', 'key_ar' => 'المقاس', 'value_en' => 'XL', 'value_ar' => 'كبير جداً'],
                        ],
                    ],
                ],
            ],
            [
                'name_ar' => 'طقم أكتيف وير نسائي أديداس ستوديو قطعتين',
                'name_en' => 'Adidas Studio Seamless Women 2-Piece Set',
                'desc_ar' => 'طقم ليجينز عالي الخصر مع توب داعم بخامة مرنة ناعمة غير شفافة لليوجا والجيم',
                'desc_en' => 'High-waisted squat-proof leggings and supportive longline bra crafted from 4-way stretch knit',
                'brand' => 'Adidas',
                'cat' => 'womens',
                'img' => $imageFiles['womens_set'],
                'variants' => [
                    [
                        'price' => 1790.00,
                        'stock' => 28,
                        'discount' => 15,
                        'attributes' => [
                            ['key_en' => 'Color', 'key_ar' => 'اللون', 'value_en' => 'Olive Green', 'value_ar' => 'أخضر زيتوني'],
                            ['key_en' => 'Size', 'key_ar' => 'المقاس', 'value_en' => 'Small', 'value_ar' => 'صغير'],
                        ],
                    ],
                    [
                        'price' => 1890.00,
                        'stock' => 25,
                        'discount' => null,
                        'attributes' => [
                            ['key_en' => 'Color', 'key_ar' => 'اللون', 'value_en' => 'Black', 'value_ar' => 'أسود'],
                            ['key_en' => 'Size', 'key_ar' => 'المقاس', 'value_en' => 'Medium', 'value_ar' => 'متوسط'],
                        ],
                    ],
                ],
            ],
            [
                'name_ar' => 'هودي تدريب أندر آرمور أرمور فليس بسوستة',
                'name_en' => 'Under Armour Armour Fleece Training Hoodie',
                'desc_ar' => 'سويت شيرت رياضي خفيف ببطانة فليس ناعمة تحفظ الدفء بدون إعاقة الحركة في الشتاء',
                'desc_en' => 'Warm brushed-inner fleece athletic hoodie with breathable quick-dry outer finish',
                'brand' => 'Under Armour',
                'cat' => 'outerwear',
                'img' => $imageFiles['hoodie'],
                'variants' => [
                    [
                        'price' => 2150.00,
                        'stock' => 32,
                        'discount' => 10,
                        'attributes' => [
                            ['key_en' => 'Color', 'key_ar' => 'اللون', 'value_en' => 'Charcoal Gray', 'value_ar' => 'رمادي فحمي'],
                            ['key_en' => 'Size', 'key_ar' => 'المقاس', 'value_en' => 'Large', 'value_ar' => 'كبير'],
                        ],
                    ],
                    [
                        'price' => 2290.00,
                        'stock' => 20,
                        'discount' => null,
                        'attributes' => [
                            ['key_en' => 'Color', 'key_ar' => 'اللون', 'value_en' => 'Black', 'value_ar' => 'أسود'],
                            ['key_en' => 'Size', 'key_ar' => 'المقاس', 'value_en' => 'XL', 'value_ar' => 'كبير جداً'],
                        ],
                    ],
                ],
            ],
            [
                'name_ar' => 'حذاء أديداس ألترابوست لايت الرياضي',
                'name_en' => 'Adidas Ultraboost Light Running Sneakers',
                'desc_ar' => 'أخف حذاء ألترابوست بإرجاع طاقة فائق مع نعل كونتيننتال مقاوم للانزلاق',
                'desc_en' => 'Ultra-responsive Light BOOST midsole paired with Continental rubber outsole grip',
                'brand' => 'Adidas',
                'cat' => 'shoes',
                'img' => $imageFiles['shoe_white'],
                'variants' => [
                    [
                        'price' => 5450.00,
                        'stock' => 18,
                        'discount' => 15,
                        'attributes' => [
                            ['key_en' => 'Color', 'key_ar' => 'اللون', 'value_en' => 'White', 'value_ar' => 'أبيض'],
                            ['key_en' => 'Size', 'key_ar' => 'المقاس', 'value_en' => '41', 'value_ar' => '41'],
                        ],
                    ],
                    [
                        'price' => 5650.00,
                        'stock' => 14,
                        'discount' => 10,
                        'attributes' => [
                            ['key_en' => 'Color', 'key_ar' => 'اللون', 'value_en' => 'Core Black', 'value_ar' => 'أسود'],
                            ['key_en' => 'Size', 'key_ar' => 'المقاس', 'value_en' => '43', 'value_ar' => '43'],
                        ],
                    ],
                ],
            ],
            [
                'name_ar' => 'طقم كرة قدم بوما ألترا ماتش داي (تيشيرت + شورت)',
                'name_en' => 'Puma Ultra Match-Day Football Kit Set',
                'desc_ar' => 'طقم كرة قدم احترافي بخامة dryCELL فائقة التهوية وخفة الوزن للملاعب المفتوحة',
                'desc_en' => 'Pro breathable dryCELL football jersey and matching shorts engineered for 90-minute comfort',
                'brand' => 'Puma',
                'cat' => 'jerseys',
                'img' => $imageFiles['jersey'],
                'variants' => [
                    [
                        'price' => 1450.00,
                        'stock' => 45,
                        'discount' => 25,
                        'attributes' => [
                            ['key_en' => 'Color', 'key_ar' => 'اللون', 'value_en' => 'Blue', 'value_ar' => 'أزرق'],
                            ['key_en' => 'Size', 'key_ar' => 'المقاس', 'value_en' => 'Medium', 'value_ar' => 'متوسط'],
                        ],
                    ],
                    [
                        'price' => 1550.00,
                        'stock' => 30,
                        'discount' => 15,
                        'attributes' => [
                            ['key_en' => 'Color', 'key_ar' => 'اللون', 'value_en' => 'Red', 'value_ar' => 'أحمر'],
                            ['key_en' => 'Size', 'key_ar' => 'المقاس', 'value_en' => 'Large', 'value_ar' => 'كبير'],
                        ],
                    ],
                ],
            ],
            [
                'name_ar' => 'جاكيت جري مضاد للرياح والمطر نيو بالانس',
                'name_en' => 'New Balance Impact Run Windbreaker Jacket',
                'desc_ar' => 'جاكيت رياضي خفيف جداً طارد للماء والرياح مع فتحات تهوية خلفية وشرائط عاكسة للضوء',
                'desc_en' => 'Water-resistant packable running windbreaker with back vent and 360-degree reflective trims',
                'brand' => 'New Balance',
                'cat' => 'outerwear',
                'img' => $imageFiles['jacket'],
                'variants' => [
                    [
                        'price' => 2650.00,
                        'stock' => 22,
                        'discount' => 18,
                        'attributes' => [
                            ['key_en' => 'Color', 'key_ar' => 'اللون', 'value_en' => 'Black', 'value_ar' => 'أسود'],
                            ['key_en' => 'Size', 'key_ar' => 'المقاس', 'value_en' => 'Large', 'value_ar' => 'كبير'],
                        ],
                    ],
                    [
                        'price' => 2750.00,
                        'stock' => 17,
                        'discount' => null,
                        'attributes' => [
                            ['key_en' => 'Color', 'key_ar' => 'اللون', 'value_en' => 'Green', 'value_ar' => 'أخضر'],
                            ['key_en' => 'Size', 'key_ar' => 'المقاس', 'value_en' => 'XL', 'value_ar' => 'كبير جداً'],
                        ],
                    ],
                ],
            ],
        ];

        $singleVariantProducts = [
            ['تيشيرت كومبريشن أندر آرمور هيت جير', 'Under Armour HeatGear Compression Tee', 'تيشيرت ضاغط يدعم العضلات ويحافظ على برودة الجسم طوال التمرين', 'Ultra-tight second-skin compression shirt with mesh underarm ventilation panels', 'Under Armour', 'mens', $imageFiles['tshirt'], 1180.00, 10, ['Color' => ['ar' => 'أسود', 'en' => 'Black'], 'Size' => ['ar' => 'متوسط', 'en' => 'Medium']]],
            ['بنطلون رياضي بوما سليم فيت تيبرد', 'Puma Essentials Tapered Sweatpants', 'بنطلون رياضي قطن مع بوليستر بخصر مطاطي وجيوب جانبية بسوستة', 'Soft cotton-blend tapered athletic joggers with ribbed cuffs and zip pockets', 'Puma', 'mens', $imageFiles['joggers'], 1390.00, 15, ['Color' => ['ar' => 'رمادي', 'en' => 'Gray'], 'Size' => ['ar' => 'كبير', 'en' => 'Large']]],
            ['شورت تدريب نايكي فليكس سترايد 7 بوصة', 'Nike Flex Stride 7" Running Shorts', 'شورت جري خفيف الوزن مع بطانة داخلية وجيب خلفي آمن للموبايل', 'Breathable 7-inch running shorts featuring brief liner and zippered back phone pocket', 'Nike', 'mens', $imageFiles['joggers'], 980.00, null, ['Color' => ['ar' => 'كحلي', 'en' => 'Navy'], 'Size' => ['ar' => 'كبير', 'en' => 'Large']]],
            ['تانك توب جيم ريبوك قطن سترتش', 'Reebok Identity Gym Sleeveless Tank', 'تانك توب مريح بفتحة واسعة لتمارين الكتف والظهر والجيم الصيفي', 'Soft cotton-blend sleeveless workout tank built for summer and strength sessions', 'Reebok', 'mens', $imageFiles['tshirt'], 640.00, 12, ['Color' => ['ar' => 'أبيض', 'en' => 'White'], 'Size' => ['ar' => 'متوسط', 'en' => 'Medium']]],
            ['بولو رياضي أديداس كلوب للتنس والجولف', 'Adidas Club Tennis Breathable Polo', 'تيشيرت بولو رياضي أنيق بياقة كلاسيكية وخامة سريعة الجفاف', 'Classic athletic polo crafted from recycled quick-dry Climalite jersey fabric', 'Adidas', 'mens', $imageFiles['rack'], 1520.00, null, ['Color' => ['ar' => 'أبيض', 'en' => 'White'], 'Size' => ['ar' => 'كبير جداً', 'en' => 'XL']]],
            ['ليجنز نسائي نايكي ون عالي الخصر', 'Nike One High-Waisted Sculpt Leggings', 'ليجنز رياضي غير شفاف يدعم الخصر ويوفر راحة تامة طوال اليوم', 'Contoured high-rise fitness leggings with hidden waistband pocket and sweat-wicking tech', 'Nike', 'womens', $imageFiles['womens_set'], 1650.00, 20, ['Color' => ['ar' => 'أسود', 'en' => 'Black'], 'Size' => ['ar' => 'متوسط', 'en' => 'Medium']]],
            ['سبورتس برا بوما ميد إمباكت للكارديو', 'Puma Fit Mid-Impact Sports Bra', 'حمالة صدر رياضية بدعم متوسط وشرائط ظهر متقاطعة لثبات مثالي', 'Supportive racerback mid-impact sports bra with breathable power-mesh lining', 'Puma', 'womens', $imageFiles['womens_set'], 850.00, null, ['Color' => ['ar' => 'وردي', 'en' => 'Rose Pink'], 'Size' => ['ar' => 'صغير', 'en' => 'Small']]],
            ['تيشيرت تدريب حريمي أندر آرمور كروس باك', 'Under Armour Tech Twist V-Neck Tee', 'تيشيرت حريمي ناعم وخفيف جداً يجف بسرعة فائقة أثناء التمرين', 'Loose ultra-soft UA Tech activewear tee with natural cotton-like feel', 'Under Armour', 'womens', $imageFiles['womens_set'], 920.00, 15, ['Color' => ['ar' => 'أزرق سماوي', 'en' => 'Sky Blue'], 'Size' => ['ar' => 'متوسط', 'en' => 'Medium']]],
            ['سويت شيرت نسائي أوفرسايز ريبوك كلاسيك', 'Reebok Classic Oversized Active Sweatshirt', 'بلوفر رياضي مريح بقصة عصرية واسعة مثالي قبل وبعد الجيم', 'Cozy oversized fleece crew sweatshirt ideal for studio warm-ups and street style', 'Reebok', 'womens', $imageFiles['hoodie'], 1480.00, 10, ['Color' => ['ar' => 'بيج', 'en' => 'Beige'], 'Size' => ['ar' => 'متوسط', 'en' => 'Medium']]],
            ['شورت بايكر نسائي نيو بالانس هامونيك', 'New Balance Harmony Bike Shorts', 'شورت رياضي عالي الخصر لركوب الدراجات واليوجا والجري', 'Smooth high-rise compression biker shorts designed for cycling and studio sessions', 'New Balance', 'womens', $imageFiles['womens_set'], 790.00, null, ['Color' => ['ar' => 'أخضر', 'en' => 'Green'], 'Size' => ['ar' => 'صغير', 'en' => 'Small']]],
            ['حذاء نيو بالانس 1080v13 فوم فريش للجري', 'New Balance Fresh Foam X 1080v13 Shoes', 'أعلى مستوى من التبطين الناعم للماراثون والمشي لمسافات طويلة', 'Plush Fresh Foam X cushioning engineered for daily high-mileage comfort', 'New Balance', 'shoes', $imageFiles['shoe_run'], 5290.00, 12, ['Color' => ['ar' => 'أبيض/رمادي', 'en' => 'White/Gray'], 'Size' => ['ar' => '43', 'en' => '43']]],
            ['حذاء ريبوك نانو X4 لرفع الأثقال والكروس فت', 'Reebok Nano X4 Cross-Training Shoes', 'حذاء جيم ثابت ومسطح الكعب يمنحك أقصى ثبات أثناء السكوات والأثقال', 'Stable lifting heel and Flexweave knit upper built for HIIT and strength workouts', 'Reebok', 'shoes', $imageFiles['shoe_white'], 3890.00, null, ['Color' => ['ar' => 'أبيض', 'en' => 'White'], 'Size' => ['ar' => '42', 'en' => '42']]],
            ['حذاء كرة قدم نايكي ميركوريال فيبور 15 ترتان', 'Nike Mercurial Vapor 15 Turf Football Boots', 'حذاء ملاعب نجيل صناعي بنعل مطاطي قوي للتحكم بالكرة والسرعة', 'Low-profile synthetic turf soccer cleats featuring Zoom Air heel cushioning', 'Nike', 'shoes', $imageFiles['shoe_red'], 3450.00, 15, ['Color' => ['ar' => 'أحمر/أسود', 'en' => 'Red/Black'], 'Size' => ['ar' => '42', 'en' => '42']]],
            ['حذاء جري بوما فيلوسيتي نيترو 3', 'Puma Velocity NITRO 3 Running Shoes', 'خامة نيترو خفيفة تمنحك ارتداداً سريعاً وتماسكاً على الطرق الرطبة', 'Nitrogen-infused NITRO foam daily trainer with durable PUMAGRIP traction', 'Puma', 'shoes', $imageFiles['shoe_run'], 3980.00, 18, ['Color' => ['ar' => 'أزرق', 'en' => 'Blue'], 'Size' => ['ar' => '44', 'en' => '44']]],
            ['تيشيرت كرة قدم أديداس نسخة المشجعين 2026', 'Adidas 2026 Pro Stadium Football Jersey', 'تيشيرت مباراة أصلي بنسيج تهوية جانبي وشعار مطرز بجودة عالية', 'Breathable stadium-edition football shirt with ribbed crewneck and woven crest', 'Adidas', 'jerseys', $imageFiles['jersey'], 1350.00, null, ['Color' => ['ar' => 'أبيض', 'en' => 'White'], 'Size' => ['ar' => 'كبير', 'en' => 'Large']]],
            ['طقم تدريب حراس مرمى نايكي بأكمام طويلة', 'Nike Park Goalkeeper Long-Sleeve Jersey', 'تيشيرت كم طويل بحماية خفيفة عند الكوع وخامة سريعة التهوية', 'Long-sleeve goalkeeper match shirt with sweat-wicking fabric and contrast cuffs', 'Nike', 'jerseys', $imageFiles['jersey'], 1290.00, 10, ['Color' => ['ar' => 'أخضر نيون', 'en' => 'Volt Green'], 'Size' => ['ar' => 'كبير', 'en' => 'Large']]],
            ['شورت كرة قدم أديداس تيرو 24 برو', 'Adidas Tiro 24 Pro Football Shorts', 'شورت كرة خفيف بأطراف شبكية جانبية وحبل ربط داخلي محكم', 'Lightweight match shorts crafted with mesh side inserts for peak pitch ventilation', 'Adidas', 'jerseys', $imageFiles['jersey'], 690.00, null, ['Color' => ['ar' => 'أسود', 'en' => 'Black'], 'Size' => ['ar' => 'متوسط', 'en' => 'Medium']]],
            ['هودي نايكي كلوب فليس كلاسيك بغطاء رأس', 'Nike Sportswear Club Fleece Pullover Hoodie', 'هودي قطن ناعم للاستخدام اليومي وبعد التمرين بجيب أمامي واسع', 'Soft brushed-back cotton fleece pullover hoodie with ribbed hem and kangaroo pocket', 'Nike', 'outerwear', $imageFiles['hoodie'], 1950.00, 15, ['Color' => ['ar' => 'أحمر نبيتي', 'en' => 'Burgundy'], 'Size' => ['ar' => 'كبير', 'en' => 'Large']]],
            ['سويت شيرت أديداس ثري سترايبس بنصف سوستة', 'Adidas 3-Stripes Half-Zip Track Top', 'جاكيت تدريب خفيف بتصميم الخطوط الثلاثة الشهيرة وياقة مرتفعة', 'Signature 3-Stripes athletic track top with stand-up collar and zip closure', 'Adidas', 'outerwear', $imageFiles['jacket'], 2190.00, null, ['Color' => ['ar' => 'أسود', 'en' => 'Black'], 'Size' => ['ar' => 'متوسط', 'en' => 'Medium']]],
            ['فيست تدريب شتوي بوما بدون أكمام مبطن', 'Puma PackLITE Padded Training Vest', 'جاكيت فيست خفيف بدون أكمام يحفظ حرارة الجسم أثناء الجري الشتوي', 'Insulated lightweight sleeveless running gilet keeping your core warm outdoors', 'Puma', 'outerwear', $imageFiles['jacket'], 2450.00, 20, ['Color' => ['ar' => 'أسود', 'en' => 'Black'], 'Size' => ['ar' => 'كبير', 'en' => 'Large']]],
            ['شنطة جيم دفل نايكي برازيليا مقاس متوسط', 'Nike Brasilia 9.5 Medium Gym Duffel Bag', 'شنطة جيم عملية بجيب منفصل للأحذية وجيوب جانبية لزجاجة المياه', 'Durable 60L training duffel with ventilated shoe compartment and padded strap', 'Nike', 'gear', $imageFiles['gym_bag'], 1490.00, 12, ['Color' => ['ar' => 'أسود', 'en' => 'Black'], 'Size' => ['ar' => '60 لتر', 'en' => '60L']]],
            ['كاب جري أديداس ايروريدي خفيف الوزن', 'Adidas AEROREADY Runner Sports Cap', 'كاب رياضي بفتحات تهوية ليزرية وحماية من الشمس أثناء الجري بالنهار', 'Lightweight curved-brim athletic cap with moisture-wicking sweatband', 'Adidas', 'gear', $imageFiles['cap'], 490.00, null, ['Color' => ['ar' => 'أبيض', 'en' => 'White'], 'Size' => ['ar' => 'مقاس موحد', 'en' => 'One Size']]],
            ['طقم 3 أزواج شراب رياضي نايكي كرو مبطن', 'Nike Everyday Cushioned Crew Socks (3-Pack)', 'جوارب رياضية مبطنة عند الكعب والمقدمة لامتصاص الصدمات في الجيم والجري', 'Thick-terry cushioned athletic crew socks with supportive arch band (3 pairs)', 'Nike', 'gear', $imageFiles['cap'], 390.00, null, ['Color' => ['ar' => 'أبيض/أسود', 'en' => 'White/Black'], 'Size' => ['ar' => '40-44', 'en' => 'EU 40-44']]],
            ['قفازات رفع أثقال أندر آرمور مبطنة جلد', 'Under Armour Weightlifting Gym Gloves', 'قفازات تمرين تحمي راحة اليد وتمنع الانزلاق مع رباط معصم داعم', 'Textured leather-palm lifting gloves featuring perforated breathability and wrist wrap', 'Under Armour', 'gear', $imageFiles['gym_bag'], 720.00, 10, ['Color' => ['ar' => 'أسود', 'en' => 'Black'], 'Size' => ['ar' => 'كبير', 'en' => 'Large']]],
        ];

        foreach ($singleVariantProducts as $p) {
            $attrs = [];
            foreach ($p[9] as $keyEn => $valPair) {
                $keyAr = match ($keyEn) {
                    'Color' => 'اللون',
                    'Size' => 'المقاس',
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
                        'stock' => rand(18, 95),
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

        // 7. SEED SAMPLE REVIEWER ACCOUNTS & SPORTSWEAR REVIEWS
        $this->command->info("\n--- Seeding Customer Accounts & Sportswear Reviews ---");
        $reviewersSpec = [
            ['first_name' => 'أحمد', 'last_name' => 'شريف', 'email' => 'ahmed.sherif@mousastore.test', 'phone' => '01011223344'],
            ['first_name' => 'مريم', 'last_name' => 'عادل', 'email' => 'mariam.adel@mousastore.test', 'phone' => '01122334455'],
            ['first_name' => 'حسام', 'last_name' => 'فوزي', 'email' => 'hossam.fawzy@mousastore.test', 'phone' => '01233445566'],
            ['first_name' => 'نور', 'last_name' => 'إيهاب', 'email' => 'nour.ehab@mousastore.test', 'phone' => '01544556677'],
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
            ['rate' => '5', 'comment' => 'الخامة ناعمة جداً ومريحة وقت التمرين في الجيم وبتشرب العرق بسرعة، أنصح بيه.'],
            ['rate' => '5', 'comment' => 'Super breathable fabric and true to size! Perfect for daily running.'],
            ['rate' => '4', 'comment' => 'التقفيل والخياطة ممتازين والمقاس مظبوط بالظبط، التوصيل كان سريع.'],
            ['rate' => '5', 'comment' => 'مريح جداً في الجري وخفيف على الرجل، يستاهل كل جنيه مقارنة بالسوق.'],
            ['rate' => '4', 'comment' => 'Great fit and stretching capability during squats and cardio workouts.'],
            ['rate' => '5', 'comment' => 'اللون في الحقيقة أشيك من الصور بكتير وما بهتش بعد الغسيل، شكراً موسى ستور.'],
            ['rate' => '3', 'comment' => 'الخامة ممتازة بس ياريت لو فيه مقاسات أكتر من الألوان الفاتحة.'],
            ['rate' => '5', 'comment' => 'Authentic athletic quality, lightweight feel, and clean packaging.'],
        ];

        $totalReviews = 0;
        foreach ($createdProducts as $prodIdx => $prod) {
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

        $this->call(GovernorateSeeder::class);

        $this->command->info("\n========================================================");
        $this->command->info("🎉 MOUSA STORE SPORTSWEAR SEEDING COMPLETED SUCCESSFULLY!");
        $this->command->info("Products: " . count($createdProducts) . " | Variants: {$totalVariants} | Reviews: {$totalReviews}");
        $this->command->info("========================================================\n");
    }
}
