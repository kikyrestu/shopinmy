<?php

namespace App\Filament\Resources\Returns;

use App\Filament\Resources\Returns\Pages\CreateReturn;
use App\Filament\Resources\Returns\Pages\EditReturn;
use App\Filament\Resources\Returns\Pages\ListReturns;
use App\Filament\Resources\Returns\Schemas\ReturnForm;
use App\Filament\Resources\Returns\Tables\ReturnsTable;
use App\Models\ReturnRequest;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class ReturnResource extends Resource
{
    protected static ?string $model = ReturnRequest::class;
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-arrow-uturn-left';
    protected static UnitEnum|string|null $navigationGroup = 'Orders';
    protected static ?string $navigationLabel = 'Returns';
    protected static ?int $navigationSort = 2;

    public static function schema(Schema $schema): Schema
    {
        return ReturnForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ReturnsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListReturns::route('/'),
            'create' => CreateReturn::route('/create'),
            'edit' => EditReturn::route('/{record}/edit'),
        ];
    }
}
