<?php

return [

    /**
     * رقم السجل الظاهر في ملف المريض (يُولَّد تلقائياً بعد الحفظ، غير قابل للتعديل يدوياً من الواجهة).
     */
    'registry' => [
        'prefix' => env('ISNAD_REGISTRY_PREFIX', 'ISN'),
        'sequence_length' => max(6, min(12, (int) env('ISNAD_REGISTRY_SEQUENCE_LENGTH', 8))),
        /** مدة بقاء صفحة العرض بعد التحقق من رقم السجل (بالدقائق). */
        'lookup_session_ttl_minutes' => max(5, min(120, (int) env('ISNAD_REGISTRY_LOOKUP_TTL', 15))),
    ],

    'ai' => [
        'enabled' => (bool) env('ISNAD_AI_SUMMARY_ENABLED', false),
        'api_key' => env('OPENAI_API_KEY'),
        'model' => env('OPENAI_MODEL', 'gpt-4o-mini'),
        'timeout' => (int) env('ISNAD_AI_TIMEOUT', 60),
    ],

    'documents' => [
        /**
         * مسار ثنائي Tesseract على الخادم (اختياري). مثال Windows: C:\Program Files\Tesseract-OCR\tesseract.exe
         */
        'tesseract_binary' => env('TESSERACT_BINARY'),

        /**
         * بعد OCR: استدعاء OpenAI لاستخراج كيانات منظمة في extracted_entities (لا يُعتمد على الملف تلقائياً).
         */
        'extraction_enabled' => (bool) env('ISNAD_DOCUMENT_EXTRACTION_ENABLED', false),
    ],

    /**
     * معالجة الطابور في الخلفية بدون إبقاء `queue:work` يدوياً.
     * يتطلب تشغيل مجدول Laravel كل دقيقة: `php artisan schedule:run` (Cron أو Task Scheduler على Windows).
     */
    'queue' => [
        'process_via_scheduler' => (bool) env('ISNAD_QUEUE_PROCESS_VIA_SCHEDULER', false),
    ],

    /**
     * لقطات مؤشرات صحية مجمّعة (لوحة المشرف). لا تُعرض الفئات الأصغر من هذا الحد.
     */
    'health_snapshots' => [
        'enabled' => (bool) env('ISNAD_HEALTH_SNAPSHOT_ENABLED', true),
        'daily_time' => env('ISNAD_HEALTH_SNAPSHOT_TIME', '02:00'),
        'minimum_group_size' => max(1, (int) env('ISNAD_HEALTH_SNAPSHOT_MIN_GROUP', 10)),
    ],

];
