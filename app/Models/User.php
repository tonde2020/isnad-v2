<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Casts\UserRoleCast;
use App\Enums\UserRole;
use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasAvatar;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements FilamentUser, HasAvatar
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'role',
        'avatar_url',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'first_login_at' => 'datetime',
            'password' => 'hashed',
            'role' => UserRoleCast::class,
        ];
    }

    public function canEditClinicalRecords(): bool
    {
        return $this->role->canEditClinicalRecords();
    }

    public function canViewSensitiveClinicalData(): bool
    {
        return $this->role->canViewSensitiveClinicalData();
    }

    public function canAccessPanel(Panel $panel): bool
    {
        if ($panel->getId() !== 'app') {
            return false;
        }

        return match ($this->role) {
            UserRole::Admin, UserRole::Patient => true,
        };
    }

    /**
     * @return HasMany<Patient, $this>
     */
    public function patients(): HasMany
    {
        return $this->hasMany(Patient::class);
    }

    public function getFilamentAvatarUrl(): ?string
    {
        if (! $this->avatar_url) {
            return null;
        }

        // مسار نسبي يتبع منفذ الطلب الحالي؛ تجنّباً لاختلاف APP_URL عن `php artisan serve` (مثلاً 8000 مقابل 8001).
        return '/storage/'.ltrim((string) $this->avatar_url, '/');
    }
}
