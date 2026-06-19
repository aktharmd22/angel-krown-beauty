<?php

namespace App\Filament\Resources\CustomerResource\RelationManagers;

use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class BookingsRelationManager extends RelationManager
{
    protected static string $relationship = 'bookings';

    protected static ?string $title = 'Booking history';

    protected static ?string $icon = 'heroicon-o-calendar-days';

    public function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('service')
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('created_at')->label('Received')->dateTime('d M Y'),
                Tables\Columns\TextColumn::make('service')->description(fn ($r) => $r->package),
                Tables\Columns\TextColumn::make('preferred_date')->label('Date')->description(fn ($r) => $r->preferred_time),
                Tables\Columns\TextColumn::make('specialist')->placeholder('—'),
                Tables\Columns\TextColumn::make('status')->badge()->color(fn (string $state) => match ($state) {
                    'confirmed' => 'success', 'done' => 'gray', 'cancelled' => 'danger', default => 'warning',
                }),
            ])
            ->paginated([5, 10]);
    }
}
