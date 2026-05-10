<?php

namespace App\Filament\Resources\Patients;

use App\Enums\UserRole;
use App\Filament\Resources\Patients\Pages\CreatePatient;
use App\Filament\Resources\Patients\Pages\EditPatient;
use App\Filament\Resources\Patients\Pages\ListPatients;
use App\Filament\Resources\Patients\Pages\ReviewMedicalRecordExtraction;
use App\Filament\Resources\Patients\Pages\ViewPatient;
use App\Filament\Resources\Patients\RelationManagers\MedicalRecordsRelationManager;
use App\Filament\Resources\Patients\RelationManagers\PatientMedicalEventsRelationManager;
use App\Filament\Resources\Patients\Schemas\PatientForm;
use App\Filament\Resources\Patients\Tables\PatientsTable;
use App\Models\Patient;
use App\Models\User;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class PatientResource extends Resource
{
    protected static ?string $model = Patient::class;

    protected static ?string $recordTitleAttribute = 'full_name';

    protected static ?string $modelLabel = 'مريض';

    protected static ?string $pluralModelLabel = 'المرضى';

    protected static ?string $navigationLabel = 'المرضى';

    protected static string|\UnitEnum|null $navigationGroup = 'السجلات الطبية';

    protected static ?int $navigationSort = 10;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUser;

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        $user = auth()->user();
        if ($user instanceof User && $user->role === UserRole::Patient) {
            $query->where('user_id', $user->getKey());
        }

        if ($user instanceof User && $user->role === UserRole::Admin) {
            $query->with([
                'user' => fn ($relation) => $relation->select(['users.id', 'users.first_login_at']),
            ]);
        }

        return $query;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->role->canEditClinicalRecords() ?? false;
    }

    public static function getModelLabel(): string
    {
        return auth()->user()?->role === UserRole::Patient
            ? 'ملفي الطبي'
            : 'مريض';
    }

    public static function getPluralModelLabel(): string
    {
        return auth()->user()?->role === UserRole::Patient
            ? 'ملفي الطبي'
            : 'المرضى';
    }

    public static function getNavigationLabel(): string
    {
        return auth()->user()?->role === UserRole::Patient
            ? 'ملفي الطبي'
            : 'المرضى';
    }

    public static function getNavigationGroup(): ?string
    {
        if (auth()->user()?->role === UserRole::Patient) {
            return null;
        }

        return 'السجلات الطبية';
    }

    public static function form(Schema $schema): Schema
    {
        if (auth()->user()?->role === UserRole::Admin) {
            return PatientForm::configureOperationalAdminShell($schema);
        }

        return PatientForm::configure($schema);
    }

    public static function getGloballySearchableAttributes(): array
    {
        if (auth()->user()?->role === UserRole::Admin) {
            return ['registry_number'];
        }

        return parent::getGloballySearchableAttributes();
    }

    public static function getRecordTitle(?Model $record): string|Htmlable|null
    {
        if ($record instanceof Patient && auth()->user()?->role === UserRole::Admin) {
            return filled($record->registry_number)
                ? (string) $record->registry_number
                : 'ملف #'.$record->getKey();
        }

        return parent::getRecordTitle($record);
    }

    public static function table(Table $table): Table
    {
        return PatientsTable::configure($table);
    }

    public static function getRelations(): array
    {
        if (auth()->user()?->role === UserRole::Admin) {
            return [];
        }

        return [
            MedicalRecordsRelationManager::class,
            PatientMedicalEventsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPatients::route('/'),
            'create' => CreatePatient::route('/create'),
            'view' => ViewPatient::route('/{record}'),
            'edit' => EditPatient::route('/{record}/edit'),
            'reviewMedicalRecordExtraction' => ReviewMedicalRecordExtraction::route('/{record}/medical-records/{medicalRecord}/review-extraction'),
        ];
    }
}
