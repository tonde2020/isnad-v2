<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PatientSelfRegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_view_patient_registration_form(): void
    {
        $this->get(route('patient.register'))
            ->assertOk()
            ->assertSeeText('إنشاء حساب مريض', false);
    }

    public function test_patient_registration_creates_user_and_profile_then_logs_in(): void
    {
        $response = $this->post(route('patient.register.store'), [
            'name' => 'فاطمة أحمد',
            'email' => 'patient@example.org',
            'password' => 'secret-password',
            'password_confirmation' => 'secret-password',
            'full_name' => 'فاطمة أحمد محمد',
            'phone' => '0912345678',
            'national_id' => null,
            'birth_date' => '1992-05-10',
            'blood_type' => 'O+',
            'gender' => 'female',
            'state' => null,
            'locality' => null,
            'displacement_area' => null,
            'emergency_contact_name' => 'أحمد',
            'emergency_contact_phone' => '0999999999',
            'accept_self_entered_disclaimer' => '1',
        ]);

        $response->assertRedirect(route('filament.app.pages.dashboard'));

        $this->assertDatabaseHas('users', [
            'email' => 'patient@example.org',
            'role' => UserRole::Patient->value,
        ]);

        $user = User::query()->where('email', 'patient@example.org')->firstOrFail();

        $this->assertDatabaseHas('patients', [
            'user_id' => $user->getKey(),
            'full_name' => 'فاطمة أحمد محمد',
            'phone' => '0912345678',
            'blood_type' => 'O+',
        ]);

        $patient = Patient::query()->where('user_id', $user->getKey())->firstOrFail();
        $this->assertNotNull($patient->registry_number);
        $this->assertSame(Patient::generateRegistryNumberForId($patient->getKey()), $patient->registry_number);

        $this->assertAuthenticatedAs($user);
    }

    public function test_registration_requires_disclaimer_acceptance(): void
    {
        $this->post(route('patient.register.store'), [
            'name' => 'مستخدم',
            'email' => 'x@example.org',
            'password' => 'secret-password',
            'password_confirmation' => 'secret-password',
            'full_name' => 'الاسم',
            'phone' => '0911111111',
        ])->assertSessionHasErrors('accept_self_entered_disclaimer');
    }

    public function test_admin_cannot_use_patient_login_route(): void
    {
        User::factory()->admin()->create([
            'email' => 'admin@example.org',
            'password' => 'password',
        ]);

        $this->post(route('patient.login.store'), [
            'email' => 'admin@example.org',
            'password' => 'password',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_patient_portal_requires_authentication(): void
    {
        $this->get(route('patient.home'))
            ->assertRedirect(route('patient.login'));
    }

    public function test_patient_user_can_open_portal_home(): void
    {
        $user = User::factory()->patient()->create();
        Patient::factory()->create(['user_id' => $user->getKey()]);

        $this->actingAs($user)->get(route('patient.home'))
            ->assertRedirect(route('filament.app.pages.dashboard'));
    }
}
