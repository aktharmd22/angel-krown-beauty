<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BookingResource\Pages;
use App\Models\Booking;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class BookingResource extends Resource
{
    protected static ?string $model = Booking::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';

    protected static ?string $navigationLabel = 'Bookings';

    protected static ?string $navigationGroup = 'Salon';

    protected static ?int $navigationSort = 1;

    public static function getNavigationBadge(): ?string
    {
        return (string) static::getModel()::where('status', 'new')->count() ?: null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Booking')
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('name')->maxLength(120),
                    Forms\Components\TextInput::make('phone')->tel()->maxLength(40),
                    Forms\Components\TextInput::make('service')->maxLength(120),
                    Forms\Components\TextInput::make('package')->maxLength(120),
                    Forms\Components\Select::make('location')
                        ->options(['salon' => 'In-salon', 'home' => 'Home service'])
                        ->default('salon')
                        ->required(),
                    Forms\Components\TextInput::make('specialist')->maxLength(120),
                    Forms\Components\TextInput::make('address')->maxLength(255)->columnSpanFull()
                        ->visible(fn (Forms\Get $get) => $get('location') === 'home'),
                    Forms\Components\TextInput::make('preferred_date')->label('Date'),
                    Forms\Components\TextInput::make('preferred_time')->label('Time'),
                ]),

            Forms\Components\Section::make('Status')
                ->columns(2)
                ->schema([
                    Forms\Components\Select::make('status')
                        ->options([
                            'new' => 'New',
                            'confirmed' => 'Confirmed',
                            'done' => 'Done',
                            'cancelled' => 'Cancelled',
                        ])
                        ->default('new')
                        ->required(),
                    Forms\Components\DateTimePicker::make('confirmed_at'),
                    Forms\Components\Textarea::make('message')
                        ->label('WhatsApp message')
                        ->rows(6)
                        ->columnSpanFull(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->poll('30s')
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Received')
                    ->dateTime('d M, H:i')
                    ->sortable(),
                Tables\Columns\TextColumn::make('name')
                    ->weight('bold')
                    ->searchable()
                    ->description(fn (Booking $r) => $r->phone),
                Tables\Columns\TextColumn::make('service')
                    ->searchable()
                    ->description(fn (Booking $r) => $r->package),
                Tables\Columns\TextColumn::make('location')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => $state === 'home' ? 'Home' : 'In-salon')
                    ->color(fn (string $state) => $state === 'home' ? 'gold' : 'gray'),
                Tables\Columns\TextColumn::make('specialist')
                    ->placeholder('No preference')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('preferred_date')
                    ->label('Date')
                    ->description(fn (Booking $r) => $r->preferred_time),
                Tables\Columns\SelectColumn::make('status')
                    ->options([
                        'new' => 'New',
                        'confirmed' => 'Confirmed',
                        'done' => 'Done',
                        'cancelled' => 'Cancelled',
                    ])
                    ->selectablePlaceholder(false)
                    ->width('9rem'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'new' => 'New',
                        'confirmed' => 'Confirmed',
                        'done' => 'Done',
                        'cancelled' => 'Cancelled',
                    ]),
                Tables\Filters\SelectFilter::make('location')
                    ->options(['salon' => 'In-salon', 'home' => 'Home service']),
            ])
            ->actions([
                Tables\Actions\Action::make('invoice')
                    ->label('Invoice')
                    ->icon('heroicon-o-document-currency-dollar')
                    ->color('gray')
                    ->iconButton()
                    ->tooltip('Create invoice')
                    ->action(function (Booking $r) {
                        $invoice = \App\Models\Invoice::create([
                            'booking_id' => $r->id,
                            'customer_name' => $r->name,
                            'customer_phone' => $r->phone,
                            'status' => 'unpaid',
                        ]);
                        $invoice->items()->create([
                            'description' => trim(($r->service ?: 'Service') . ($r->package ? " — {$r->package}" : '')),
                            'quantity' => 1,
                            'unit_price' => 0,
                        ]);
                        $invoice->load('items')->recalculateTotals()->save();

                        return redirect(\App\Filament\Resources\InvoiceResource::getUrl('edit', ['record' => $invoice]));
                    }),
                Tables\Actions\EditAction::make()->iconButton()->tooltip('Edit'),
                Tables\Actions\DeleteAction::make()->iconButton()->tooltip('Delete'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('No bookings yet')
            ->emptyStateDescription('Bookings from the website will appear here.');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBookings::route('/'),
            'create' => Pages\CreateBooking::route('/create'),
            'edit' => Pages\EditBooking::route('/{record}/edit'),
        ];
    }
}
