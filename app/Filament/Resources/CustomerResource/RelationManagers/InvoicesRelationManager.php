<?php

namespace App\Filament\Resources\CustomerResource\RelationManagers;

use App\Models\Invoice;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class InvoicesRelationManager extends RelationManager
{
    protected static string $relationship = 'invoices';

    protected static ?string $title = 'Invoices';

    protected static ?string $icon = 'heroicon-o-document-currency-dollar';

    public function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('invoice_number')
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('invoice_number')->label('Invoice #')->weight('bold'),
                Tables\Columns\TextColumn::make('issue_date')->date('d M Y'),
                Tables\Columns\TextColumn::make('total')->money('myr'),
                Tables\Columns\TextColumn::make('status')->badge()->color(fn (string $state) => match ($state) {
                    'paid' => 'success', 'unpaid' => 'warning', 'cancelled' => 'danger', default => 'gray',
                }),
            ])
            ->actions([
                Tables\Actions\Action::make('pdf')
                    ->label('PDF')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('gray')
                    ->url(fn (Invoice $r) => route('invoices.pdf', $r))
                    ->openUrlInNewTab(),
            ])
            ->paginated([5, 10]);
    }
}
