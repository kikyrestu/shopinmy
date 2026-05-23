<?php

namespace App\Filament\Resources\Bundles;

use App\Filament\Resources\Bundles\Pages\CreateBundle;
use App\Filament\Resources\Bundles\Pages\EditBundle;
use App\Filament\Resources\Bundles\Pages\ListBundles;
use App\Filament\Resources\Bundles\Schemas\BundleForm;
use App\Filament\Resources\Bundles\Tables\BundlesTable;
use App\Models\Bundle;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class BundleResource extends Resource
{
    protected static ?string $model = Bundle::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedGift;

    protected static UnitEnum|string|null $navigationGroup = 'Marketing';

    public static function form(Schema $schema): Schema
    {
        return BundleForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return BundlesTable::configure($table);
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
            'index' => ListBundles::route('/'),
            'create' => CreateBundle::route('/create'),
            'edit' => EditBundle::route('/{record}/edit'),
        ];
    }
}
