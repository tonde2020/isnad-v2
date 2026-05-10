<?php

namespace App\Http\Controllers;

use App\Models\MedicalRecord;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class MedicalPrivateFileController extends Controller
{
    public function stream(Request $request): Response
    {
        $path = $request->query('path');

        if (! is_string($path) || $path === '' || str_contains($path, '..')) {
            abort(403);
        }

        if (! str_starts_with($path, 'medical-records/')) {
            abort(403);
        }

        $record = MedicalRecord::query()->where('file_path', $path)->first();

        if ($record === null) {
            abort(404);
        }

        Gate::authorize('view', $record);

        if (! Storage::disk('medical_private')->exists($path)) {
            abort(404);
        }

        return Storage::disk('medical_private')->response($path);
    }
}
