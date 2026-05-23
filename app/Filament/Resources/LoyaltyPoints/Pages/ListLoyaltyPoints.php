<?php
namespace App\Filament\Resources\LoyaltyPoints\Pages;
use App\Filament\Resources\LoyaltyPoints\LoyaltyPointResource;
use Filament\Resources\Pages\ListRecords;

class ListLoyaltyPoints extends ListRecords
{
    protected static string $resource = LoyaltyPointResource::class;
}
