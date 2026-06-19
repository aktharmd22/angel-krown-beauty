<?php

namespace App\Filament\Resources\BroadcastResource\RelationManagers;

use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class RecipientsRelationManager extends RelationManager
{
    protected static string $relationship = 'recipients';

    protected static ?string $title = 'Recipients';

    protected static ?string $icon = 'heroicon-o-users';

    public function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('phone')
            ->columns([
                Tables\Columns\TextColumn::make('name')->placeholder('—')->searchable(),
                Tables\Columns\TextColumn::make('phone')->searchable(),
                Tables\Columns\TextColumn::make('status')->badge()->color(fn (string $s) => match ($s) {
                    'sent' => 'success', 'failed' => 'danger', default => 'gray',
                }),
                Tables\Columns\TextColumn::make('error')->color('danger')->placeholder('—')->wrap()->toggleable(),
                Tables\Columns\TextColumn::make('sent_at')->dateTime('d M, H:i')->placeholder('—'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')->options(['pending' => 'Pending', 'sent' => 'Sent', 'failed' => 'Failed']),
            ])
            ->paginated([25, 50, 100]);
    }
}
