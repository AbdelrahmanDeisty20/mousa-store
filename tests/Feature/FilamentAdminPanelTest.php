<?php

namespace Tests\Feature;

use App\Events\OfferCreatedForCartItems;
use App\Events\OfferCreatedForFavorites;
use App\Filament\Pages\Auth\EditProfile;
use App\Filament\Resources\AttributeDefinitionResource;
use App\Filament\Resources\BannerResource;
use App\Filament\Resources\BrandResource;
use App\Filament\Resources\CategoryResource;
use App\Filament\Resources\ContactResource;
use App\Filament\Resources\OfferResource;
use App\Filament\Resources\OfferResource\Pages\CreateOffer;
use App\Filament\Resources\OrderResource;
use App\Filament\Resources\OrderResource\Pages\ViewOrder;
use App\Filament\Resources\PageResource\Pages\CreatePage;
use App\Filament\Resources\PageResource\Pages\EditPage;
use App\Filament\Resources\ProductResource;
use App\Filament\Resources\UserResource;
use App\Filament\Resources\UserResource\Pages\CreateUser;
use App\Mail\OtpMail;
use App\Models\Address;
use App\Models\AttributeDefinition;
use App\Models\Banner;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Contact;
use App\Models\Favorite;
use App\Models\Governorate;
use App\Models\Notification as UserNotification;
use App\Models\Offer;
use App\Models\Order;
use App\Models\Page;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ProductBanner;
use App\Models\Property;
use App\Models\Review;
use App\Models\Setting;
use App\Models\User;
use Database\Seeders\AdminRoleSeeder;
use Database\Seeders\GovernorateSeeder;
use Database\Seeders\PageSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class FilamentAdminPanelTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $subAdmin;

    private User $customer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(AdminRoleSeeder::class);
        $this->seed(GovernorateSeeder::class);
        $this->seed(PageSeeder::class);

        $this->admin = User::where('email', 'admin@admin.com')->firstOrFail();

        $this->subAdmin = User::create([
            'first_name' => 'Sub',
            'last_name' => 'Manager',
            'email' => 'subadmin@mousastore.com',
            'phone' => '01011111111',
            'user_type' => 'admin',
            'is_verfived' => true,
            'password' => '123456789',
        ]);
        $this->subAdmin->assignRole('subAdmin');

        $this->customer = User::create([
            'first_name' => 'Ahmed',
            'last_name' => 'Customer',
            'email' => 'customer@example.com',
            'phone' => '01022222222',
            'user_type' => 'user',
            'is_verfived' => true,
            'password' => '123456789',
        ]);
        $this->customer->assignRole('user');
    }

    public function test_guest_and_customer_cannot_access_filament_admin_panel(): void
    {
        $this->get('/admin')->assertRedirect('/admin/login');
        $loginResponse = $this->get('/admin/login');
        $loginResponse->assertOk();
        $loginResponse->assertSee('mainlogo.png', false);
        $loginResponse->assertSee('mainlogo-dark.png', false);
        $loginResponse->assertSee('#D9141E', false);
        $loginResponse->assertSee('Alexandria', false);
        $loginResponse->assertSee('SPORTSWEAR', false);

        $this->actingAs($this->customer)
            ->get('/admin')
            ->assertForbidden();
    }

    public function test_all_filament_pages_and_resources_render_for_admin(): void
    {
        $gov = Governorate::firstOrFail();
        $category = Category::create(['name_ar' => 'تيشيرتات', 'name_en' => 'T-Shirts', 'image' => 'cat.jpg', 'sort_order' => 1]);
        $brand = Brand::create(['name_ar' => 'نايكي', 'name_en' => 'Nike', 'image' => 'brand.jpg']);
        $attrDef = AttributeDefinition::create(['name_ar' => 'المقاس', 'name_en' => 'Size']);
        $attrDef->values()->create(['value_ar' => 'كبير', 'value_en' => 'L']);

        $product = Product::create([
            'name_ar' => 'تيشيرت رياضي',
            'name_en' => 'Sports Tee',
            'desc_ar' => 'وصف عربي',
            'desc_en' => 'English description',
            'category_id' => $category->id,
            'brand_id' => $brand->id,
        ]);

        $variant = Property::create([
            'product_id' => $product->id,
            'price' => 450,
            'stock' => 20,
            'main_image' => 'main.jpg',
            'images' => ['g1.jpg'],
        ]);

        $offer = Offer::create([
            'property_id' => $variant->id,
            'discount_price' => 390,
            'start' => now()->addDay(),
            'end' => now()->addDays(5),
        ]);

        $order = Order::create([
            'order_number' => 'ORD-TEST-100',
            'user_id' => $this->customer->id,
            'total_price' => 490,
            'status' => 'pending',
            'user_name' => 'Ahmed Customer',
            'user_phone' => '01022222222',
            'user_address' => 'Cairo St',
            'governorate_id' => $gov->id,
        ]);
        $order->items()->attach($variant->id, ['quantity' => 1, 'price' => 450]);

        Payment::create([
            'order_id' => $order->id,
            'method' => 'cash',
            'amount' => 490,
            'status' => 'pending',
            'transaction_id' => 'TXN-100',
        ]);

        Address::create([
            'user_id' => $this->customer->id,
            'name_address' => 'Home',
            'city' => 'Cairo',
            'phone' => '01022222222',
            'address' => '12 Nile St',
        ]);

        Review::create([
            'user_id' => $this->customer->id,
            'product_id' => $product->id,
            'rate' => 5,
            'comment' => 'Great fit',
        ]);

        Favorite::create([
            'user_id' => $this->customer->id,
            'product_id' => $product->id,
            'is_favorite' => true,
        ]);

        $banner = Banner::create([
            'title_ar' => 'عرض خاص',
            'title_en' => 'Special Banner',
            'short_desc_ar' => 'وصف البانر',
            'short_desc_en' => 'Banner desc',
            'image' => 'banner.jpg',
        ]);

        $productBanner = ProductBanner::create([
            'title_ar' => 'إعلان مميز',
            'title_en' => 'Featured Ad',
            'desc_ar' => 'تفاصيل',
            'desc_en' => 'Details',
            'price' => 599,
            'image' => 'ad.jpg',
        ]);

        $setting = Setting::create([
            'governorate_id' => $gov->id,
            'shipping' => 60,
        ]);

        $contact = Contact::firstOrCreate(
            ['email' => 'support@mousastore.com'],
            [
                'phone' => '01000000000',
                'whatsapp' => '01000000000',
                'facebook' => 'https://facebook.com/mousastore',
                'address_ar' => 'القاهرة، مصر',
                'address_en' => 'Cairo, Egypt',
            ]
        );

        UserNotification::create([
            'user_id' => $this->admin->id,
            'title' => 'طلب جديد',
            'body' => 'تم إنشاء طلب تجريبي',
            'type' => 'order',
            'data' => ['order_id' => $order->id],
            'is_read' => false,
        ]);

        $page = Page::firstOrFail();

        $urls = [
            '/admin',
            '/admin/profile',
            '/admin/categories',
            '/admin/categories/create',
            "/admin/categories/{$category->id}/edit",
            '/admin/brands',
            '/admin/brands/create',
            "/admin/brands/{$brand->id}/edit",
            '/admin/attribute-definitions',
            '/admin/attribute-definitions/create',
            "/admin/attribute-definitions/{$attrDef->id}/edit",
            '/admin/products',
            '/admin/products/create',
            "/admin/products/{$product->id}",
            "/admin/products/{$product->id}/edit",
            '/admin/offers',
            '/admin/offers/create',
            "/admin/offers/{$offer->id}/edit",
            '/admin/orders',
            "/admin/orders/{$order->id}",
            '/admin/users',
            '/admin/users/create',
            "/admin/users/{$this->customer->id}/edit",
            '/admin/payments',
            '/admin/addresses',
            '/admin/reviews',
            '/admin/favorites',
            '/admin/banners',
            '/admin/banners/create',
            "/admin/banners/{$banner->id}/edit",
            '/admin/product-banners',
            '/admin/product-banners/create',
            "/admin/product-banners/{$productBanner->id}/edit",
            '/admin/pages',
            '/admin/pages/create',
            "/admin/pages/{$page->id}/edit",
            '/admin/settings',
            '/admin/settings/create',
            "/admin/settings/{$setting->id}/edit",
            '/admin/contacts',
            "/admin/contacts/{$contact->id}/edit",
            '/admin/notifications',
        ];

        foreach ($urls as $url) {
            $response = $this->actingAs($this->admin)->get($url);
            $response->assertOk();
        }
    }

    public function test_order_workflow_actions_and_stock_restoration(): void
    {
        $gov = Governorate::firstOrFail();
        $category = Category::create(['name_ar' => 'أحذية', 'name_en' => 'Shoes', 'image' => 'c.jpg', 'sort_order' => 1]);
        $brand = Brand::create(['name_ar' => 'أديداس', 'name_en' => 'Adidas', 'image' => 'b.jpg']);
        $product = Product::create([
            'name_ar' => 'حذاء جري',
            'name_en' => 'Running Shoe',
            'desc_ar' => 'وصف',
            'desc_en' => 'Desc',
            'category_id' => $category->id,
            'brand_id' => $brand->id,
        ]);
        $variant = Property::create([
            'product_id' => $product->id,
            'price' => 1200,
            'stock' => 5,
            'main_image' => 's.jpg',
        ]);

        $order = Order::create([
            'order_number' => 'ORD-FLOW-1',
            'user_id' => $this->customer->id,
            'total_price' => 1200,
            'status' => 'pending',
            'user_name' => 'Ahmed',
            'user_phone' => '01022222222',
            'user_address' => 'Giza',
            'governorate_id' => $gov->id,
        ]);
        $order->items()->attach($variant->id, ['quantity' => 2, 'price' => 1200]);

        $this->actingAs($this->admin);

        Livewire::test(ViewOrder::class, ['record' => $order->getRouteKey()])
            ->callAction('process');

        $this->assertSame('processing', $order->fresh()->status);
        $this->assertDatabaseHas('notifications', [
            'user_id' => $this->customer->id,
            'type' => 'order_status',
        ]);

        Livewire::test(ViewOrder::class, ['record' => $order->getRouteKey()])
            ->callAction('cancel', data: ['notice' => 'Out of stock in branch']);

        $this->assertSame('cancelled', $order->fresh()->status);
        $this->assertSame('Out of stock in branch', $order->fresh()->notice);
        $this->assertSame(7, $variant->fresh()->stock);

        $order2 = Order::create([
            'order_number' => 'ORD-FLOW-2',
            'user_id' => $this->customer->id,
            'total_price' => 1200,
            'status' => 'processing',
            'user_name' => 'Ahmed',
            'user_phone' => '01022222222',
            'user_address' => 'Giza',
            'governorate_id' => $gov->id,
        ]);

        Livewire::test(ViewOrder::class, ['record' => $order2->getRouteKey()])
            ->callAction('complete');

        $this->assertSame('completed', $order2->fresh()->status);
    }

    public function test_offer_creation_dispatches_cart_and_favorite_events(): void
    {
        Event::fake([OfferCreatedForCartItems::class, OfferCreatedForFavorites::class]);

        $category = Category::create(['name_ar' => 'ملابس', 'name_en' => 'Apparel', 'image' => 'c.jpg', 'sort_order' => 1]);
        $brand = Brand::create(['name_ar' => 'بوما', 'name_en' => 'Puma', 'image' => 'b.jpg']);
        $product = Product::create([
            'name_ar' => 'هودي',
            'name_en' => 'Hoodie',
            'desc_ar' => 'وصف',
            'desc_en' => 'Desc',
            'category_id' => $category->id,
            'brand_id' => $brand->id,
        ]);
        $variant = Property::create([
            'product_id' => $product->id,
            'price' => 800,
            'stock' => 10,
            'main_image' => 'h.jpg',
        ]);

        $this->actingAs($this->admin);

        Livewire::test(CreateOffer::class)
            ->fillForm([
                'property_id' => $variant->id,
                'discount_price' => 25,
                'start' => now()->addHour()->format('Y-m-d H:i:s'),
                'end' => now()->addDays(2)->format('Y-m-d H:i:s'),
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('offers', [
            'property_id' => $variant->id,
            'discount_price' => 25,
            'discount' => 25,
        ]);

        Event::assertDispatched(OfferCreatedForCartItems::class);
        Event::assertDispatched(OfferCreatedForFavorites::class);
    }

    public function test_policy_page_cms_creation_and_api_endpoint_contract(): void
    {
        $this->actingAs($this->admin);

        Livewire::test(CreatePage::class)
            ->fillForm([
                'slug' => 'warranty-policy',
                'is_active' => true,
                'title_ar' => 'سياسة الضمان',
                'title_en' => 'Warranty Policy',
                'subtitle_ar' => 'تفاصيل الضمان للمنتجات الرياضية',
                'subtitle_en' => 'Sports gear warranty coverage',
                'sections_ar' => [
                    [
                        'heading' => 'مدة الضمان',
                        'body' => 'نضمن جودة التصنيع.',
                        'points' => [
                            ['point' => 'ضمان 30 يوماً'],
                            ['point' => 'يشمل عيوب الصناعة'],
                        ],
                    ],
                ],
                'sections_en' => [
                    [
                        'heading' => 'Coverage Duration',
                        'body' => 'Manufacturing quality guaranteed.',
                        'points' => [
                            ['point' => '30-day warranty'],
                            ['point' => 'Covers defects'],
                        ],
                    ],
                ],
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->getJson('/api/v1/pages/warranty-policy', ['Accept-Language' => 'ar'])
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.slug', 'warranty-policy')
            ->assertJsonPath('data.title', 'سياسة الضمان')
            ->assertJsonPath('data.sections.0.points.0', 'ضمان 30 يوماً');

        $existingPage = Page::where('slug', 'return-policy')->firstOrFail();

        Livewire::test(EditPage::class, ['record' => $existingPage->getRouteKey()])
            ->set('data.title_en', 'Updated Return Policy')
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame('Updated Return Policy', $existingPage->fresh()->title_en);
    }

    public function test_user_resource_creates_sub_admin_with_single_hashed_password(): void
    {
        $this->actingAs($this->admin);

        Livewire::test(CreateUser::class)
            ->fillForm([
                'first_name' => 'Staff',
                'last_name' => 'Member',
                'email' => 'staff@mousastore.com',
                'phone' => '01033333333',
                'password' => 'StaffPass123',
                'role_name' => 'subAdmin',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $created = User::where('email', 'staff@mousastore.com')->firstOrFail();
        $this->assertSame('admin', $created->user_type);
        $this->assertTrue($created->hasRole('subAdmin'));
        $this->assertTrue(Hash::check('StaffPass123', $created->password));
    }

    public function test_sub_admin_restrictions_profile_password_update_and_emails_preserved(): void
    {
        $this->actingAs($this->subAdmin);

        $this->assertFalse(UserResource::canDelete($this->customer));
        $this->assertFalse(UserResource::canEdit($this->admin));
        $this->assertFalse(OrderResource::canDelete(new Order));
        $this->assertFalse(ContactResource::canEdit(new Contact));
        $this->assertTrue(ProductResource::canViewAny());
        $this->assertTrue(CategoryResource::canViewAny());
        $this->assertTrue(BrandResource::canViewAny());
        $this->assertTrue(AttributeDefinitionResource::canViewAny());
        $this->assertTrue(OfferResource::canViewAny());
        $this->assertTrue(BannerResource::canViewAny());

        $this->actingAs($this->admin);

        Livewire::test(EditProfile::class)
            ->fillForm([
                'first_name' => 'Super',
                'last_name' => 'Admin',
                'phone' => '01099999999',
                'email' => $this->admin->email,
                'password' => 'NewSecret123!',
                'passwordConfirmation' => 'NewSecret123!',
                'currentPassword' => '123456789',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertTrue(Hash::check('NewSecret123!', $this->admin->fresh()->password));

        $this->get('/admin/switch-lang/en')->assertRedirect();
        $this->assertSame('en', session('lang'));

        $this->get('/api/v1/products')->assertOk();

        $mailHtml = (new OtpMail('123456', 'register', 10, 'Mousa'))->render();
        $this->assertStringContainsString('123456', $mailHtml);
    }
}
