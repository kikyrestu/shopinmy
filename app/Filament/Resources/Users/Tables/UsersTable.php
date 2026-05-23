<?php

namespace App\Filament\Resources\Users\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('email')
                    ->label('Email address')
                    ->searchable(),
                TextColumn::make('email_verified_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    \Filament\Actions\BulkAction::make('send_wa_blast')
                        ->label('Kirim WA Blast')
                        ->icon('heroicon-o-chat-bubble-left-ellipsis')
                        ->color('success')
                        ->form([
                            \Filament\Forms\Components\Select::make('template_id')
                                ->label('Pilih Template')
                                ->options(\App\Models\WaTemplate::where('is_active', true)->pluck('name', 'id'))
                                ->required(),
                        ])
                        ->action(function (\Illuminate\Database\Eloquent\Collection $records, array $data) {
                            $template = \App\Models\WaTemplate::find($data['template_id']);
                            if (!$template) return;
                            
                            $targets = [];
                            $messages = [];
                            
                            foreach ($records as $user) {
                                // Extract phone number, ignore if null
                                $phone = $user->phone; // Assuming user has a phone field
                                if (empty($phone)) continue;
                                
                                $message = str_replace('{name}', $user->name, $template->message);
                                
                                // To use 'data' parameter for personalized delay/message, we can prepare JSON
                                // Or we can send immediately using comma-separated targets if the message is static.
                                // For personalized message {name}, we MUST use data payload or loop.
                                $messages[] = [
                                    'target' => $phone,
                                    'message' => $message,
                                    'delay' => '15-45',
                                ];
                            }
                            
                            if (!empty($messages)) {
                                \App\Services\FonnteService::send('', '', [
                                    'data' => json_encode($messages),
                                ]);
                            }
                            
                            \Filament\Notifications\Notification::make()
                                ->title('WA Blast sedang dikirim ke Antrean')
                                ->success()
                                ->send();
                        }),
                ]),
            ]);
    }
}
