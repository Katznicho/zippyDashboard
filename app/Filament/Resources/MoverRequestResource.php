<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MoverRequestResource\Pages;
use App\Filament\Resources\MoverRequestResource\RelationManagers;
use App\Models\MoverRequest;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class MoverRequestResource extends Resource
{
    protected static ?string $model = MoverRequest::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Movers';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('app_user_id')
                    ->numeric(),
                Forms\Components\TextInput::make('user_id')
                    ->numeric(),
                Forms\Components\TextInput::make('car_type')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('moved_item')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('pickup_address')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('pickup_lat')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('pickup_long')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('dropoff_address')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('dropoff_lat')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('dropoff_long')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('price')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('payment_method')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('status')
                    ->required()
                    ->maxLength(255)
                    ->default('pending'),
                Forms\Components\DateTimePicker::make('pickup_date')
                    ->required(),
                Forms\Components\DateTimePicker::make('dropoff_date'),
                Forms\Components\Textarea::make('notes')
                    ->maxLength(65535)
                    ->columnSpanFull(),
                Forms\Components\Textarea::make('admin_notes')
                    ->maxLength(65535)
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('app_user_id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('user_id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('car_type')
                    ->searchable(),
                Tables\Columns\TextColumn::make('moved_item')
                    ->searchable(),
                Tables\Columns\TextColumn::make('pickup_address')
                    ->searchable(),
                Tables\Columns\TextColumn::make('pickup_lat')
                    ->searchable(),
                Tables\Columns\TextColumn::make('pickup_long')
                    ->searchable(),
                Tables\Columns\TextColumn::make('dropoff_address')
                    ->searchable(),
                Tables\Columns\TextColumn::make('dropoff_lat')
                    ->searchable(),
                Tables\Columns\TextColumn::make('dropoff_long')
                    ->searchable(),
                Tables\Columns\TextColumn::make('price')
                    ->searchable(),
                Tables\Columns\TextColumn::make('payment_method')
                    ->searchable(),
                Tables\Columns\TextColumn::make('status')
                    ->searchable(),
                Tables\Columns\TextColumn::make('pickup_date')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('dropoff_date')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMoverRequests::route('/'),
            'create' => Pages\CreateMoverRequest::route('/create'),
            'view' => Pages\ViewMoverRequest::route('/{record}'),
            'edit' => Pages\EditMoverRequest::route('/{record}/edit'),
        ];
    }
}
