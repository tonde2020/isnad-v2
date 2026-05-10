<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <style>
        * { box-sizing: border-box; }
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 10px;
            color: #4A3F35;
            margin: 0;
            padding: 12px;
            background: #fff;
        }
        .brand-muted { color: #8D7456; }
        .page-title {
            font-size: 17px;
            font-weight: bold;
            margin: 0 0 6px;
            color: #4A3F35;
        }
        .registry {
            font-family: DejaVu Sans Mono, DejaVu Sans, monospace;
            font-size: 10px;
            font-weight: bold;
            color: #8D7456;
            margin-bottom: 8px;
        }
        .meta-row { font-size: 10px; color: #6D5F52; margin-bottom: 4px; }
        .card {
            border: 1px solid #EAE3D2;
            border-radius: 10px;
            padding: 10px 12px;
            margin-bottom: 10px;
            background: #FDFBF7;
        }
        .card-plain { background: #fff; }
        h2 {
            font-size: 11px;
            margin: 0 0 6px;
            color: #4A3F35;
            font-weight: bold;
            border-right: 3px solid #8D7456;
            padding-right: 6px;
        }
        .warn {
            background: #fff5f5;
            border: 1px solid #fecaca;
            border-right: 5px solid #ef4444;
            padding: 10px 12px;
            margin-bottom: 10px;
            border-radius: 8px;
        }
        .warn h2 { border-right-color: #ef4444; color: #991b1b; }
        .two-col { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        .two-col td { width: 50%; vertical-align: top; padding: 0 4px; }
        .two-col td:first-child { padding-right: 0; padding-left: 6px; }
        .two-col td:last-child { padding-left: 0; padding-right: 6px; }
        .tag {
            display: inline-block;
            background: #F5EFE6;
            border: 1px solid #EAE3D2;
            color: #6D5F52;
            padding: 3px 8px;
            border-radius: 6px;
            font-size: 9px;
            font-weight: bold;
            margin: 2px 0 2px 4px;
        }
        .ai-box {
            background: #f0f9ff;
            border: 1px solid #bae6fd;
            border-radius: 10px;
            padding: 10px 12px;
            margin-bottom: 10px;
        }
        .ai-note { font-size: 8px; color: #0369a1; margin: 0 0 6px; }
        ul { margin: 4px 0 0; padding-right: 14px; }
        .qr-wrap {
            text-align: center;
            vertical-align: middle;
            width: 118px;
            padding: 4px;
        }
        .qr-inner {
            display: inline-block;
            padding: 6px;
            background: #fff;
            border: 1px solid #EAE3D2;
            border-radius: 8px;
        }
        .qr-caption {
            font-size: 7px;
            color: #94a3b8;
            margin-top: 4px;
            font-weight: bold;
            line-height: 1.3;
        }
        .header-table { width: 100%; border-collapse: collapse; margin-bottom: 12px; }
        .header-table td { vertical-align: top; }
    </style>
</head>
<body>

    @php
        $pdfAllergyRows = $patient->patientAllergyRecords;
        $pdfShowAllergy = $pdfAllergyRows->isNotEmpty() || filled($patient->allergies);
        $pdfPm = $patient->patientMedications->where('is_active', true);
        $pdfLegacy = $patient->medications->where('is_active', true);
    @endphp

    <table class="header-table" dir="rtl">
        <tr>
            <td style="width: 72%;">
                <h1 class="page-title">{{ $patient->full_name }}</h1>
                @if(filled($patient->registry_number))
                    <div class="registry">رقم السجل: {{ $patient->registry_number }}</div>
                @endif
                <div class="meta-row">
                    @if($patient->birth_date)
                        العمر: {{ $patient->birth_date->age }} سنة
                    @else
                        العمر: —
                    @endif
                    @if($patient->blood_type)
                        <span class="brand-muted"> — فصيلة الدم: {{ $patient->blood_type }}</span>
                    @endif
                </div>
            </td>
            <td class="qr-wrap">
                <div class="qr-inner">
                    {!! QrCode::format('svg')->size(96)->margin(0)->color(141, 116, 86)->backgroundColor(255, 255, 255)->generate($profileShareUrl) !!}
                </div>
                <div class="qr-caption">مسح الرمز للملف الرقمي<br>(رابط موقّع {{ \App\Support\PatientTemporaryProfileLink::EXPIRY_MINUTES }} دقيقة)</div>
            </td>
        </tr>
    </table>

    @if($pdfShowAllergy)
        <div class="warn">
            <h2>تنبيهات الحساسية</h2>
            @if($pdfAllergyRows->isNotEmpty())
                @foreach($pdfAllergyRows as $row)
                    <span class="tag" style="background:#fee2e2;border-color:#fecaca;color:#991b1b;">{{ $row->displayLabel() }}</span>
                @endforeach
            @endif
            @if(filled($patient->allergies))
                <div style="margin-top:6px;color:#7f1d1d;font-weight:bold;">{!! nl2br(e($patient->allergies)) !!}</div>
            @endif
        </div>
    @endif

    @if(filled($patient->emergency_contact_name) || filled($patient->emergency_contact_phone))
        <div class="card card-plain">
            <h2>جهة اتصال الطوارئ</h2>
            @if(filled($patient->emergency_contact_name))
                <div><strong>الاسم:</strong> {{ $patient->emergency_contact_name }}</div>
            @endif
            @if(filled($patient->emergency_contact_phone))
                <div dir="ltr" style="text-align:right;"><strong>الهاتف:</strong> {{ $patient->emergency_contact_phone }}</div>
            @endif
        </div>
    @endif

    <table class="two-col" dir="rtl">
        <tr>
            <td>
                <div class="card card-plain" style="min-height: 100%;">
                    <h2>الأمراض المزمنة</h2>
                    @if($patient->patientChronicDiseases->isNotEmpty())
                        <div style="margin-bottom:6px;">
                            @foreach($patient->patientChronicDiseases as $row)
                                <span class="tag">{{ $row->displayLabel() }}</span>
                            @endforeach
                        </div>
                    @endif
                    @if(filled($patient->chronic_diseases))
                        <div style="font-size:9px;line-height:1.45;">{!! nl2br(e($patient->chronic_diseases)) !!}</div>
                    @elseif($patient->patientChronicDiseases->isEmpty())
                        <span class="brand-muted" style="font-style:italic;">لا توجد أمراض مزمنة مسجلة في السجل المنظم.</span>
                    @endif
                </div>
            </td>
            <td>
                <div class="card card-plain" style="min-height: 100%;">
                    <h2>الأدوية الحالية</h2>
                    @if($pdfPm->isNotEmpty())
                        <ul>
                            @foreach($pdfPm as $med)
                                <li>
                                    <strong>{{ $med->displayMedicationName() }}</strong>
                                    @if($med->dosage || $med->frequency || $med->duration)
                                        — {{ trim(implode(' — ', array_filter([$med->dosage, $med->frequency, $med->duration]))) }}
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    @elseif($pdfLegacy->isNotEmpty())
                        <ul>
                            @foreach($pdfLegacy as $med)
                                <li>
                                    <strong>{{ $med->medication_name }}</strong>
                                    @if($med->dosage || $med->frequency)
                                        — {{ trim(implode(' — ', array_filter([$med->dosage, $med->frequency]))) }}
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <span class="brand-muted" style="font-style:italic;">لا توجد أدوية نشطة مسجلة حالياً.</span>
                    @endif
                </div>
            </td>
        </tr>
    </table>

    @if(filled($patient->clinical_ai_summary) && ! $patient->ai_summary_disabled)
        <div class="ai-box">
            <h2 style="border-right-color:#0ea5e9;color:#0c4a6e;">ملخص مساعد (ذكاء اصطناعي)</h2>
            <p class="ai-note">
                للمساعدة على القراءة السريعة فقط — ليس تشخيصاً ولا توصية علاجية.
                @if($patient->clinical_ai_summary_generated_at)
                    تاريخ التوليد: {{ $patient->clinical_ai_summary_generated_at->format('Y-m-d H:i') }}
                @endif
            </p>
            <div style="font-size:9px;color:#0c4a6e;line-height:1.45;">{!! nl2br(e($patient->clinical_ai_summary)) !!}</div>
        </div>
    @endif

    <div class="card card-plain">
        <h2>آخر التقارير والمرفقات</h2>
        @forelse($patient->medicalRecords as $rec)
            <div style="margin-bottom:4px;">
                • {{ $rec->title ?: 'مرفق طبي' }}
                @if($rec->record_date)
                    <span class="brand-muted">({{ $rec->record_date->format('Y-m-d') }})</span>
                @endif
            </div>
        @empty
            <div style="border:2px dashed #F0EBE3;border-radius:12px;padding:16px;text-align:center;color:#94a3b8;font-weight:bold;font-style:italic;">
                لم يُرفق أي ملف طبي حديثاً في هذا التقرير المطبوع.
            </div>
        @endforelse
    </div>

</body>
</html>
