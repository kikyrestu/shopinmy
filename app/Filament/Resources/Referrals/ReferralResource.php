<?php

namespace App\Filament\Resources\Referrals;

use App\Filament\Resources\Referrals\Pages\ListReferrals;
use App\Filament\Resources\Referrals\Schemas\ReferralForm;
use App\Filament\Resources\Referrals\Tables\ReferralsTable;
use App\Models\Referral;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class ReferralResource extends Resource
{
    protected static ?string $model = Referral::class;
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-user-group';
    protected static UnitEnum|string|null $navigationGroup = 'Customers';
    protected static ?string $navigationLabel = 'Referrals';
    protected static ?int $navigationSort = 3;

    public static function schema(Schema $schema): Schema
    {
        return ReferralForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ReferralsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListReferrals::route('/'),
        ];
    }
}
