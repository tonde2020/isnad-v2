<?php

namespace App\Services\ClinicalAi;

use App\Contracts\ClinicalSummarizer;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Throwable;

final class OpenAiClinicalSummarizer implements ClinicalSummarizer
{
    public function summarizePatientSnapshot(array $anonymizedContext): string
    {
        $apiKey = config('isnad.ai.api_key');
        $model = config('isnad.ai.model', 'gpt-4o-mini');
        $timeout = config('isnad.ai.timeout', 60);

        if (! is_string($apiKey) || $apiKey === '') {
            throw new RuntimeException('Missing OpenAI API key.');
        }

        $system = <<<'PROMPT'
أنت مساعد لقراءة ملفات مرضى للأطباء فقط. أخرج ملخصاً بالعربية الفصحى المبسطة، بنقاط واضحة.
قواعد صارمة:
- لا تقدّم تشخيصاً نهائياً ولا توصية علاجية قاطعة.
- اذكر أن المعلومات قد تكون ناقصة وأن القرار الطبي للطبيب المعالج.
- إن كانت البيانات ناقصة، قل ذلك صراحة.
PROMPT;

        $userPayload = json_encode($anonymizedContext, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);

        try {
            $response = Http::timeout($timeout)
                ->withToken($apiKey)
                ->acceptJson()
                ->post('https://api.openai.com/v1/chat/completions', [
                    'model' => $model,
                    'temperature' => 0.2,
                    'messages' => [
                        ['role' => 'system', 'content' => $system],
                        ['role' => 'user', 'content' => "السياق السريري (بدون هوية مباشرة):\n\n".$userPayload],
                    ],
                ]);
        } catch (Throwable $e) {
            throw new RuntimeException('OpenAI request failed: '.$e->getMessage(), 0, $e);
        }

        if (! $response->successful()) {
            throw new RuntimeException('OpenAI HTTP '.$response->status().': '.$response->body());
        }

        $text = data_get($response->json(), 'choices.0.message.content');

        if (! is_string($text) || $text === '') {
            throw new RuntimeException('Empty OpenAI completion.');
        }

        return trim($text);
    }

    public function isAvailable(): bool
    {
        return is_string(config('isnad.ai.api_key'))
            && config('isnad.ai.api_key') !== ''
            && (bool) config('isnad.ai.enabled', false);
    }
}
