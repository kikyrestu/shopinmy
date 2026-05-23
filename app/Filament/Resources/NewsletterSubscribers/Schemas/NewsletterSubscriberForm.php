<?php

namespace App\Filament\Resources\NewsletterSubscribers\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DateTimePicker;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;

class NewsletterSubscriberForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Subscriber Info')->schema([
                TextInput::make('email')
                    ->email()
                    ->required()
                    ->unique(ignoreRecord: true),
                TextInput::make('name'),
                DateTimePicker::make('subscribed_at')
                    ->default(now()),
                DateTimePicker::make('unsubscribed_at')
                    ->label('Unsubscribed At'),
            ])->columns(2),
        ]);
    }
}
