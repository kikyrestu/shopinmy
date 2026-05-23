<?php
namespace App\Filament\Resources\ProductQuestions\Pages;
use App\Filament\Resources\ProductQuestions\ProductQuestionResource;
use Filament\Resources\Pages\EditRecord;

class EditProductQuestion extends EditRecord
{
    protected static string $resource = ProductQuestionResource::class;
}
