<?php

namespace Database\Seeders;

use App\Models\Page;
use Illuminate\Database\Seeder;

class PageSeeder extends Seeder
{
    /**
     * Seed the 5 standard policy & consumer-protection CMS pages in Arabic and English.
     */
    public function run(): void
    {
        $pages = [
            [
                'slug'        => 'privacy-policy',
                'title_ar'    => 'سياسة الخصوصية',
                'title_en'    => 'Privacy Policy',
                'subtitle_ar' => 'كيف نقوم بجمع وحماية واستخدام بياناتك الشخصية في متجر موسى ستور.',
                'subtitle_en' => 'How Mousa Store collects, protects, and uses your personal information.',
                'sections_ar' => [
                    [
                        'heading' => 'البيانات التي نقوم بجمعها',
                        'body'    => 'نجمع فقط البيانات الضرورية لإتمام طلباتك وتحسين تجربتك في التسوق عبر متجر موسى ستور.',
                        'points'  => [
                            'الاسم الكامل، البريد الإلكتروني، ورقم الهاتف للتواصل وتأكيد الطلبات.',
                            'عنوان التوصيل والمحافظة لحساب تكلفة الشحن وتسليم الطلبات بدقة.',
                            'تفضيلات المنتجات وسجل الطلبات لتقديم خدمة عملاء أسرع.',
                        ],
                    ],
                    [
                        'heading' => 'حماية البيانات ومشاركتها',
                        'body'    => 'نلتزم بحماية بيانات عملائنا وعدم بيعها أو تأجيرها لأي طرف ثالث، وتتم مشاركتها فقط مع الجهات اللازمة لتنفيذ الخدمة.',
                        'points'  => [
                            'شركات الشحن المعتمدة لتسليم طلبك إلى باب المنزل.',
                            'بوابات الدفع الإلكتروني المؤمنة لإتمام المعاملات المالية.',
                            'نستخدم تشفيراً آمناً لحماية كلمات المرور وبيانات الحساب.',
                        ],
                    ],
                ],
                'sections_en' => [
                    [
                        'heading' => 'Information We Collect',
                        'body'    => 'We only collect the information required to process your orders and improve your shopping experience on Mousa Store.',
                        'points'  => [
                            'Full name, email address, and phone number for account verification and order updates.',
                            'Shipping address and governorate to calculate delivery fees and deliver orders accurately.',
                            'Order history and wishlist items to provide faster customer support.',
                        ],
                    ],
                    [
                        'heading' => 'Data Security & Sharing',
                        'body'    => 'We never sell or rent your personal data to third parties. Data is shared strictly with trusted partners required to fulfill your order.',
                        'points'  => [
                            'Authorized courier partners to deliver your packages.',
                            'Encrypted payment service providers when processing online payments.',
                            'All passwords and verification tokens are securely hashed.',
                        ],
                    ],
                ],
                'is_active' => true,
            ],
            [
                'slug'        => 'return-policy',
                'title_ar'    => 'سياسة الاستبدال والاسترجاع',
                'title_en'    => 'Return & Exchange Policy',
                'subtitle_ar' => 'شروط وإجراءات استبدال أو استرجاع الملابس والأحذية الرياضية في موسى ستور.',
                'subtitle_en' => 'Terms and steps for returning or exchanging sportswear and footwear at Mousa Store.',
                'sections_ar' => [
                    [
                        'heading' => 'المدة القانونية للاستبدال والاسترجاع',
                        'body'    => 'وفقاً لقانون حماية المستهلك المصري، يحق لك طلب استبدال أو استرجاع المنتجات خلال الفترات المحددة دون تعقيد.',
                        'points'  => [
                            'خلال 14 يوماً من تاريخ الاستلام لاستبدال المقاس أو استرجاع المنتج بحالته الأصلية.',
                            'خلال 30 يوماً في حالة وجود أي عيب صناعة أو عدم مطابقة للمواصفات المعلنة.',
                        ],
                    ],
                    [
                        'heading' => 'شروط قبول الاسترجاع أو الاستبدال',
                        'body'    => 'لضمان الحفاظ على جودة المنتجات للجميع، يُشترط توافر الشروط التالية عند طلب الإرجاع:',
                        'points'  => [
                            'أن يكون المنتج غير مستخدم، غير مغسول، وبجميع التيكت والملصقات الأصلية والتغليف الأصلي.',
                            'تقديم فاتورة الشراء أو رقم الطلب المسجل على الحساب.',
                            'الجوارب والملابس الداخلية وملابس السباحة لا تُستبدل أو تُسترجع لأسباب صحية إلا في حالة وجود عيب صناعة.',
                        ],
                    ],
                    [
                        'heading' => 'استرداد المبلغ',
                        'body'    => 'يتم فحص المنتج فور استلامه وإتمام عملية الاسترداد بنفس وسيلة الدفع المستخدمة.',
                        'points'  => [
                            'يتم رد المبلغ خلال 3 إلى 7 أيام عمل بعد استلام المرتجع ومراجعته.',
                            'يتحمل المتجر مصاريف الشحن بالكامل إذا كان السبب عيب صناعة أو خطأ في التجهيز.',
                        ],
                    ],
                ],
                'sections_en' => [
                    [
                        'heading' => 'Legal Return & Exchange Window',
                        'body'    => 'In accordance with Egyptian Consumer Protection Law, you may request an exchange or return within the statutory periods.',
                        'points'  => [
                            'Within 14 days of delivery for size exchanges or change of mind in original condition.',
                            'Within 30 days if the item has a manufacturing defect or does not match the product description.',
                        ],
                    ],
                    [
                        'heading' => 'Eligibility Conditions',
                        'body'    => 'To keep every customer protected, returned items must meet the following criteria:',
                        'points'  => [
                            'Items must be unworn, unwashed, with all original tags and packaging intact.',
                            'Order number or proof of purchase must be provided.',
                            'For hygiene reasons, socks, innerwear, and swimwear cannot be returned unless defective.',
                        ],
                    ],
                    [
                        'heading' => 'Refund Processing',
                        'body'    => 'Once the returned item is inspected, refunds are issued promptly using your original payment method.',
                        'points'  => [
                            'Refunds are processed within 3 to 7 business days after inspection.',
                            'Mousa Store covers return shipping costs in full for defective or incorrectly shipped items.',
                        ],
                    ],
                ],
                'is_active' => true,
            ],
            [
                'slug'        => 'terms-and-conditions',
                'title_ar'    => 'الشروط والأحكام',
                'title_en'    => 'Terms & Conditions',
                'subtitle_ar' => 'القواعد المنظمة لاستخدام متجر موسى ستور وإتمام عمليات الشراء.',
                'subtitle_en' => 'Guidelines governing your use of Mousa Store and online purchases.',
                'sections_ar' => [
                    [
                        'heading' => 'استخدام الحساب والطلبات',
                        'body'    => 'باستخدامك لموقع موسى ستور فإنك توافق على تقديم بيانات صحيحة ودقيقة عند إنشاء الحساب وإتمام الطلب.',
                        'points'  => [
                            'يلتزم العميل بإدخال رقم هاتف وعنوان صحيح لتسهيل عملية التوصيل.',
                            'يحتفظ المتجر بحق إلغاء أي طلب يحتوي على خطأ واضح في السعر أو نفاد مفاجئ في المخزون مع إخطار العميل فوراً.',
                        ],
                    ],
                    [
                        'heading' => 'الأسعار والمدفوعات',
                        'body'    => 'جميع الأسعار معروضة بالجنيه المصري وتشمل ضريبة القيمة المضافة إن وجدت، وتُضاف تكلفة الشحن حسب المحافظة عند إتمام الطلب.',
                        'points'  => [
                            'نوفر خيارات الدفع عند الاستلام أو عبر وسائل الدفع الإلكتروني المتاحة.',
                            'جميع الصور المعروضة تعبّر عن المنتجات الفعلية مع مراعاة اختلافات الإضاءة البسيطة.',
                        ],
                    ],
                ],
                'sections_en' => [
                    [
                        'heading' => 'Account Usage & Orders',
                        'body'    => 'By accessing Mousa Store and placing orders, you agree to provide accurate account and delivery details.',
                        'points'  => [
                            'Customers must provide a valid Egyptian phone number and detailed delivery address.',
                            'We reserve the right to cancel orders caused by sudden stock depletion or manifest pricing errors, with immediate customer notification.',
                        ],
                    ],
                    [
                        'heading' => 'Pricing & Payments',
                        'body'    => 'All listed prices are in Egyptian Pounds (EGP), with governorate shipping fees transparently displayed prior to checkout confirmation.',
                        'points'  => [
                            'Supported payment options include Cash on Delivery and electronic payment gateways.',
                            'Product images closely represent actual items, subject to minor screen lighting variations.',
                        ],
                    ],
                ],
                'is_active' => true,
            ],
            [
                'slug'        => 'shipping-policy',
                'title_ar'    => 'سياسة الشحن والتوصيل',
                'title_en'    => 'Shipping & Delivery Policy',
                'subtitle_ar' => 'مواعيد التوصيل وتكاليف الشحن لجميع محافظات جمهورية مصر العربية.',
                'subtitle_en' => 'Estimated delivery timelines and shipping fees across all Egyptian governorates.',
                'sections_ar' => [
                    [
                        'heading' => 'مدة تجهيز وتوصيل الطلبات',
                        'body'    => 'نحرص في موسى ستور على تجهيز وشحن جميع طلبات الملابس والأحذية الرياضية في أسرع وقت ممكن.',
                        'points'  => [
                            'القاهرة الكبرى والجيزة: التوصيل خلال 1 إلى 3 أيام عمل.',
                            'الإسكندرية والدلتا والقناة: التوصيل خلال 2 إلى 4 أيام عمل.',
                            'محافظات الصعيد والبحر الأحمر وسيناء: التوصيل خلال 3 إلى 6 أيام عمل.',
                        ],
                    ],
                    [
                        'heading' => 'تكلفة الشحن ومعاينة الطلب',
                        'body'    => 'تظهر تكلفة الشحن بوضوح حسب المحافظة المختارة قبل تأكيد الطلب النهائي.',
                        'points'  => [
                            'تبدأ أسعار التوصيل من 50 جنيهاً للقاهرة والجيزة وتختلف حسب المحافظة.',
                            'يتم التواصل مع العميل هاتفياً قبل وصول المندوب لتأكيد ميعاد التسليم.',
                        ],
                    ],
                ],
                'sections_en' => [
                    [
                        'heading' => 'Order Processing & Delivery Timeframes',
                        'body'    => 'Mousa Store prepares and dispatches all sportswear orders quickly through our nationwide courier network.',
                        'points'  => [
                            'Greater Cairo & Giza: 1 to 3 business days.',
                            'Alexandria, Delta & Canal cities: 2 to 4 business days.',
                            'Upper Egypt, Red Sea & Sinai: 3 to 6 business days.',
                        ],
                    ],
                    [
                        'heading' => 'Shipping Rates & Delivery Inspection',
                        'body'    => 'Shipping charges are transparently calculated based on your selected governorate before checkout.',
                        'points'  => [
                            'Rates start from 50 EGP for Cairo and Giza depending on your governorate.',
                            'Our courier contacts you by phone prior to delivery arrival.',
                        ],
                    ],
                ],
                'is_active' => true,
            ],
            [
                'slug'        => 'consumer-protection',
                'title_ar'    => 'حماية المستهلك',
                'title_en'    => 'Consumer Protection',
                'subtitle_ar' => 'التزام متجر موسى ستور بحقوق المستهلك وفقاً للقانون رقم 181 لسنة 2018.',
                'subtitle_en' => 'Mousa Store commitment to customer rights under Egyptian Consumer Protection Law No. 181 of 2018.',
                'sections_ar' => [
                    [
                        'heading' => 'حقوقك كمستهلك في موسى ستور',
                        'body'    => 'نلتزم بكافة معايير الشفافية وحقوق المستهلك المنصوص عليها في قانون حماية المستهلك المصري رقم 181 لسنة 2018.',
                        'points'  => [
                            'وضوح الأسعار النهائية وتكاليف الشحن والضرائب قبل إتمام الشراء.',
                            'الحق في استلام فاتورة واضحة تتضمن تفاصيل المنتجات والأسعار.',
                            'توفير بيانات واضحة لخامات ومقاسات وألوان الملابس الرياضية قبل الطلب.',
                        ],
                    ],
                    [
                        'heading' => 'الشكاوى والتواصل مع جهاز حماية المستهلك',
                        'body'    => 'فريق خدمة عملاء موسى ستور متاح لحل أي شكوى فوراً، كما يمكنك التواصل مع جهاز حماية المستهلك في أي وقت.',
                        'points'  => [
                            'تواصل معنا مباشرة عبر صفحة اتصل بنا أو البريد الإلكتروني الرسمي للمتجر.',
                            'الخط الساخن لجهاز حماية المستهلك المصري: 19588.',
                        ],
                    ],
                ],
                'sections_en' => [
                    [
                        'heading' => 'Your Consumer Rights at Mousa Store',
                        'body'    => 'We strictly adhere to all transparency and consumer safeguards under Egyptian Consumer Protection Law No. 181 of 2018.',
                        'points'  => [
                            'Full price clarity including product prices, applicable taxes, and governorate shipping before checkout.',
                            'Right to an accurate order summary and invoice detailing purchased items.',
                            'Clear product sizing, color, material specifications, and authentic warranty details.',
                        ],
                    ],
                    [
                        'heading' => 'Complaints & Consumer Protection Hotline',
                        'body'    => 'Our customer support team resolves any dispute promptly. Customers also retain full access to official regulatory channels.',
                        'points'  => [
                            'Reach Mousa Store support directly via our Contacts page or official support email.',
                            'Egyptian Consumer Protection Agency (CPA) Hotline: 19588.',
                        ],
                    ],
                ],
                'is_active' => true,
            ],
        ];

        foreach ($pages as $pageData) {
            Page::firstOrCreate(
                ['slug' => $pageData['slug']],
                $pageData
            );
        }
    }
}
