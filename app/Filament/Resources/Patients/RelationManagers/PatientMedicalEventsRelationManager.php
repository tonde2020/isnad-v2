<?php

namespace App\Filament\Resources\Patients\RelationManagers;

use App\Enums\UserRole;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class PatientMedicalEventsRelationManager extends RelationManager
{
    /** @see MedicalRecordsRelationManager::$isLazy */
    protected static bool $isLazy = false;

    protected static string $relationship = 'medicalEvents';

    protected static ?string $title = 'الخط الزمني الطبي';

    protected static ?string $modelLabel = 'حدث';

    protected static ?string $pluralModelLabel = 'أحداث';

    public function isReadOnly(): bool
    {
        return true;
    }

    public static function canViewForRecord(Model $ownerRecord, string $pageClass): bool
    {
        $user = auth()->user();

        if ($user?->role === UserRole::Patient) {
            return isset($ownerRecord->user_id)
                && (int) $ownerRecord->user_id === (int) $user->getKey()
                && parent::canViewForRecord($ownerRecord, $pageClass);
        }

        if (! $user?->canViewSensitiveClinicalData()) {
            return false;
        }

        return parent::canViewForRecord($ownerRecord, $pageClass);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('event_date')
                    ->label('التاريخ')
                    ->date('Y-m-d')
                    ->sortable(),
                TextColumn::make('event_time')
                    ->label('الوقت')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('event_type')
                    ->label('النوع')
                    ->badge(),
                TextColumn::make('title')
                    ->label('العنوان')
                    ->wrap(),
                TextColumn::make('source')
                    ->label('المصدر')
                    ->badge(),
            ])
            ->defaultSort('event_date', 'desc')
            ->emptyStateHeading('لا توجد أحداث مسجّلة بعد');
    }
}
