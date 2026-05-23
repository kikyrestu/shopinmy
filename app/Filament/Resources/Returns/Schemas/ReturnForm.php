<?php

namespace App\Filament\Resources\Returns\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;

class ReturnForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Return Details')->schema([
                Select::make('order_id')
                    ->relationship('order', 'id')
                    ->label('Order ID')
                    ->searchable()
                    ->preload()
                    ->required(),
                Select::make('user_id')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                Textarea::make('reason')
                    ->required()
                    ->rows(3)
                    ->columnSpanFull(),
                Select::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                        'completed' => 'Completed',
                    ])
                    ->default('pending')
                    ->required(),
                Textarea::make('notes')
                    ->label('Admin Notes')
                    ->rows(2)
                    ->columnSpanFull(),
            ])->columns(2),
        ]);
    }
}
