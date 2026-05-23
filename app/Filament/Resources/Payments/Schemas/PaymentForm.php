<?php

namespace App\Filament\Resources\Payments\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;

class PaymentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Payment Details')->schema([
                    Select::make('order_id')
                        ->relationship('order', 'id')
                        ->required()
                        ->searchable()
                        ->preload(),
                    Select::make('type')
                        ->options([
                            'gateway' => 'Gateway (Auto)',
                            'manual' => 'Manual Transfer',
                        ])
                        ->required(),
                    Select::make('method')
                        ->options([
                            'billplz' => 'Billplz (FPX)',
                            'stripe' => 'Stripe',
                            'cod' => 'COD',
                            'manual_transfer' => 'Manual Transfer',
                        ])
                        ->required(),
                    Select::make('status')
                        ->options([
                            'pending' => 'Pending',
                            'paid' => 'Paid',
                            'failed' => 'Failed',
                            'refunded' => 'Refunded',
                        ])
                        ->default('pending')
                        ->required(),
                    TextInput::make('reference')
                        ->maxLength(255),
                    TextInput::make('amount')
                        ->required()
                        ->numeric()
                        ->prefix('RM'),
                ])->columns(2),

                Section::make('Manual Transfer Verification')->schema([
                    FileUpload::make('proof_image')
                        ->image()
                        ->directory('payment-proofs')
                        ->label('Bukti Transfer')
                        ->columnSpanFull(),
                    Textarea::make('rejection_reason')
                        ->label('Rejection Reason')
                        ->rows(2)
                        ->helperText('Isi jika menolak pembayaran'),
                    DateTimePicker::make('verified_at')
                        ->label('Verified At')
                        ->disabled(),
                    Select::make('verified_by')
                        ->relationship('verifier', 'name')
                        ->label('Verified By')
                        ->disabled(),
                ])->columns(2)
                ->collapsed()
                ->visible(fn ($record) => $record?->type === 'manual'),
            ]);
    }
}
