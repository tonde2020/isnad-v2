<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DiseaseMasterSeeder extends Seeder
{
    public function run(): void
    {
        $diseases = [
            ['code' => 'hypertension', 'name_ar' => 'ارتفاع ضغط الدم', 'name_en' => 'Hypertension', 'category' => 'cardiovascular'],
            ['code' => 'heart_failure', 'name_ar' => 'فشل القلب', 'name_en' => 'Heart Failure', 'category' => 'cardiovascular'],
            ['code' => 'ischemic_heart_disease', 'name_ar' => 'مرض القلب الإقفاري / الذبحة', 'name_en' => 'Ischemic Heart Disease', 'category' => 'cardiovascular'],
            ['code' => 'arrhythmia', 'name_ar' => 'اضطراب ضربات القلب', 'name_en' => 'Arrhythmia', 'category' => 'cardiovascular'],
            ['code' => 'heart_disease_general', 'name_ar' => 'أمراض القلب', 'name_en' => 'Heart Disease', 'category' => 'cardiovascular'],

            ['code' => 'diabetes_type_1', 'name_ar' => 'السكري النوع الأول', 'name_en' => 'Type 1 Diabetes', 'category' => 'endocrine'],
            ['code' => 'diabetes_type_2', 'name_ar' => 'السكري النوع الثاني', 'name_en' => 'Type 2 Diabetes', 'category' => 'endocrine'],
            ['code' => 'gestational_diabetes', 'name_ar' => 'سكري الحمل', 'name_en' => 'Gestational Diabetes', 'category' => 'endocrine'],
            ['code' => 'hypothyroidism', 'name_ar' => 'قصور الغدة الدرقية', 'name_en' => 'Hypothyroidism', 'category' => 'endocrine'],
            ['code' => 'hyperthyroidism', 'name_ar' => 'فرط نشاط الغدة الدرقية', 'name_en' => 'Hyperthyroidism', 'category' => 'endocrine'],

            ['code' => 'asthma', 'name_ar' => 'الربو', 'name_en' => 'Asthma', 'category' => 'respiratory'],
            ['code' => 'copd', 'name_ar' => 'الانسداد الرئوي المزمن', 'name_en' => 'COPD', 'category' => 'respiratory'],
            ['code' => 'chronic_bronchitis', 'name_ar' => 'التهاب الشعب الهوائية المزمن', 'name_en' => 'Chronic Bronchitis', 'category' => 'respiratory'],

            ['code' => 'chronic_kidney_disease', 'name_ar' => 'مرض الكلى المزمن', 'name_en' => 'Chronic Kidney Disease', 'category' => 'renal'],
            ['code' => 'kidney_failure', 'name_ar' => 'الفشل الكلوي', 'name_en' => 'Kidney Failure', 'category' => 'renal'],
            ['code' => 'chronic_liver_disease', 'name_ar' => 'مرض الكبد المزمن', 'name_en' => 'Chronic Liver Disease', 'category' => 'hepatic'],
            ['code' => 'hepatitis_b', 'name_ar' => 'التهاب الكبد الوبائي ب', 'name_en' => 'Hepatitis B', 'category' => 'hepatic'],
            ['code' => 'hepatitis_c', 'name_ar' => 'التهاب الكبد الوبائي ج', 'name_en' => 'Hepatitis C', 'category' => 'hepatic'],
            ['code' => 'viral_hepatitis', 'name_ar' => 'التهاب الكبد الوبائي', 'name_en' => 'Viral Hepatitis', 'category' => 'hepatic'],

            ['code' => 'epilepsy', 'name_ar' => 'الصرع', 'name_en' => 'Epilepsy', 'category' => 'neurological'],
            ['code' => 'stroke_history', 'name_ar' => 'جلطة دماغية سابقة', 'name_en' => 'History of Stroke', 'category' => 'neurological'],
            ['code' => 'parkinsons_disease', 'name_ar' => 'مرض باركنسون', 'name_en' => 'Parkinson\'s Disease', 'category' => 'neurological'],
            ['code' => 'multiple_sclerosis', 'name_ar' => 'التصلب المتعدد', 'name_en' => 'Multiple Sclerosis', 'category' => 'neurological'],

            ['code' => 'sickle_cell_disease', 'name_ar' => 'فقر الدم المنجلي', 'name_en' => 'Sickle Cell Disease', 'category' => 'hematology'],
            ['code' => 'thalassemia', 'name_ar' => 'الثلاسيميا', 'name_en' => 'Thalassemia', 'category' => 'hematology'],
            ['code' => 'chronic_anemia', 'name_ar' => 'فقر الدم المزمن', 'name_en' => 'Chronic Anemia', 'category' => 'hematology'],
            ['code' => 'hiv', 'name_ar' => 'فيروس نقص المناعة البشرية', 'name_en' => 'HIV', 'category' => 'immunology'],
            ['code' => 'lupus', 'name_ar' => 'الذئبة الحمراء', 'name_en' => 'Lupus', 'category' => 'immunology'],
            ['code' => 'rheumatoid_arthritis', 'name_ar' => 'الروماتويد', 'name_en' => 'Rheumatoid Arthritis', 'category' => 'immunology'],

            ['code' => 'peptic_ulcer_disease', 'name_ar' => 'قرحة المعدة المزمنة', 'name_en' => 'Peptic Ulcer Disease', 'category' => 'gastrointestinal'],
            ['code' => 'inflammatory_bowel_disease', 'name_ar' => 'التهاب الأمعاء المزمن', 'name_en' => 'Inflammatory Bowel Disease', 'category' => 'gastrointestinal'],
            ['code' => 'gerd', 'name_ar' => 'ارتجاع المريء المزمن', 'name_en' => 'GERD', 'category' => 'gastrointestinal'],

            ['code' => 'osteoarthritis', 'name_ar' => 'خشونة المفاصل', 'name_en' => 'Osteoarthritis', 'category' => 'musculoskeletal'],
            ['code' => 'osteoporosis', 'name_ar' => 'هشاشة العظام', 'name_en' => 'Osteoporosis', 'category' => 'musculoskeletal'],
            ['code' => 'chronic_back_pain', 'name_ar' => 'آلام الظهر المزمنة', 'name_en' => 'Chronic Back Pain', 'category' => 'musculoskeletal'],

            ['code' => 'cancer', 'name_ar' => 'سرطان / ورم خبيث', 'name_en' => 'Cancer', 'category' => 'oncology'],
            ['code' => 'breast_cancer', 'name_ar' => 'سرطان الثدي', 'name_en' => 'Breast Cancer', 'category' => 'oncology'],
            ['code' => 'prostate_cancer', 'name_ar' => 'سرطان البروستاتا', 'name_en' => 'Prostate Cancer', 'category' => 'oncology'],

            ['code' => 'depression', 'name_ar' => 'الاكتئاب المزمن', 'name_en' => 'Depression', 'category' => 'mental_health'],
            ['code' => 'anxiety_disorder', 'name_ar' => 'اضطراب القلق', 'name_en' => 'Anxiety Disorder', 'category' => 'mental_health'],
            ['code' => 'bipolar_disorder', 'name_ar' => 'اضطراب ثنائي القطب', 'name_en' => 'Bipolar Disorder', 'category' => 'mental_health'],
            ['code' => 'schizophrenia', 'name_ar' => 'الفصام', 'name_en' => 'Schizophrenia', 'category' => 'mental_health'],

            ['code' => 'cerebral_palsy', 'name_ar' => 'الشلل الدماغي', 'name_en' => 'Cerebral Palsy', 'category' => 'pediatric'],
            ['code' => 'autism_spectrum_disorder', 'name_ar' => 'اضطراب طيف التوحد', 'name_en' => 'Autism Spectrum Disorder', 'category' => 'pediatric'],
            ['code' => 'down_syndrome', 'name_ar' => 'متلازمة داون', 'name_en' => 'Down Syndrome', 'category' => 'pediatric'],

            ['code' => 'obesity', 'name_ar' => 'السمنة', 'name_en' => 'Obesity', 'category' => 'general'],
            ['code' => 'malnutrition', 'name_ar' => 'سوء التغذية المزمن', 'name_en' => 'Chronic Malnutrition', 'category' => 'general'],
            ['code' => 'disability', 'name_ar' => 'إعاقة مزمنة', 'name_en' => 'Chronic Disability', 'category' => 'general'],
            ['code' => 'other_chronic_disease', 'name_ar' => 'مرض مزمن آخر', 'name_en' => 'Other Chronic Disease', 'category' => 'other'],

            ['code' => 'chronic_allergy', 'name_ar' => 'حساسية مزمنة', 'name_en' => 'Chronic Allergy', 'category' => 'allergy'],
        ];

        $now = now();

        foreach ($diseases as $disease) {
            $payload = [
                'name_ar' => $disease['name_ar'],
                'name_en' => $disease['name_en'],
                'category' => $disease['category'],
                'is_active' => true,
                'updated_at' => $now,
            ];

            $exists = DB::table('disease_masters')->where('code', $disease['code'])->exists();

            if ($exists) {
                DB::table('disease_masters')->where('code', $disease['code'])->update($payload);
            } else {
                DB::table('disease_masters')->insert(array_merge($payload, [
                    'code' => $disease['code'],
                    'created_at' => $now,
                ]));
            }
        }
    }
}
