<?php

namespace App\Services\ClinicalAi;

use App\Contracts\MedicalDocumentEntityExtractor;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Throwable;

final class OpenAiMedicalDocumentEntityExtractor implements MedicalDocumentEntityExtractor
{
    public function extractFromOcrText(string $ocrText): ?array
    {
        $apiKey = config('isnad.ai.api_key');
        $model = config('isnad.ai.model', 'gpt-4o-mini');
        $timeout = config('isnad.ai.timeout', 60);

        if (! is_string($apiKey) || $apiKey === '') {
            throw new RuntimeException('Missing OpenAI API key.');
        }

        $system = <<<'PROMPT'
أنت تستخرج بيانات طبية من نص تم قراءته آلياً (OCR). أعد النتيجة كـ JSON فقط بدون شرح.
قواعد:
- لا تضف تشخيصاً أو معلومة غير ظاهرة في النص.
- إذا كان النص غير واضح استخدم confidence منخفضاً (0–1).
- المفاتيح المطلوبة: medications (مصفوفة من objects: name, dosage, frequency, duration, instructions, confidence),
  conditions (name, confidence), allergies (name, confidence),
  lab_results (test_name, value, unit, reference_range, confidence),
  notes (نص قصير عن أجزاء غير مقروءة).
PROMPT;

        try {
            $response = Http::timeout($timeout)
                ->withToken($apiKey)
                ->acceptJson()
                ->post('https://api.openai.com/v1/chat/completions', [
                    'model' => $model,
                    'temperature' => 0.1,
                    'response_format' => ['type' => 'json_object'],
                    'messages' => [
                        ['role' => 'system', 'content' => $system],
                        ['role' => 'user', 'content' => "النص المستخرج من المستند الطبي:\n\n".$ocrText],
                    ],
                ]);
        } catch (Throwable $e) {
            throw new RuntimeException('OpenAI extraction request failed: '.$e->getMessage(), 0, $e);
        }

        if (! $response->successful()) {
            throw new RuntimeException('OpenAI extraction HTTP '.$response->status().': '.$response->body());
        }

        $content = data_get($response->json(), 'choices.0.message.content');

        if (! is_string($content) || $content === '') {
            throw new RuntimeException('Empty OpenAI extraction response.');
        }

        /** @var array<string, mixed>|null $decoded */
        $decoded = json_decode($content, true);

        if (! is_array($decoded)) {
            throw new RuntimeException('Invalid JSON from OpenAI extraction.');
        }

        return $decoded;
    }

    public function isAvailable(): bool
    {
        return (bool) config('isnad.documents.extraction_enabled', false)
            && is_string(config('isnad.ai.api_key'))
            && config('isnad.ai.api_key') !== '';
    }
}
