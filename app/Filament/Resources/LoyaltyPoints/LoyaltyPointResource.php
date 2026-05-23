<?php

namespace App\Filament\Resources\LoyaltyPoints;

use App\Filament\Resources\LoyaltyPoints\Pages\ListLoyaltyPoints;
use App\Filament\Resources\LoyaltyPoints\Schemas\LoyaltyPointForm;
use App\Filament\Resources\LoyaltyPoints\Tables\LoyaltyPointsTable;
use App\Models\LoyaltyPoint;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class LoyaltyPointResource extends Resource
{
    protected static ?string $model = LoyaltyPoint::class;
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-star';
    protected static UnitEnum|string|null $navigationGroup = 'Customers';
    protected static ?string $navigationLabel = 'Loyalty Points';
    protected static ?int $navigationSort = 2;

    public static function schema(Schema $schema): Schema
    {
        return LoyaltyPointForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return LoyaltyPointsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListLoyaltyPoints::route('/'),
        ];
    }
}
