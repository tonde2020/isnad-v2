<?php

namespace App\Enums;

enum UserRole: string
{
    case Admin = 'admin';
    case Patient = 'patient';

    /**
     * إنشاء ملفات مرضى من لوحة التحكم وحذفها وتعديل غير الملف الشخصي للمريض.
     */
    public function canEditClinicalRecords(): bool
    {
        return $this === self::Admin;
    }

    /**
     * عرض البيانات الطبية التفصيلية في الواجهات — للمريض على ملفه فقط (مع سياسات Laravel).
     * المشرف التشغيلي لا يمرّ هنا؛ يرى أرقام السجل والأعداد فقط عبر واجهات مخصصة.
     */
    public function canViewSensitiveClinicalData(): bool
    {
        return $this === self::Patient;
    }

    public function isPatientPortalUser(): bool
    {
        return $this === self::Patient;
    }
}
