<?php

namespace Database\Seeders;

use App\Models\DiseaseMaster;
use App\Models\MedicationMaster;
use Illuminate\Database\Seeder;

class DiseaseMedicationMasterSeeder extends Seeder
{
    public function run(): void
    {
        $chronic = [
            ['name_ar' => 'سكري', 'name_en' => 'Diabetes mellitus'],
            ['name_ar' => 'ضغط دم مرتفع', 'name_en' => 'Hypertension'],
            ['name_ar' => 'ربو قصبي', 'name_en' => 'Asthma'],
            ['name_ar' => 'أمراض قلبية', 'name_en' => 'Heart disease'],
            ['name_ar' => 'فشل كلوي مزمن', 'name_en' => 'CKD'],
            ['name_ar' => 'غدة درقية', 'name_en' => 'Thyroid disorder'],
            ['name_ar' => 'سمنة', 'name_en' => 'Obesity'],
            ['name_ar' => 'ارتفاع دهون الدم', 'name_en' => 'Dyslipidemia'],
            ['name_ar' => 'أمراض كبد مزمنة', 'name_en' => 'Chronic liver disease'],
            ['name_ar' => 'صرع', 'name_en' => 'Epilepsy'],
        ];

        foreach ($chronic as $row) {
            DiseaseMaster::query()->firstOrCreate(
                ['name_ar' => $row['name_ar'], 'category' => 'chronic'],
                ['name_en' => $row['name_en'], 'is_active' => true],
            );
        }

        $allergies = [
            ['name_ar' => 'حساسية دوائية', 'name_en' => 'Drug allergy'],
            ['name_ar' => 'حساسية بنسلين', 'name_en' => 'Penicillin allergy'],
            ['name_ar' => 'حساسية مضادات حيوية', 'name_en' => 'Antibiotic allergy'],
            ['name_ar' => 'حساسية طعام', 'name_en' => 'Food allergy'],
            ['name_ar' => 'حساسية غبار / حبوب لقاح', 'name_en' => 'Environmental allergy'],
        ];

        foreach ($allergies as $row) {
            DiseaseMaster::query()->firstOrCreate(
                ['name_ar' => $row['name_ar'], 'category' => 'allergy'],
                ['name_en' => $row['name_en'], 'is_active' => true],
            );
        }

        $meds = [
            ['brand_name' => 'Glucophage', 'generic_name' => 'Metformin', 'strength' => '500mg', 'form' => 'tablet'],
            ['brand_name' => 'Janumet', 'generic_name' => 'Sitagliptin/Metformin', 'strength' => '50/1000mg', 'form' => 'tablet'],
            ['brand_name' => 'Norvasc', 'generic_name' => 'Amlodipine', 'strength' => '5mg', 'form' => 'tablet'],
            ['brand_name' => 'Concor', 'generic_name' => 'Bisoprolol', 'strength' => '5mg', 'form' => 'tablet'],
            ['brand_name' => 'Lipitor', 'generic_name' => 'Atorvastatin', 'strength' => '20mg', 'form' => 'tablet'],
            ['brand_name' => 'Augmentin', 'generic_name' => 'Amoxicillin/Clavulanate', 'strength' => '625mg', 'form' => 'tablet'],
            ['brand_name' => 'Panadol', 'generic_name' => 'Paracetamol', 'strength' => '500mg', 'form' => 'tablet'],
            ['brand_name' => 'Brufen', 'generic_name' => 'Ibuprofen', 'strength' => '400mg', 'form' => 'tablet'],
            ['brand_name' => 'Ventolin', 'generic_name' => 'Salbutamol', 'strength' => '100mcg', 'form' => 'inhaler'],
            ['brand_name' => 'Eltroxin', 'generic_name' => 'Levothyroxine', 'strength' => '100mcg', 'form' => 'tablet'],
        ];

        foreach ($meds as $m) {
            MedicationMaster::query()->firstOrCreate(
                [
                    'generic_name' => $m['generic_name'],
                    'strength' => $m['strength'],
                ],
                [
                    'brand_name' => $m['brand_name'],
                    'form' => $m['form'],
                    'is_active' => true,
                ],
            );
        }
    }
}
