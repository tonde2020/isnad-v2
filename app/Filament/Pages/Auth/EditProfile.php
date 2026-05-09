<?php

namespace App\Filament\Pages\Auth;

use Filament\Auth\Pages\EditProfile as BaseEditProfile;
use Filament\Facades\Filament;
use Filament\Forms\Components\FileUpload;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Schema;

class EditProfile extends BaseEditProfile
{
    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                $this->getAvatarFormComponent(),
                $this->getNameFormComponent(),
                $this->getEmailFormComponent(),
                $this->getPasswordFormComponent(),
                $this->getPasswordConfirmationFormComponent(),
                $this->getCurrentPasswordFormComponent(),
            ]);
    }

    protected function getAvatarFormComponent(): Component
    {
        return FileUpload::make('avatar_url')
            ->label('الصورة الشخصية')
            ->avatar()
            ->imageEditor()
            ->disk('public')
            ->directory('avatars')
            ->visibility('public')
            ->maxSize(2048);
    }

    protected function getRedirectUrl(): ?string
    {
        return Filament::getUrl();
    }
}
