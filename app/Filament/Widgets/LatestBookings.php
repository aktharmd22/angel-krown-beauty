<?php

namespace App\Filament\Widgets;

use App\Models\Booking;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class LatestBookings extends BaseWidget
{
    protected static ?int $sort = 5;

    protected int|string|array $columnSpan = 'full';

    protected static ?string $heading = 'Recent bookings';

    public function table(Table $table): Table
    {
        return $table
            ->query(Booking::query()->latest()->limit(6))
            ->columns([
                Tables\Columns\TextColumn::make('created_at')->label('Received')->dateTime('d M, H:i'),
                Tables\Columns\TextColumn::make('name')->weight('bold')->description(fn (Booking $r) => $r->phone),
                Tables\Columns\TextColumn::make('service')->description(fn (Booking $r) => $r->package),
                Tables\Columns\TextColumn::make('specialist')->placeholder('No preference'),
                Tables\Columns\TextColumn::make('status')->badge()->color(fn (string $state) => match ($state) {
                    'confirmed' => 'success', 'done' => 'gray', 'cancelled' => 'danger', default => 'warning',
                }),
            ])
            ->paginated(false);
    }
}
