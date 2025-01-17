<?php

namespace App\Filament\Resources\MoverRequestResource\Pages;

use App\Filament\Resources\MoverRequestResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMoverRequests extends ListRecords
{
    protected static string $resource = MoverRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
