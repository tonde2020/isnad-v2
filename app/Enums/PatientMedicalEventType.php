<?php

namespace App\Enums;

enum PatientMedicalEventType: string
{
    case DiagnosisChronic = 'diagnosis_chronic';
    case AllergyAdded = 'allergy_added';
    case MedicationStarted = 'medication_started';
    case AttachmentUploaded = 'attachment_uploaded';
    /** ذكر نتائج مختبر من اعتماد استخراج OCR/AI — ليس تشخيصاً معتمداً تلقائياً */
    case LabResultsRecorded = 'lab_results_recorded';
}
