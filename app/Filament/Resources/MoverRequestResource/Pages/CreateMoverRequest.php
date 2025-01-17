<?php

namespace App\Filament\Resources\MoverRequestResource\Pages;

use App\Filament\Resources\MoverRequestResource;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateMoverRequest extends CreateRecord
{
    protected static string $resource = MoverRequestResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Mover registered successfully')
            ->body('The mover has been registered successfully');
    }
}
