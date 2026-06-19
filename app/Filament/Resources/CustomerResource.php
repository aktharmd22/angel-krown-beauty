<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CustomerResource\Pages;
use App\Models\Customer;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class CustomerResource extends Resource
{
    protected static ?string $model = Customer::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-circle';

    protected static ?string $navigationGroup = 'Salon';

    protected static ?int $navigationSort = 3;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make()
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('name')->maxLength(120),
                    Forms\Components\TextInput::make('phone')->tel()->required()->maxLength(30),
                    Forms\Components\TextInput::make('email')->email()->maxLength(120),
                    Forms\Components\Textarea::make('notes')->rows(3)->columnSpanFull()
                        ->placeholder('Preferences, allergies, favourite specialist…'),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        $currency = config('salon.currency', 'RM');

        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('name')->weight('bold')->searchable()->placeholder('—'),
                Tables\Columns\TextColumn::make('phone')->searchable(),
                Tables\Columns\TextColumn::make('email')->toggleable()->placeholder('—'),
                Tables\Columns\TextColumn::make('visits_count')
                    ->label('Visits')
                    ->state(fn (Customer $r) => $r->visits_count)
                    ->badge()->color('primary'),
                Tables\Columns\TextColumn::make('total_spent')
                    ->label('Total spent')
                    ->state(fn (Customer $r) => $currency . ' ' . number_format($r->total_spent, 2)),
                Tables\Columns\TextColumn::make('last_visit')
                    ->label('Last visit')
                    ->state(fn (Customer $r) => $r->last_visit ? \Illuminate\Support\Carbon::parse($r->last_visit)->format('d M Y') : '—'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('No customers yet')
            ->emptyStateDescription('Customers are created automatically from bookings.');
    }

    public static function getRelations(): array
    {
        return [
            \App\Filament\Resources\CustomerResource\RelationManagers\BookingsRelationManager::class,
            \App\Filament\Resources\CustomerResource\RelationManagers\InvoicesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCustomers::route('/'),
            'create' => Pages\CreateCustomer::route('/create'),
            'edit' => Pages\EditCustomer::route('/{record}/edit'),
        ];
    }
}
