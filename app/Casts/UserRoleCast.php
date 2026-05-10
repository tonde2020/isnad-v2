<?php

namespace App\Casts;

use App\Enums\UserRole;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use ValueError;

/**
 * يقرأ أدواراً قديمة (staff / viewer) كـ admin حتى لا يحدث ValueError قبل تشغيل هجرات التطبيع.
 */
final class UserRoleCast implements CastsAttributes
{
    public function get(Model $model, string $key, mixed $value, array $attributes): ?UserRole
    {
        if ($value === null || $value === '') {
            return null;
        }

        $string = (string) $value;

        $resolved = UserRole::tryFrom($string);
        if ($resolved !== null) {
            return $resolved;
        }

        return match ($string) {
            'staff', 'viewer' => UserRole::Admin,
            default => throw new ValueError(sprintf(
                '"%s" is not a valid backing value for enum %s',
                $string,
                UserRole::class,
            )),
        };
    }

    public function set(Model $model, string $key, mixed $value, array $attributes): ?string
    {
        if ($value === null) {
            return null;
        }

        if ($value instanceof UserRole) {
            return $value->value;
        }

        return UserRole::from((string) $value)->value;
    }
}
