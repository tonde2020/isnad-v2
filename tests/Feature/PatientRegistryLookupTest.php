<?php

namespace Tests\Feature;

use App\Models\Patient;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PatientRegistryLookupTest extends TestCase
{
    use RefreshDatabase;

    public function test_lookup_form_is_public(): void
    {
        $this->get(route('patient.registry.lookup'))
            ->assertOk()
            ->assertSeeText('رقم السجل', false)
            ->assertSeeText('تحقق بشري', false);
    }

    public function test_submit_requires_captcha_answer(): void
    {
        $this->get(route('patient.registry.lookup'));

        $this->post(route('patient.registry.lookup.submit'), [
            'registry_number' => 'ISN-00000001',
        ])->assertSessionHasErrors('captcha_answer');
    }

    public function test_honeypot_triggers_generic_error(): void
    {
        $this->get(route('patient.registry.lookup'));

        $this->post(route('patient.registry.lookup.submit'), [
            'registry_number' => 'ISN-00000001',
            'captcha_answer' => 4,
            'website' => 'http://spam.example',
        ])
            ->assertRedirect(route('patient.registry.lookup'))
            ->assertSessionHasErrors('registry_number');
    }

    public function test_submitting_valid_registry_redirects_to_show_view(): void
    {
        $user = User::factory()->patient()->create();
        $patient = Patient::factory()->create(['user_id' => $user->getKey()]);
        $patient->refresh();

        $this->get(route('patient.registry.lookup'));
        $answer = session(self::captchaSessionKey())['answer'];

        $this->post(route('patient.registry.lookup.submit'), [
            'registry_number' => $patient->registry_number,
            'captcha_answer' => $answer,
        ])->assertRedirect(route('patient.registry.lookup.show'));

        $this->get(route('patient.registry.lookup.show'))
            ->assertOk()
            ->assertSeeText($patient->full_name, false)
            ->assertSeeText($patient->registry_number ?? '', false);
    }

    public function test_unknown_registry_shows_validation_error(): void
    {
        $this->get(route('patient.registry.lookup'));
        $answer = session(self::captchaSessionKey())['answer'];

        $this->post(route('patient.registry.lookup.submit'), [
            'registry_number' => 'XX-NOTFOUND-999',
            'captcha_answer' => $answer,
        ])
            ->assertRedirect(route('patient.registry.lookup'))
            ->assertSessionHasErrors('registry_number');
    }

    /**
     * @return non-empty-string
     */
    private static function captchaSessionKey(): string
    {
        return 'registry_lookup_captcha';
    }

    public function test_show_without_session_redirects_to_form(): void
    {
        $this->get(route('patient.registry.lookup.show'))
            ->assertRedirect(route('patient.registry.lookup'));
    }
}
