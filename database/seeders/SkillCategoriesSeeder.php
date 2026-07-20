<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SkillCategoriesSeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name_en' => 'Cook',               'name_ar' => 'طباخ',             'name_bn' => 'রাঁধুনি',             'icon' => 'heroicon-o-fire',              'sort_order' => 1],
            ['name_en' => 'Driver (Light)',      'name_ar' => 'سائق خفيف',        'name_bn' => 'ড্রাইভার (হালকা)',      'icon' => 'heroicon-o-truck',             'sort_order' => 2],
            ['name_en' => 'Driver (Heavy)',      'name_ar' => 'سائق ثقيل',        'name_bn' => 'ড্রাইভার (ভারী)',       'icon' => 'heroicon-o-truck',             'sort_order' => 3],
            ['name_en' => 'Mason',               'name_ar' => 'بناء',             'name_bn' => 'রাজমিস্ত্রি',          'icon' => 'heroicon-o-wrench',            'sort_order' => 4],
            ['name_en' => 'Electrician',         'name_ar' => 'كهربائي',          'name_bn' => 'ইলেকট্রিশিয়ান',       'icon' => 'heroicon-o-bolt',              'sort_order' => 5],
            ['name_en' => 'Plumber',             'name_ar' => 'سباك',             'name_bn' => 'প্লাম্বার',             'icon' => 'heroicon-o-wrench-screwdriver','sort_order' => 6],
            ['name_en' => 'Carpenter',           'name_ar' => 'نجار',             'name_bn' => 'কাঠমিস্ত্রি',          'icon' => 'heroicon-o-wrench',            'sort_order' => 7],
            ['name_en' => 'Painter',             'name_ar' => 'دهان',             'name_bn' => 'রঙ মিস্ত্রি',          'icon' => 'heroicon-o-paint-brush',       'sort_order' => 8],
            ['name_en' => 'Welder',              'name_ar' => 'لحام',             'name_bn' => 'ওয়েল্ডার',             'icon' => 'heroicon-o-fire',              'sort_order' => 9],
            ['name_en' => 'Security Guard',      'name_ar' => 'حارس أمن',         'name_bn' => 'নিরাপত্তা প্রহরী',     'icon' => 'heroicon-o-shield-check',      'sort_order' => 10],
            ['name_en' => 'Cleaner',             'name_ar' => 'عامل نظافة',       'name_bn' => 'ক্লিনার',              'icon' => 'heroicon-o-sparkles',          'sort_order' => 11],
            ['name_en' => 'Helper',              'name_ar' => 'مساعد',            'name_bn' => 'সাধারণ শ্রমিক',        'icon' => 'heroicon-o-hand-raised',       'sort_order' => 12],
            ['name_en' => 'Gardener',            'name_ar' => 'بستاني',           'name_bn' => 'মালী',                 'icon' => 'heroicon-o-sun',               'sort_order' => 13],
            ['name_en' => 'Farmer',              'name_ar' => 'مزارع',            'name_bn' => 'কৃষক',                 'icon' => 'heroicon-o-sun',               'sort_order' => 14],
            ['name_en' => 'Factory Worker',      'name_ar' => 'عامل مصنع',        'name_bn' => 'কারখানা শ্রমিক',       'icon' => 'heroicon-o-cog',               'sort_order' => 15],
            ['name_en' => 'Tailor',              'name_ar' => 'خياط',             'name_bn' => 'দর্জি',                'icon' => 'heroicon-o-scissors',          'sort_order' => 16],
            ['name_en' => 'Barber',              'name_ar' => 'حلاق',             'name_bn' => 'নাপিত',                'icon' => 'heroicon-o-scissors',          'sort_order' => 17],
            ['name_en' => 'AC Technician',       'name_ar' => 'فني تكييف',        'name_bn' => 'এসি টেকনিশিয়ান',      'icon' => 'heroicon-o-wrench-screwdriver','sort_order' => 18],
            ['name_en' => 'Mechanic',            'name_ar' => 'ميكانيكي',         'name_bn' => 'মেকানিক',              'icon' => 'heroicon-o-cog-6-tooth',       'sort_order' => 19],
            ['name_en' => 'Tiler',               'name_ar' => 'بلاط',             'name_bn' => 'টাইলস মিস্ত্রি',       'icon' => 'heroicon-o-squares-2x2',       'sort_order' => 20],
            ['name_en' => 'Steel Fixer',         'name_ar' => 'حداد',             'name_bn' => 'স্টিল ফিক্সার',        'icon' => 'heroicon-o-wrench',            'sort_order' => 21],
            ['name_en' => 'Scaffolder',          'name_ar' => 'سقالة',            'name_bn' => 'স্কাফোল্ডার',          'icon' => 'heroicon-o-wrench',            'sort_order' => 22],
            ['name_en' => 'Forklift Operator',   'name_ar' => 'مشغل رافعة شوكية', 'name_bn' => 'ফর্কলিফট অপারেটর',    'icon' => 'heroicon-o-arrow-up',          'sort_order' => 23],
            ['name_en' => 'Crane Operator',      'name_ar' => 'مشغل رافعة',       'name_bn' => 'ক্রেন অপারেটর',        'icon' => 'heroicon-o-arrow-up',          'sort_order' => 24],
            ['name_en' => 'Loader / Unloader',   'name_ar' => 'حمال',             'name_bn' => 'লোডার / আনলোডার',      'icon' => 'heroicon-o-archive-box',       'sort_order' => 25],
            ['name_en' => 'Packer',              'name_ar' => 'عامل تعبئة',       'name_bn' => 'প্যাকার',              'icon' => 'heroicon-o-archive-box-arrow-down', 'sort_order' => 26],
            ['name_en' => 'Salesman',            'name_ar' => 'بائع',             'name_bn' => 'সেলসম্যান',            'icon' => 'heroicon-o-shopping-bag',      'sort_order' => 27],
            ['name_en' => 'Receptionist',        'name_ar' => 'موظف استقبال',     'name_bn' => 'রিসেপশনিস্ট',         'icon' => 'heroicon-o-phone',             'sort_order' => 28],
            ['name_en' => 'Cashier',             'name_ar' => 'كاشير',            'name_bn' => 'ক্যাশিয়ার',            'icon' => 'heroicon-o-banknotes',         'sort_order' => 29],
            ['name_en' => 'Cook Helper',         'name_ar' => 'مساعد طباخ',       'name_bn' => 'রান্নার সহকারী',       'icon' => 'heroicon-o-fire',              'sort_order' => 30],
            ['name_en' => 'Pastry Chef',         'name_ar' => 'طاهي حلويات',      'name_bn' => 'পেস্ট্রি শেফ',         'icon' => 'heroicon-o-cake',              'sort_order' => 31],
            ['name_en' => 'Baker',               'name_ar' => 'خباز',             'name_bn' => 'বেকার',                'icon' => 'heroicon-o-cake',              'sort_order' => 32],
            ['name_en' => 'Butcher',             'name_ar' => 'جزار',             'name_bn' => 'কসাই',                 'icon' => 'heroicon-o-scissors',          'sort_order' => 33],
            ['name_en' => 'Laundry Worker',      'name_ar' => 'عامل مغسلة',       'name_bn' => 'লন্ড্রি কর্মী',        'icon' => 'heroicon-o-sparkles',          'sort_order' => 34],
            ['name_en' => 'Housemaid',           'name_ar' => 'عاملة منزلية',     'name_bn' => 'গৃহকর্মী',             'icon' => 'heroicon-o-home',              'sort_order' => 35],
            ['name_en' => 'Nanny',               'name_ar' => 'جليسة أطفال',      'name_bn' => 'আয়া / শিশু পরিচারিকা', 'icon' => 'heroicon-o-heart',             'sort_order' => 36],
            ['name_en' => 'Elder Caretaker',     'name_ar' => 'مقدم رعاية المسنين', 'name_bn' => 'বৃদ্ধ পরিচারক',     'icon' => 'heroicon-o-heart',             'sort_order' => 37],
            ['name_en' => 'Hospital Helper',     'name_ar' => 'مساعد مستشفى',     'name_bn' => 'হাসপাতাল সহকারী',     'icon' => 'heroicon-o-plus-circle',       'sort_order' => 38],
            ['name_en' => 'Office Boy',          'name_ar' => 'فراش مكتب',        'name_bn' => 'অফিস বয়',             'icon' => 'heroicon-o-building-office',   'sort_order' => 39],
            ['name_en' => 'Warehouse Worker',    'name_ar' => 'عامل مستودع',      'name_bn' => 'গুদামঘর কর্মী',        'icon' => 'heroicon-o-archive-box',       'sort_order' => 40],
            ['name_en' => 'Others',              'name_ar' => 'أخرى',             'name_bn' => 'অন্যান্য',             'icon' => 'heroicon-o-ellipsis-horizontal','sort_order' => 99],
        ];

        foreach ($categories as $cat) {
            DB::table('skill_categories')->updateOrInsert(
                ['name_en' => $cat['name_en']],
                array_merge($cat, ['is_active' => true])
            );
        }

        $this->command->info('✅ SkillCategories সিড হয়েছে: ' . count($categories) . 'টি ক্যাটাগরি');
    }
}