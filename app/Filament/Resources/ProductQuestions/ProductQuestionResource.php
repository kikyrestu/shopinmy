<?php

namespace App\Filament\Resources\ProductQuestions;

use App\Filament\Resources\ProductQuestions\Pages\ListProductQuestions;
use App\Filament\Resources\ProductQuestions\Pages\EditProductQuestion;
use App\Filament\Resources\ProductQuestions\Schemas\ProductQuestionForm;
use App\Filament\Resources\ProductQuestions\Tables\ProductQuestionsTable;
use App\Models\ProductQuestion;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class ProductQuestionResource extends Resource
{
    protected static ?string $model = ProductQuestion::class;
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-chat-bubble-left-right';
    protected static UnitEnum|string|null $navigationGroup = 'Shop';
    protected static ?string $navigationLabel = 'Product Q&A';
    protected static ?int $navigationSort = 5;

    public static function schema(Schema $schema): Schema
    {
        return ProductQuestionForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ProductQuestionsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListProductQuestions::route('/'),
            'edit' => EditProductQuestion::route('/{record}/edit'),
        ];
    }
}
