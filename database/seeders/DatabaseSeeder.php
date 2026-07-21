<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Classification;
use App\Models\Plan;
use App\Models\Question;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@swalif.test'],
            ['name' => 'مدير سوالف', 'password' => Hash::make('password'), 'is_admin' => true]
        );

        User::updateOrCreate(
            ['email' => 'player@swalif.test'],
            ['name' => 'لاعب تجريبي', 'password' => Hash::make('password'), 'is_admin' => false]
        );

        $categories = [
            ['مولات الإمارات', 'UAE Malls', '🏬', 'uae'],
            ['معالم الإمارات', 'UAE Landmarks', '🕌', 'uae'],
            ['مطاعم الإمارات', 'UAE Restaurants', '🍽️', 'uae'],
            ['التمور الإماراتية', 'UAE Dates', '🌴', 'uae'],
            ['أمثال إماراتية', 'UAE Proverbs', '💬', 'uae'],
            ['أكمل الآية', 'Complete the Verse', '📖', 'general'],
            ['كرة القدم', 'Football', '⚽', 'general'],
            ['عالم الحيوان', 'Animals', '🦁', 'general'],
            ['أعلام الدول', 'Flags', '🚩', 'general'],
            ['ألغاز', 'Riddles', '🧩', 'general'],
        ];

        $uaeClassification = Classification::firstOrCreate(
            ['name_en' => 'UAE'],
            [
                'name_ar' => 'إمارات',
                'slug' => 'uae-'.Str::random(4),
                'icon' => '🇦🇪',
                'description' => 'تصنيف خاص بالمحتوى الإماراتي.',
                'is_active' => true,
                'sort_order' => 1,
            ]
        );
        $uaeClassification->fill([
            'name_ar' => 'إمارات',
            'icon' => '🇦🇪',
            'is_active' => true,
            'sort_order' => 1,
        ])->save();

        $generalClassification = Classification::firstOrCreate(
            ['name_en' => 'General'],
            [
                'name_ar' => 'عامة',
                'slug' => 'general-'.Str::random(4),
                'icon' => '🌍',
                'description' => 'تصنيف للمحتوى العام والمتنوع.',
                'is_active' => true,
                'sort_order' => 2,
            ]
        );
        $generalClassification->fill([
            'name_ar' => 'عامة',
            'icon' => '🌍',
            'is_active' => true,
            'sort_order' => 2,
        ])->save();

        $classificationMap = [
            'uae' => $uaeClassification,
            'general' => $generalClassification,
        ];

        foreach ($categories as $index => $item) {
            $classification = $classificationMap[$item[3]];

            Category::updateOrCreate(
                ['slug' => Str::slug($item[1])],
                [
                    'name_ar' => $item[0],
                    'name_en' => $item[1],
                    'icon' => $item[2],
                    'group' => $classification->name_ar,
                    'classification_id' => $classification->id,
                    'description' => 'أسئلة ممتعة تناسب العائلة والأصدقاء.',
                    'is_active' => true,
                    'sort_order' => $index + 1,
                ]
            );
        }

        $questionBank = [
            'uae-malls' => [
                ['easy', 'أين يقع مول الإمارات؟', 'دبي', 'أبوظبي', 'الشارقة'],
                ['easy', 'أي مول يحتوي على حديقة ثلج داخلية؟', 'مول الإمارات', 'دبي مول', 'ياس مول'],
                ['easy', 'في أي إمارة يقع ياس مول؟', 'أبوظبي', 'دبي', 'عجمان'],
                ['medium', 'ما اسم أكبر مول في دبي من حيث المساحة؟', 'دبي مول', 'ابن بطوطة', 'مردف سيتي سنتر'],
                ['medium', 'أي مول يضم نافورة دبي القريبة منه؟', 'دبي مول', 'مول العرب', 'مدينة زايد للتسوق'],
                ['medium', 'في أي مدينة يقع سيتي سنتر مرسى دبي؟', 'دبي', 'العين', 'الفجيرة'],
                ['hard', 'متى افتُتح دبي مول تقريباً؟', '2008', '1999', '2015'],
                ['hard', 'أي مول يشتهر بممر ابن بطوطة الموضوعي؟', 'ابن بطوطة مول', 'ديرة سيتي سنتر', 'مينا مول'],
                ['hard', 'أين يقع مول الوصل؟', 'دبي', 'رأس الخيمة', 'أم القيوين'],
            ],
            'uae-landmarks' => [
                ['easy', 'ما هي عاصمة الإمارات؟', 'أبوظبي', 'دبي', 'الشارقة'],
                ['easy', 'في أي مدينة يقع برج خليفة؟', 'دبي', 'أبوظبي', 'العين'],
                ['easy', 'ما المعلم الشهير ذي الشكل الشراعي في دبي؟', 'برج العرب', 'متحف المستقبل', 'قصر الحصن'],
                ['medium', 'أين يقع مسجد الشيخ زايد الكبير؟', 'أبوظبي', 'دبي', 'عجمان'],
                ['medium', 'ما اسم المتحف الكروي الشهير في دبي؟', 'متحف المستقبل', 'متحف اللوفر', 'قصر الإمارات'],
                ['medium', 'أي معلم يقع على جزيرة السعديات؟', 'متحف اللوفر أبوظبي', 'دبي فريم', 'القرية العالمية'],
                ['hard', 'في أي عام افتُتح برج خليفة؟', '2010', '2005', '2018'],
                ['hard', 'أين يقع قصر الحصن؟', 'أبوظبي', 'الشارقة', 'الفجيرة'],
                ['hard', 'ما الجبل الأشهر قرب رأس الخيمة؟', 'جبل جيس', 'جبل حفيت', 'جبل علي'],
            ],
            'football' => [
                ['easy', 'كم لاعباً أساسياً في فريق كرة القدم؟', '11', '7', '9'],
                ['easy', 'ما لون بطاقة الإنذار؟', 'أصفر', 'أحمر', 'أزرق'],
                ['easy', 'كم دقيقة تقريباً مدة الشوط الواحد؟', '45', '30', '60'],
                ['medium', 'كم مرة فازت البرازيل بكأس العالم حتى 2022؟', '5', '3', '7'],
                ['medium', 'من فاز بكأس العالم 2022؟', 'الأرجنتين', 'فرنسا', 'المغرب'],
                ['medium', 'ما اسم ملاعب قطر الشهيرة لكأس العالم؟', 'استاد لوسيل', 'ويمبلي', 'كامب نو'],
                ['hard', 'من هو الهداف التاريخي لكأس العالم؟', 'ميروشلافا كلوزه', 'بيليه', 'رونالدو'],
                ['hard', 'كم عدد المنتخبات في كأس العالم 2022؟', '32', '24', '48'],
                ['hard', 'أي دولة استضافت أول كأس عالم؟', 'الأوروغواي', 'البرازيل', 'إنجلترا'],
            ],
        ];

        foreach ($questionBank as $slug => $items) {
            $category = Category::where('slug', $slug)->first();
            if (! $category) {
                continue;
            }

            foreach ($items as $item) {
                [$level, $text, $correct, $wrong1, $wrong2] = $item;
                $points = config("game.points_map.$level", 200);

                $question = Question::updateOrCreate(
                    ['question_text' => $text],
                    [
                        'category_id' => $category->id,
                        'level' => $level,
                        'points' => $points,
                        'time_limit' => 60,
                        'is_active' => true,
                    ]
                );

                $question->options()->delete();
                $question->options()->createMany([
                    ['option_text' => $correct, 'is_correct' => true],
                    ['option_text' => $wrong1, 'is_correct' => false],
                    ['option_text' => $wrong2, 'is_correct' => false],
                ]);
            }
        }

        // Sample questions for remaining categories — 6 per difficulty
        foreach (Category::whereNotIn('slug', array_keys($questionBank))->get() as $category) {
            foreach (['easy' => 200, 'medium' => 400, 'hard' => 600] as $level => $points) {
                for ($i = 1; $i <= 6; $i++) {
                    $text = "سؤال {$category->name_ar} — مستوى {$level} رقم {$i}؟";
                    $question = Question::updateOrCreate(
                        ['question_text' => $text],
                        [
                            'category_id' => $category->id,
                            'level' => $level,
                            'points' => $points,
                            'time_limit' => 60,
                            'is_active' => true,
                        ]
                    );
                    $question->options()->delete();
                    $question->options()->createMany([
                        ['option_text' => 'الإجابة الصحيحة', 'is_correct' => true],
                        ['option_text' => 'خيار خاطئ 1', 'is_correct' => false],
                        ['option_text' => 'خيار خاطئ 2', 'is_correct' => false],
                    ]);
                }
            }
        }

        foreach ([
            ['أسبوعي', 'weekly', 10, 7, false],
            ['شهري', 'monthly', 25, 30, true],
            ['سنوي', 'yearly', 199, 365, false],
        ] as $index => $plan) {
            Plan::updateOrCreate(
                ['type' => $plan[1]],
                [
                    'name' => $plan[0],
                    'price' => $plan[2],
                    'duration_days' => $plan[3],
                    'currency' => 'AED',
                    'features' => ['فتح جميع الفئات', 'لعب غير محدود', 'جميع المستويات', 'تحديثات مستمرة'],
                    'is_recommended' => $plan[4],
                    'is_active' => true,
                    'sort_order' => $index + 1,
                ]
            );
        }
    }
}
