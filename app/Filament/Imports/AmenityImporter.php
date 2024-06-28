<?php

namespace App\Filament\Imports;

use App\Models\Amenity;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;

class AmenityImporter extends Importer
{
    protected static ?string $model = Amenity::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('name')
                ->requiredMapping()
                ->rules(['required', 'max:255']),
            ImportColumn::make('description')
                ->rules(['max:255']),
            ImportColumn::make('image')
                ->rules(['max:255']),
        ];
    }

    public function resolveRecord(): ?Amenity
    {
        // return Amenity::firstOrNew([
        //     // Update existing records, matching them by `$this->data['column_name']`
        //     'email' => $this->data['email'],
        // ]);

        return new Amenity();
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your amenity import has completed and ' . number_format($import->successful_rows) . ' ' . str('row')->plural($import->successful_rows) . ' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to import.';
        }

        return $body;
    }
}
