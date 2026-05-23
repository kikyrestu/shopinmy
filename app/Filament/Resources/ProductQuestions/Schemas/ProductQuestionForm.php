<?php

namespace App\Filament\Resources\ProductQuestions\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Placeholder;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;

class ProductQuestionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Question')->schema([
                Placeholder::make('product_name')
                    ->label('Product')
                    ->content(fn ($record) => $record?->product?->name ?? '-'),
                Placeholder::make('user_name')
                    ->label('Asked by')
                    ->content(fn ($record) => $record?->user?->name ?? '-'),
                Placeholder::make('question_text')
                    ->label('Question')
                    ->content(fn ($record) => $record?->question ?? '-')
                    ->columnSpanFull(),
            ])->columns(2),

            Section::make('Admin Response')->schema([
                Textarea::make('answer')
                    ->label('Answer')
                    ->rows(4)
                    ->columnSpanFull(),
                Toggle::make('is_published')
                    ->label('Publish Q&A')
                    ->helperText('Show this Q&A on the product page'),
            ]),
        ]);
    }
}
