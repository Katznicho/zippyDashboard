<?php

namespace App\Filament\Resources\MoverRequestResource\Pages;

use App\Filament\Resources\MoverRequestResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMoverRequest extends EditRecord
{
    protected static string $resource = MoverRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
