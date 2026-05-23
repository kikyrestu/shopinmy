<?php

namespace App\Filament\Resources\WaTemplates;

use App\Filament\Resources\WaTemplates\Pages\CreateWaTemplate;
use App\Filament\Resources\WaTemplates\Pages\EditWaTemplate;
use App\Filament\Resources\WaTemplates\Pages\ListWaTemplates;
use App\Filament\Resources\WaTemplates\Schemas\WaTemplateForm;
use App\Filament\Resources\WaTemplates\Tables\WaTemplatesTable;
use App\Models\WaTemplate;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class WaTemplateResource extends Resource
{
    protected static ?string $model = WaTemplate::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return WaTemplateForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                \Filament\Tables\Columns\TextColumn::make('name')
                    ->label('Nama Template')
                    ->searchable(),
                \Filament\Tables\Columns\TextColumn::make('event_code')
                    ->label('Kode Event')
                    ->searchable(),
                \Filament\Tables\Columns\IconColumn::make('is_active')
                    ->label('Status')
                    ->boolean(),
                \Filament\Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
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
            'index' => ListWaTemplates::route('/'),
            'create' => CreateWaTemplate::route('/create'),
            'edit' => EditWaTemplate::route('/{record}/edit'),
        ];
    }
}
