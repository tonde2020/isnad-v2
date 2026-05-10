<?php

namespace App\Filament\Resources\Patients\Tables;

use App\Enums\UserRole;
use App\Models\Patient;
use App\Support\PatientTemporaryProfileLink;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\TextInput;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PatientsTable
{
    public static function configure(Table $table): Table
    {
        $showPatientPhi = fn (): bool => auth()->user()?->role !== UserRole::Admin;

        return $table
            ->searchable(auth()->user()?->role !== UserRole::Admin)
            ->defaultSort('created_at', direction: 'desc')
            ->columns([
                TextColumn::make('registry_number')
                    ->label('رقم السجل')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->toggleable(),
                TextColumn::make('full_name')
                    ->label('الاسم')
                    ->searchable()
                    ->sortable()
                    ->visible($showPatientPhi),
                TextColumn::make('phone')
                    ->label('الهاتف')
                    ->searchable()
                    ->visible($showPatientPhi),
                TextColumn::make('national_id')
                    ->label('الرقم الوطني')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->visible($showPatientPhi),
                TextColumn::make('birth_date')
                    ->label('تاريخ الميلاد')
                    ->date()
                    ->sortable()
                    ->visible($showPatientPhi),
                TextColumn::make('blood_type')
                    ->label('فصيلة الدم')
                    ->toggleable()
                    ->visible($showPatientPhi),
                TextColumn::make('created_at')
                    ->label(fn (): string => auth()->user()?->role === UserRole::Admin
                        ? 'تاريخ الانضمام'
                        : 'تاريخ الإنشاء')
                    ->dateTime('Y-m-d H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: auth()->user()?->role !== UserRole::Admin),
                TextColumn::make('user.first_login_at')
                    ->label('أول تسجيل دخول للحساب')
                    ->dateTime('Y-m-d H:i')
                    ->placeholder('لم يُسجَّل بعد')
                    ->visible(fn (): bool => auth()->user()?->role === UserRole::Admin),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make()
                    ->label('عرض'),
                Action::make('generateTemporaryLink')
                    ->label('رابط مؤقت للطبيب')
                    ->icon(Heroicon::OutlinedLink)
                    ->modalHeading('رابط الوصول للملف')
                    ->modalDescription('الرابط موقّع رقمياً وصالح لمدة '.PatientTemporaryProfileLink::EXPIRY_MINUTES.' دقيقة فقط. لا تشاركه في مجموعات عامة.')
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('إغلاق')
                    ->fillForm(function (Patient $record): array {
                        return [
                            'signed_url' => PatientTemporaryProfileLink::publicProfileUrl($record),
                        ];
                    })
                    ->schema([
                        TextInput::make('signed_url')
                            ->label('الرابط')
                            ->disabled()
                            ->copyable()
                            ->columnSpanFull(),
                    ])
                    ->extraModalFooterActions(function (Action $action): array {
                        $record = $action->getRecord();
                        if (! $record instanceof Patient) {
                            return [];
                        }

                        $url = PatientTemporaryProfileLink::publicProfileUrl($record);

                        return [
                            Action::make('openWhatsApp')
                                ->label('مشاركة واتساب')
                                ->icon(Heroicon::OutlinedChatBubbleLeftRight)
                                ->url(PatientTemporaryProfileLink::whatsappShareUrl($url))
                                ->openUrlInNewTab(),
                        ];
                    })
                    ->visible(fn (Patient $record): bool => auth()->user()?->can('generateTemporaryLink', $record) ?? false),
                EditAction::make()
                    ->label('تعديل'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ])->visible(fn (): bool => auth()->user()?->can('deleteAny', Patient::class) ?? false),
            ]);
    }
}
