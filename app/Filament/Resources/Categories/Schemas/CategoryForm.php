<?php

namespace App\Filament\Resources\Categories\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Utilities\Set;
use Illuminate\Support\Str;
use Filament\Schemas\Schema;

class CategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('parent_id')
                    ->relationship('parent', 'name')
                    ->searchable()
                    ->preload(),
                TextInput::make('name')
                    ->required()
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn (Set $set, ?string $state) => $set('slug', Str::slug($state))),
                TextInput::make('slug')
                    ->required()
                    ->unique(ignoreRecord: true),
                Select::make('icon')
                    ->label('Category Icon (Phosphor)')
                    ->options([
                        'ph-tag' => 'Tag',
                        'ph-t-shirt' => 'T-Shirt',
                        'ph-sneaker' => 'Sneaker',
                        'ph-device-mobile' => 'Mobile Device',
                        'ph-laptop' => 'Laptop',
                        'ph-headphones' => 'Headphones',
                        'ph-watch' => 'Watch',
                        'ph-handbag' => 'Handbag',
                        'ph-cooking-pot' => 'Cooking',
                        'ph-armchair' => 'Furniture',
                        'ph-shopping-basket' => 'Groceries',
                        'ph-megaphone' => 'Promo',
                        'ph-star' => 'Star',
                        'ph-heart' => 'Heart',
                        'ph-gift' => 'Gift',
                        'ph-baby' => 'Baby',
                        'ph-baseball-cap' => 'Cap',
                        'ph-bicycle' => 'Bicycle',
                        'ph-books' => 'Books',
                        'ph-camera' => 'Camera',
                        'ph-car' => 'Automotive',
                        'ph-coffee' => 'Coffee',
                        'ph-first-aid' => 'Health',
                        'ph-game-controller' => 'Gaming',
                        'ph-music-notes' => 'Music',
                        'ph-paint-brush' => 'Art',
                        'ph-paw-print' => 'Pets',
                        'ph-plant' => 'Plants',
                        'ph-scissors' => 'Beauty/Salon',
                        'ph-sparkle' => 'Beauty/Cosmetics',
                        'ph-wrench' => 'Tools',
                        'ph-video-camera' => 'Video',
                    ])
                    ->searchable()
                    ->allowHtml()
                    ->getOptionLabelFromRecordUsing(fn ($value) => "<div class='flex items-center gap-2'><i class='ph {$value} text-lg'></i> <span>{$value}</span></div>")
                    ->nullable()
                    ->helperText('Type to search for an icon. E.g. "ph-t-shirt".'),
                FileUpload::make('image')
                    ->image()
                    ->directory('categories'),
            ]);
    }
}
