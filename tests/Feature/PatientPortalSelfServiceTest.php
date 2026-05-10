<?php

namespace Tests\Feature;

use App\Models\DiseaseMaster;
use App\Models\Patient;
use App\Models\PatientDisease;
use App\Models\User;
use Database\Seeders\DiseaseMasterSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PatientPortalSelfServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_patient_can_update_own_profile(): void
    {
        $this->seed(DiseaseMasterSeeder::class);

        $user = User::factory()->patient()->create(['name' => 'قديم']);
        $patient = Patient::factory()->create([
            'user_id' => $user->getKey(),
            'full_name' => 'اسم قديم',
            'phone' => '0911111111',
        ]);

        $hypertension = DiseaseMaster::query()->where('code', 'hypertension')->firstOrFail();
        $chronicAllergy = DiseaseMaster::query()->where('code', 'chronic_allergy')->firstOrFail();

        $this->actingAs($user)->put(route('patient.profile.update'), [
            'name' => 'جديد',
            'full_name' => 'اسم كامل محدث',
            'phone' => '0922222222',
            'national_id' => null,
            'birth_date' => '1990-01-15',
            'blood_type' => 'O+',
            'gender' => 'male',
            'state' => 'خرطوم',
            'locality' => null,
            'displacement_area' => null,
            'emergency_contact_name' => 'أحمد',
            'emergency_contact_phone' => '0933333333',
            'chronic_master_ids' => [$hypertension->getKey()],
            'allergy_master_ids' => [$chronicAllergy->getKey()],
            'chronic_diseases' => 'ضغط',
            'allergies' => 'بنسلين',
        ])->assertRedirect(route('filament.app.pages.dashboard'));

        $user->refresh();
        $patient->refresh();

        $this->assertSame('جديد', $user->name);
        $this->assertSame('اسم كامل محدث', $patient->full_name);
        $this->assertSame('0922222222', $patient->phone);
        $this->assertSame('O+', $patient->blood_type);

        $this->assertTrue(
            PatientDisease::query()
                ->where('patient_id', $patient->getKey())
                ->where('disease_master_id', $hypertension->getKey())
                ->where('source', 'patient')
                ->exists()
        );
        $this->assertTrue(
            PatientDisease::query()
                ->where('patient_id', $patient->getKey())
                ->where('disease_master_id', $chronicAllergy->getKey())
                ->where('source', 'patient')
                ->exists()
        );
    }

    public function test_patient_share_page_contains_profile_link_token(): void
    {
        $user = User::factory()->patient()->create();
        $patient = Patient::factory()->create(['user_id' => $user->getKey()]);

        $this->actingAs($user)
            ->get(route('patient.share'))
            ->assertOk()
            ->assertSee($patient->uuid, false)
            ->assertSee('رابط مشاهدة للطبيب', false);
    }
}
