<?php

namespace Tests\Unit;

use App\Models\Patient;
use App\Models\User;
use App\Policies\PatientPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PatientPolicyTest extends TestCase
{
    use RefreshDatabase;

    private PatientPolicy $policy;

    protected function setUp(): void
    {
        parent::setUp();
        $this->policy = new PatientPolicy;
    }

    public function test_operational_admin_can_create_registry_records_but_has_no_phi_mutation_or_links(): void
    {
        $admin = User::factory()->admin()->create();
        $patient = Patient::factory()->create();

        $this->assertTrue($this->policy->create($admin));
        $this->assertFalse($this->policy->update($admin, $patient));
        $this->assertFalse($this->policy->delete($admin, $patient));
        $this->assertFalse($this->policy->deleteAny($admin));
        $this->assertFalse($this->policy->generateTemporaryLink($admin, $patient));
        $this->assertFalse($admin->canViewSensitiveClinicalData());
        $this->assertTrue($this->policy->viewAny($admin));
        $this->assertTrue($this->policy->view($admin, $patient));
    }

    public function test_only_patient_role_has_sensitive_clinical_ui_flag(): void
    {
        $admin = User::factory()->admin()->create();
        $patientUser = User::factory()->patient()->create();

        $this->assertFalse($admin->canViewSensitiveClinicalData());
        $this->assertTrue($patientUser->canViewSensitiveClinicalData());
    }

    public function test_patient_portal_user_has_scoped_list_and_view_permissions(): void
    {
        $portalUser = User::factory()->patient()->create();
        $unlinkedPatient = Patient::factory()->create();

        $this->assertTrue($this->policy->viewAny($portalUser));
        $this->assertFalse($this->policy->view($portalUser, $unlinkedPatient));
        $this->assertFalse($this->policy->generateTemporaryLink($portalUser, $unlinkedPatient));
        $this->assertFalse($this->policy->updateOwnProfile($portalUser, $unlinkedPatient));
        $this->assertFalse($this->policy->update($portalUser, $unlinkedPatient));
        $this->assertFalse($portalUser->canEditClinicalRecords());
        $this->assertTrue($portalUser->canViewSensitiveClinicalData());
    }

    public function test_patient_can_generate_link_and_update_profile_only_for_own_record(): void
    {
        $portalUser = User::factory()->patient()->create();
        $own = Patient::factory()->create(['user_id' => $portalUser->getKey()]);
        $other = Patient::factory()->create();

        $this->assertTrue($this->policy->generateTemporaryLink($portalUser, $own));
        $this->assertTrue($this->policy->updateOwnProfile($portalUser, $own));
        $this->assertFalse($this->policy->generateTemporaryLink($portalUser, $other));
        $this->assertFalse($this->policy->updateOwnProfile($portalUser, $other));
        $this->assertFalse($this->policy->update($portalUser, $other));
        $this->assertTrue($this->policy->view($portalUser, $own));
        $this->assertTrue($this->policy->update($portalUser, $own));
    }
}
