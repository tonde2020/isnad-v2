<?php

namespace Tests\Feature;

use App\Models\MedicalRecord;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class MedicalPrivateFileStreamTest extends TestCase
{
    use RefreshDatabase;

    public function test_patient_owner_can_stream_own_medical_file_with_valid_signature(): void
    {
        Storage::fake('medical_private');

        $path = 'medical-records/test-stream.jpeg';
        Storage::disk('medical_private')->put($path, 'fake-image-bytes');

        $user = User::factory()->patient()->create();
        $patient = Patient::factory()->create(['user_id' => $user->getKey()]);

        MedicalRecord::query()->create([
            'patient_id' => $patient->getKey(),
            'title' => 'مرفق',
            'file_path' => $path,
            'processing_status' => 'completed',
        ]);

        $url = URL::temporarySignedRoute(
            'medical-private-files.stream',
            now()->addMinutes(5),
            ['path' => $path],
            absolute: true,
        );

        $this->actingAs($user)->get($url)->assertOk();
    }

    public function test_invalid_signature_returns_forbidden(): void
    {
        Storage::fake('medical_private');

        $path = 'medical-records/x.jpeg';
        Storage::disk('medical_private')->put($path, 'x');

        $user = User::factory()->patient()->create();
        $patient = Patient::factory()->create(['user_id' => $user->getKey()]);

        MedicalRecord::query()->create([
            'patient_id' => $patient->getKey(),
            'title' => 'مرفق',
            'file_path' => $path,
            'processing_status' => 'completed',
        ]);

        $this->actingAs($user)
            ->get('/medical-private-files/stream?path='.urlencode($path))
            ->assertForbidden();
    }
}
