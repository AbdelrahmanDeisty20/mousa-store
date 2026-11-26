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
        $this->command->info("Starting fresh Mousa Store 100-product Sportswear seeding...");

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

        // 2. PRODUCT AD BANNERS
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

        // 3. CATEGORIES (6 Main + 6 Subcategories = 12 Categories)
        $this->command->info("\n--- Seeding 12 Sportswear Categories ---");
        $categoriesData = [
            [
                'key' => 'mens',
                'name_ar' => 'ملابس رياضية رجالي',
                'name_en' => "Men's Sportswear",
                'url' => 'https://images.unsplash.com/photo-1521572163474-6864f9cf17ab?w=600&auto=format&fit=crop',
                'filename' => 'sport_cat_mens.jpg',
                'children' => [
                    ['key' => 'mens_tops', 'name_ar' => 'تيشيرتات وتوبس الجيم', 'name_en' => 'Gym T-Shirts & Tops'],
                    ['key' => 'mens_track', 'name_ar' => 'تريننج وبنطلونات رياضية', 'name_en' => 'Tracksuits & Joggers'],
                ],
            ],
            [
                'key' => 'womens',
                'name_ar' => 'ملابس رياضية حريمي',
                'name_en' => "Women's Activewear",
                'url' => 'https://images.unsplash.com/photo-1518611012118-696072aa579a?w=600&auto=format&fit=crop',
                'filename' => 'sport_cat_womens.jpg',
                'children' => [
                    ['key' => 'womens_leggings', 'name_ar' => 'ليجنز وتوبس رياضية', 'name_en' => 'Leggings & Sports Tops'],
                    ['key' => 'womens_yoga', 'name_ar' => 'أطقم يوجا وبيلاتس', 'name_en' => 'Yoga & Pilates Sets'],
                ],
            ],
            [
                'key' => 'shoes',
                'name_ar' => 'أحذية رياضية وجري',
                'name_en' => 'Running & Training Shoes',
                'url' => 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?w=600&auto=format&fit=crop',
                'filename' => 'sport_cat_shoes.jpg',
                'children' => [
                    ['key' => 'shoes_run', 'name_ar' => 'أحذية جري ومشي', 'name_en' => 'Running Sneakers'],
                    ['key' => 'shoes_football', 'name_ar' => 'أحذية كرة قدم وتدريب', 'name_en' => 'Football & Gym Shoes'],
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
                $childCat = Category::create([
                    'name_ar' => $child['name_ar'],
                    'name_en' => $child['name_en'],
                    'image' => $c['filename'],
                    'sort_order' => $sortCounter++,
                    'parent_id' => $parent->id,
                ]);
                $createdCategories[$child['key']] = $childCat;
            }
        }

        // 4. BRANDS
        $this->command->info("\n--- Seeding 6 Sportswear Brands ---");
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

        // 5. SPORTSWEAR IMAGE POOL (18 verified images for main_image + sub-gallery images)
        $this->command->info("\n--- Downloading 18 Sportswear Product & Gallery Images ---");
        $imageUrls = [
            'tshirt_1'     => 'https://images.unsplash.com/photo-1521572163474-6864f9cf17ab?w=600&auto=format&fit=crop',
            'tshirt_2'     => 'https://images.unsplash.com/photo-1581655353564-df123a1eb820?w=600&auto=format&fit=crop',
            'hoodie_1'     => 'https://images.unsplash.com/photo-1556821840-3a63f95609a7?w=600&auto=format&fit=crop',
            'hoodie_2'     => 'https://images.unsplash.com/photo-1620799140408-edc6dcb6d633?w=600&auto=format&fit=crop',
            'joggers'      => 'https://images.unsplash.com/photo-1552902865-b72c031ac5ea?w=600&auto=format&fit=crop',
            'womens_set'   => 'https://images.unsplash.com/photo-1518611012118-696072aa579a?w=600&auto=format&fit=crop',
            'womens_yoga'  => 'https://images.unsplash.com/photo-1571019613454-1cb2f99b2d8b?w=600&auto=format&fit=crop',
            'jacket'       => 'https://images.unsplash.com/photo-1591047139829-d91aecb6caea?w=600&auto=format&fit=crop',
            'shoe_red'     => 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?w=600&auto=format&fit=crop',
            'shoe_white'   => 'https://images.unsplash.com/photo-1600185365926-3a2ce3cdb9eb?w=600&auto=format&fit=crop',
            'shoe_run'     => 'https://images.unsplash.com/photo-1460353581641-37baddab0fa2?w=600&auto=format&fit=crop',
            'shoe_blue'    => 'https://images.unsplash.com/photo-1539185441755-769473a23570?w=600&auto=format&fit=crop',
            'shoe_black'   => 'https://images.unsplash.com/photo-1551107696-a4b0c5a0d9a2?w=600&auto=format&fit=crop',
            'jersey'       => 'https://images.unsplash.com/photo-1579952363873-27f3bade9f55?w=600&auto=format&fit=crop',
            'gym_bag'      => 'https://images.unsplash.com/photo-1553062407-98eeb64c6a62?w=600&auto=format&fit=crop',
            'cap'          => 'https://images.unsplash.com/photo-1588850561407-ed78c282e89b?w=600&auto=format&fit=crop',
            'rack'         => 'https://images.unsplash.com/photo-1489987707025-afc232f7ea0f?w=600&auto=format&fit=crop',
            'bottle_gear'  => 'https://images.unsplash.com/photo-1517838277536-f5f99be501cd?w=600&auto=format&fit=crop',
        ];

        $imageFiles = [];
        foreach ($imageUrls as $key => $url) {
            $fn = "sport_prod_{$key}.jpg";
            $this->downloadAndSaveImage($url, 'products', $fn);
            $imageFiles[$key] = $fn;
        }

        // Category-specific gallery pools so every product gets main + 3 sub/gallery images
        $galleryPools = [
            'mens'            => [$imageFiles['tshirt_1'], $imageFiles['tshirt_2'], $imageFiles['joggers'], $imageFiles['rack']],
            'mens_tops'       => [$imageFiles['tshirt_2'], $imageFiles['tshirt_1'], $imageFiles['rack'], $imageFiles['hoodie_2']],
            'mens_track'      => [$imageFiles['joggers'], $imageFiles['hoodie_1'], $imageFiles['jacket'], $imageFiles['tshirt_1']],
            'womens'          => [$imageFiles['womens_set'], $imageFiles['womens_yoga'], $imageFiles['hoodie_2'], $imageFiles['rack']],
            'womens_leggings' => [$imageFiles['womens_set'], $imageFiles['womens_yoga'], $imageFiles['tshirt_2'], $imageFiles['hoodie_2']],
            'womens_yoga'     => [$imageFiles['womens_yoga'], $imageFiles['womens_set'], $imageFiles['bottle_gear'], $imageFiles['hoodie_2']],
            'shoes'           => [$imageFiles['shoe_red'], $imageFiles['shoe_white'], $imageFiles['shoe_black'], $imageFiles['shoe_blue']],
            'shoes_run'       => [$imageFiles['shoe_run'], $imageFiles['shoe_blue'], $imageFiles['shoe_white'], $imageFiles['shoe_red']],
            'shoes_football'  => [$imageFiles['shoe_black'], $imageFiles['shoe_red'], $imageFiles['jersey'], $imageFiles['shoe_run']],
            'jerseys'         => [$imageFiles['jersey'], $imageFiles['tshirt_2'], $imageFiles['tshirt_1'], $imageFiles['shoe_black']],
            'outerwear'       => [$imageFiles['hoodie_1'], $imageFiles['hoodie_2'], $imageFiles['jacket'], $imageFiles['joggers']],
            'gear'            => [$imageFiles['gym_bag'], $imageFiles['cap'], $imageFiles['bottle_gear'], $imageFiles['shoe_white']],
        ];

        $colorChoices = [
            ['en' => 'Black', 'ar' => 'أسود'],
            ['en' => 'White', 'ar' => 'أبيض'],
            ['en' => 'Navy Blue', 'ar' => 'كحلي'],
            ['en' => 'Red', 'ar' => 'أحمر'],
            ['en' => 'Charcoal Gray', 'ar' => 'رمادي فحمي'],
            ['en' => 'Olive Green', 'ar' => 'أخضر زيتوني'],
            ['en' => 'Royal Blue', 'ar' => 'أزرق ملكي'],
        ];

        $apparelSizes = [
            ['en' => 'Small', 'ar' => 'صغير (S)'],
            ['en' => 'Medium', 'ar' => 'متوسط (M)'],
            ['en' => 'Large', 'ar' => 'كبير (L)'],
            ['en' => 'XL', 'ar' => 'كبير جداً (XL)'],
        ];

        $shoeSizes = [
            ['en' => '40', 'ar' => '40'],
            ['en' => '41', 'ar' => '41'],
            ['en' => '42', 'ar' => '42'],
            ['en' => '43', 'ar' => '43'],
            ['en' => '44', 'ar' => '44'],
        ];

        $gearSizes = [
            ['en' => 'One Size', 'ar' => 'مقاس موحد'],
            ['en' => 'Medium (40L)', 'ar' => 'متوسط (40 لتر)'],
            ['en' => 'Large (60L)', 'ar' => 'كبير (60 لتر)'],
        ];

        $materials = [
            ['en' => 'Dri-FIT Polyester', 'ar' => 'بوليستر دراي-فت'],
            ['en' => 'Stretch Cotton', 'ar' => 'قطن مرن'],
            ['en' => 'Breathable Mesh', 'ar' => 'شبك عالي التهوية'],
            ['en' => 'Tech Fleece', 'ar' => 'فليس رياضي دافئ'],
        ];

        $fits = [
            ['en' => 'Slim Fit', 'ar' => 'سليم فيت'],
            ['en' => 'Regular Fit', 'ar' => 'قصة مريحة'],
            ['en' => 'Athletic Fit', 'ar' => 'قصة رياضية'],
        ];

        // 6. BUILD EXACTLY 100 PRODUCTS DISTRIBUTED ACROSS ALL 12 CATEGORIES
        $this->command->info("\n--- Seeding 100 Distributed Sportswear Products ---");

        $brandNames = array_keys($createdBrands);
        $categoryCatalog = [
            'mens' => [
                'base_price' => 1450,
                'items' => [
                    ['طقم تدريب رجالي متكامل', 'Men Complete Training Outfit'],
                    ['طقم جري صيفي خفيف', 'Men Summer Running Kit'],
                    ['بدلة رياضية كلاسيك رجالي', 'Men Classic Sport Tracksuit'],
                    ['طقم تمارين كارديو رجالي', 'Men Cardio Performance Set'],
                    ['بدلة تدريب يومي مريحة', 'Men Daily Gym Warmup Suit'],
                    ['طقم أكتيف وير رجالي بجيوب', 'Men Utility Activewear Set'],
                    ['طقم جيم احترافي سريع الجفاف', 'Men Quick-Dry Pro Gym Kit'],
                    ['بدلة رياضية خفيفة للمشي', 'Men Lightweight Walking Suit'],
                    ['طقم رياضي سليم فيت رجالي', 'Men Slim Fit Athletic Set'],
                ],
            ],
            'mens_tops' => [
                'base_price' => 680,
                'items' => [
                    ['تيشيرت جيم دراي-فت سليم', 'Men Dri-FIT Slim Gym Tee'],
                    ['تيشيرت كومبريشن داعم للعضلات', 'Compression Muscle Support Top'],
                    ['تانك توب تدريب بفتحة واسعة', 'Sleeveless Muscle Training Tank'],
                    ['تيشيرت بولو رياضي مسامي', 'Breathable Athletic Club Polo'],
                    ['تيشيرت جري ليلي عاكس للضوء', 'Reflective Night Running T-Shirt'],
                    ['تيشيرت قطن ناعم للتمرين اليومي', 'Soft Stretch Everyday Workout Tee'],
                    ['تيشيرت شبكي للكارديو واللياقة', 'Mesh Panel Cardio Training Top'],
                    ['تيشيرت كم طويل دافئ للشتاء', 'Thermal Long-Sleeve Training Top'],
                    ['تيشيرت كروس فت مقاوم للتعرق', 'Heavy-Duty CrossFit Workout Tee'],
                ],
            ],
            'mens_track' => [
                'base_price' => 1120,
                'items' => [
                    ['بنطلون جوجرز رياضي سليم فيت', 'Tapered Slim-Fit Sport Joggers'],
                    ['تريننج تدريب بسوستة كاملة', 'Full-Zip Pro Training Tracksuit'],
                    ['شورت جري 7 بوصة ببطانة', '7-Inch Lined Road Running Shorts'],
                    ['بنطلون تدريب فليس شتوي', 'Brushed Fleece Winter Track Pants'],
                    ['شورت جيم خفيف بجيوب بسوستة', 'Zip-Pocket Lightweight Gym Shorts'],
                    ['بنطلون كارغو رياضي مرن', 'Stretch Athletic Cargo Pants'],
                    ['شورت باسكت بول واسع مريح', 'Mesh Breathable Basketball Shorts'],
                    ['بنطلون تسخين سريع الجفاف', 'Quick-Dry Track Warmup Pants'],
                ],
            ],
            'womens' => [
                'base_price' => 1550,
                'items' => [
                    ['طقم أكتيف وير نسائي قطعتين', 'Women 2-Piece Seamless Active Set'],
                    ['طقم جري نسائي خفيف الوزن', 'Women Ultralight Running Set'],
                    ['طقم تدريب حريمي دراي-فت', 'Women Dri-FIT Studio Training Set'],
                    ['طقم رياضي نسائي بسوستة أمامية', 'Women Front-Zip Sport Tracksuit'],
                    ['طقم تمارين منزلية مرن جداً', 'Women 4-Way Stretch Home Gym Set'],
                    ['طقم أكتيف وير صيفي بفتحات تهوية', 'Women Mesh-Vent Summer Fitness Kit'],
                    ['طقم تدريب نسائي ناعم الملمس', 'Women Cloud-Soft Workout Outfit'],
                    ['طقم رياضي أنيق للمشي السريع', 'Women Power-Walk Track Set'],
                ],
            ],
            'womens_leggings' => [
                'base_price' => 890,
                'items' => [
                    ['ليجنز عالي الخصر غير شفاف', 'High-Waist Squat-Proof Leggings'],
                    ['سبورتس برا بدعم متوسط', 'Mid-Impact Racerback Sports Bra'],
                    ['ليجنز بجيب جانبي للموبايل', 'Side-Pocket Sculpt Fitness Leggings'],
                    ['كروب توب تدريب نسائي مرن', 'Seamless Cropped Gym Training Top'],
                    ['شورت بايكر نسائي عالي الخصر', 'High-Rise Cycling Biker Shorts'],
                    ['توب رياضي بظهر متقاطع', 'Cross-Back Breathable Studio Tank'],
                    ['ليجنز كومبريشن للجري الطويل', 'Compression Pocket Running Leggings'],
                    ['سبورتس برا ببطانة قابلة للإزالة', 'Padded High-Support Gym Bra'],
                ],
            ],
            'womens_yoga' => [
                'base_price' => 1320,
                'items' => [
                    ['طقم يوجا سيملس ناعم 3 قطع', '3-Piece Seamless Yoga Studio Set'],
                    ['جمبسوت بيلاتس مرن قطعة واحدة', 'One-Piece Sculpt Pilates Jumpsuit'],
                    ['بنطلون يوجا واسع الأرجل مريح', 'Flared High-Waist Yoga Pants'],
                    ['توب بيلاتس بأكمام طويلة وفتحة إبهام', 'Thumbhole Long-Sleeve Pilates Top'],
                    ['طقم استوديو بيلاتس باستيل', 'Pastel Ribbed Studio Yoga Kit'],
                    ['بلوفر يوجا خفيف بعد التمرين', 'Relaxed Post-Workout Studio Wrap'],
                    ['شورت يوجا ناعم بدون خياطة', 'Seamless Soft Stretch Yoga Shorts'],
                    ['طقم يوجا قطني مسامي للصباح', 'Breathable Cotton Morning Yoga Set'],
                ],
            ],
            'shoes' => [
                'base_price' => 3650,
                'items' => [
                    ['سنيكرز تدريب شامل للرجال والنساء', 'All-Round Cross-Training Sneakers'],
                    ['حذاء جيم مسطح الكعب لرفع الأثقال', 'Flat-Heel Weightlifting Gym Shoes'],
                    ['سنيكرز لايف ستايل رياضي مريح', 'Everyday Cushion Sport Sneakers'],
                    ['حذاء تدريب كروس فت برباط ثابت', 'Lockdown Fit CrossFit Trainers'],
                    ['سنيكرز هوائي ببطانة مزدوجة', 'Dual-Air Cushion Training Shoes'],
                    ['حذاء رياضي خفيف للسفر والمشي', 'Ultralight Knit Travel Sneakers'],
                    ['حذاء تنس بأرضية مقاومة للتآكل', 'All-Court Durable Tennis Shoes'],
                    ['حذاء تدريب صالات داخلية مانع للانزلاق', 'Non-Marking Indoor Court Trainers'],
                ],
            ],
            'shoes_run' => [
                'base_price' => 4150,
                'items' => [
                    ['حذاء جري بيغاسوس برو فوم', 'Pegasus Pro React Running Shoes'],
                    ['حذاء ألترابوست لايت للماراثون', 'Ultraboost Light Marathon Runners'],
                    ['حذاء فريش فوم للمشي الطويل', 'Fresh Foam Distance Walking Shoes'],
                    ['حذاء نيترو السريع للجري اليومي', 'NITRO Speed Daily Road Trainers'],
                    ['حذاء جري شبكي فائق التهوية', 'Breeze Mesh Summer Running Sneakers'],
                    ['حذاء ترايل للجري في الطرق الوعرة', 'All-Terrain Grip Trail Runners'],
                    ['حذاء جري خفيف الوزن للسباقات', 'Carbon-Plate Race Day Runners'],
                    ['حذاء مشي طبي بوسادة قدم مريحة', 'CloudStride Cushioned Walking Shoes'],
                ],
            ],
            'shoes_football' => [
                'base_price' => 2950,
                'items' => [
                    ['حذاء كرة قدم ترتان نجيل صناعي', 'Synthe-Turf Precision Football Boots'],
                    ['ستارز كرة قدم ملاعب عشبية', 'Firm-Ground Speed Soccer Cleats'],
                    ['حذاء خماسي داخل الصالات فلات', 'Indoor Futsal Grip Gum-Sole Shoes'],
                    ['حذاء تحكم بالكرة بمقدمة محببة', 'Textured Strike Control Football Boots'],
                    ['ستارز خفيف الوزن للاعب الجناح', 'Featherlight Wing Attack Cleats'],
                    ['حذاء تدريب كرة قدم مبطن الكعب', 'Padded-Heel Football Training Turf'],
                    ['حذاء ملاعب رملية ونجيل مقاوم', 'Durable Multi-Surface Soccer Turf'],
                    ['ستارز كلاسيك جلد صناعي مرن', 'Classic Leather-Feel Football Boots'],
                ],
            ],
            'jerseys' => [
                'base_price' => 1150,
                'items' => [
                    ['طقم كرة قدم ماتش داي كامل', 'Pro Match-Day Football Kit Set'],
                    ['تيشيرت كرة قدم نسخة الاستاد', 'Stadium Edition Breathable Jersey'],
                    ['تيشيرت حارس مرمى بأكمام طويلة', 'Padded Goalkeeper Long-Sleeve Kit'],
                    ['شورت كرة قدم خفيف بتهوية جانبية', 'Side-Mesh Pro Football Shorts'],
                    ['طقم تدريب أندية بدون أكمام', 'Club Training Sleeveless Bib Set'],
                    ['تيشيرت منتخب كلاسيك ياقة دائرية', 'National Retro Crewneck Football Shirt'],
                    ['سويت شيرت تسخين قبل المباراة', 'Pre-Match Quarter-Zip Training Top'],
                    ['شراب كرة قدم طويل داعم للساق', 'Over-Calf Cushion Football Socks'],
                ],
            ],
            'outerwear' => [
                'base_price' => 1950,
                'items' => [
                    ['هودي تك فليس رياضي بسوستة', 'Tech Fleece Zip Athletic Hoodie'],
                    ['جاكيت جري مضاد للرياح والمطر', 'Water-Repellent Running Windbreaker'],
                    ['بلوفر كلوب فليس قطني دافئ', 'Club Fleece Pullover Sport Hoodie'],
                    ['سويت شيرت بنصف سوستة وياقة عالية', 'Half-Zip Stand-Collar Track Jacket'],
                    ['فيست تدريب شتوي مبطن خفيف', 'Insulated Padded Running Gilet'],
                    ['جاكيت تدريب تراك ثري سترايبس', 'Signature Track Warmup Jacket'],
                    ['هودي أوفرسايز للتسخين والشتاء', 'Oversized Heavyweight Gym Hoodie'],
                    ['جاكيت مطر خفيف قابل للطي للجري', 'Packable Storm-Proof Sport Jacket'],
                    ['كارديجان رياضي مبطن للتمرين الخارجي', 'Thermal Outdoor Active Track Coat'],
                ],
            ],
            'gear' => [
                'base_price' => 420,
                'items' => [
                    ['شنطة جيم دفل بجيب منفصل للأحذية', 'Ventilated Shoe-Pocket Gym Duffel'],
                    ['كاب جري مسامي خفيف الوزن', 'Laser-Perforated Runner Sports Cap'],
                    ['طقم 3 أزواج شرابات جيم مبطنة', '3-Pack Terry Cushioned Crew Socks'],
                    ['قفازات رفع أثقال جلدية برباط معصم', 'Leather-Palm Wrist-Wrap Gym Gloves'],
                    ['حقيبة ظهر رياضية مقاومة للماء 30 لتر', 'Water-Resistant 30L Sport Backpack'],
                    ['حزام خصر رياضي لحمل الموبايل أثناء الجري', 'Bounce-Free Phone Waist Running Belt'],
                    ['رباط رأس ومعصم رياضي ممتص للعرق', 'Moisture-Wicking Sweatband Kit'],
                    ['شنطة كتف رياضية خفيفة للتمرين السريع', 'Compact Crossbody Gym Sling Bag'],
                    ['زجاجة مياه رياضية معزولة 750 مل', 'Insulated 750ml Sport Water Bottle'],
                ],
            ],
        ];

        $totalProducts = 0;
        $totalVariants = 0;
        $totalAttributes = 0;
        $totalOffers = 0;
        $createdProducts = [];

        foreach ($categoryCatalog as $catKey => $group) {
            $categoryObj = $createdCategories[$catKey];
            $galleryPool = $galleryPools[$catKey];
            $isShoe = str_starts_with($catKey, 'shoes');
            $isGear = $catKey === 'gear';

            foreach ($group['items'] as $idx => $names) {
                $brandName = $brandNames[($totalProducts + $idx) % count($brandNames)];
                $brandAr = $createdBrands[$brandName]->name_ar;
                $brandObj = $createdBrands[$brandName];

                $product = Product::create([
                    'name_ar' => "{$brandAr} {$names[0]}",
                    'name_en' => "{$brandName} {$names[1]}",
                    'desc_ar' => "{$names[0]} من {$brandAr} مصمم بخامات رياضية عالية الجودة لراحة وأداء ممتاز أثناء التمرين والجري.",
                    'desc_en' => "{$brandName} {$names[1]} crafted with breathable athletic materials for peak comfort, durability, and movement.",
                    'brand_id' => $brandObj->id,
                    'category_id' => $categoryObj->id,
                ]);
                $createdProducts[] = $product;

                if (Schema::hasTable('category_product')) {
                    DB::table('category_product')->insert([
                        'product_id' => $product->id,
                        'category_id' => $categoryObj->id,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }

                // Make first 2 products in each category multi-variant (2 variants with distinct prices & options), others 1 variant
                $variantCount = $idx < 2 ? 2 : 1;

                for ($v = 0; $v < $variantCount; $v++) {
                    $mainImg = $galleryPool[($idx + $v) % count($galleryPool)];
                    // Rotate galleryPool so every variant gets 1 main_image + 3 gallery sub-images
                    $subImages = [];
                    for ($g = 0; $g < 3; $g++) {
                        $subImages[] = $galleryPool[($idx + $v + $g + 1) % count($galleryPool)];
                    }
                    $galleryImages = array_values(array_unique(array_merge([$mainImg], $subImages)));

                    $price = round($group['base_price'] + (($idx * 135) + ($v * 180)) % 950, 2);
                    if ($price < 320) {
                        $price += 200;
                    }

                    $property = Property::create([
                        'product_id' => $product->id,
                        'main_image' => $mainImg,
                        'images' => $galleryImages,
                        'stock' => rand(15, 95),
                        'price' => $price,
                    ]);
                    $totalVariants++;

                    // Give each variant 2 or 3 attributes (Color + Size + optional Material/Fit)
                    $color = $colorChoices[($totalProducts + $v) % count($colorChoices)];
                    $sizePool = $isShoe ? $shoeSizes : ($isGear ? $gearSizes : $apparelSizes);
                    $size = $sizePool[($totalProducts + $v) % count($sizePool)];

                    $attrsToCreate = [
                        ['key_en' => 'Color', 'key_ar' => 'اللون', 'value_en' => $color['en'], 'value_ar' => $color['ar']],
                        ['key_en' => 'Size', 'key_ar' => 'المقاس', 'value_en' => $size['en'], 'value_ar' => $size['ar']],
                    ];

                    // Add a 3rd attribute on alternating products / variants
                    if (($totalProducts + $v) % 2 === 0) {
                        $mat = $materials[($totalProducts + $v) % count($materials)];
                        $attrsToCreate[] = ['key_en' => 'Material', 'key_ar' => 'الخامة', 'value_en' => $mat['en'], 'value_ar' => $mat['ar']];
                    } elseif (! $isGear) {
                        $fit = $fits[$totalProducts % count($fits)];
                        $attrsToCreate[] = ['key_en' => 'Fit', 'key_ar' => 'القصة', 'value_en' => $fit['en'], 'value_ar' => $fit['ar']];
                    }

                    foreach ($attrsToCreate as $attr) {
                        Attribute::create([
                            'property_id' => $property->id,
                            'key_en' => $attr['key_en'],
                            'key_ar' => $attr['key_ar'],
                            'value_en' => $attr['value_en'],
                            'value_ar' => $attr['value_ar'],
                        ]);
                        $totalAttributes++;
                    }

                    // Active offers on ~35% of variants
                    if (($totalVariants % 3 === 1)) {
                        $discountPct = [10, 12, 15, 20, 25][($totalVariants) % 5];
                        $offerData = [
                            'property_id' => $property->id,
                            'start' => now()->subDays(10),
                            'end' => now()->addYears(2),
                        ];
                        if (Schema::hasColumn('offers', 'discount_price')) {
                            $offerData['discount_price'] = $discountPct;
                        }
                        if (Schema::hasColumn('offers', 'discount')) {
                            $offerData['discount'] = $discountPct;
                        }
                        Offer::create($offerData);
                        $totalOffers++;
                    }
                }

                $totalProducts++;
            }
        }

        // 7. SEED REVIEWER ACCOUNTS & CUSTOMER REVIEWS ACROSS ALL 100 PRODUCTS
        $this->command->info("\n--- Seeding Customer Accounts & Reviews ---");
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
            $reviewsForProduct = ($prodIdx % 3 === 0) ? 2 : 1;

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

        // 8. SEED GOVERNORATES, SHIPPING RATES, CONTACTS & CMS POLICY PAGES
        $this->call(GovernorateSeeder::class);
        $this->call(PageSeeder::class);

        $this->command->info("\n========================================================");
        $this->command->info("🎉 MOUSA STORE 100-PRODUCT SPORTSWEAR SEEDING COMPLETED!");
        $this->command->info("Products: {$totalProducts} | Variants: {$totalVariants} | Attributes: {$totalAttributes} | Offers: {$totalOffers} | Reviews: {$totalReviews}");
        $this->command->info("========================================================\n");
    }
}
