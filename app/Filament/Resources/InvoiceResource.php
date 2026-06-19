<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InvoiceResource\Pages;
use App\Models\Invoice;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class InvoiceResource extends Resource
{
    protected static ?string $model = Invoice::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-currency-dollar';

    protected static ?string $navigationGroup = 'Salon';

    protected static ?int $navigationSort = 2;

    protected static ?string $recordTitleAttribute = 'invoice_number';

    public static function getNavigationBadge(): ?string
    {
        return (string) static::getModel()::where('status', 'unpaid')->count() ?: null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function form(Form $form): Form
    {
        $currency = config('salon.currency', 'RM');

        return $form->schema([
            Forms\Components\Grid::make(3)->schema([
                Forms\Components\Section::make('Customer')
                    ->columnSpan(2)
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('customer_name')->required()->maxLength(120),
                        Forms\Components\TextInput::make('customer_phone')->tel()->maxLength(40),
                        Forms\Components\TextInput::make('customer_email')->email()->maxLength(120)->columnSpanFull(),
                    ]),

                Forms\Components\Section::make('Invoice')
                    ->columnSpan(1)
                    ->schema([
                        Forms\Components\TextInput::make('invoice_number')
                            ->placeholder('Auto-generated')
                            ->disabled()
                            ->dehydrated(false),
                        Forms\Components\DatePicker::make('issue_date')
                            ->default(now())
                            ->required(),
                        Forms\Components\Select::make('status')
                            ->options([
                                'draft' => 'Draft',
                                'unpaid' => 'Unpaid',
                                'paid' => 'Paid',
                                'cancelled' => 'Cancelled',
                            ])
                            ->default('unpaid')
                            ->required(),
                        Forms\Components\Select::make('payment_method')
                            ->options([
                                'cash' => 'Cash',
                                'card' => 'Card',
                                'ewallet' => 'E-wallet',
                                'bank' => 'Bank transfer',
                            ])
                            ->placeholder('—'),
                    ]),
            ]),

            Forms\Components\Section::make('Items')
                ->schema([
                    Forms\Components\Repeater::make('items')
                        ->relationship()
                        ->reorderable()
                        ->defaultItems(1)
                        ->columns(12)
                        ->schema([
                            Forms\Components\TextInput::make('description')
                                ->required()
                                ->columnSpan(6),
                            Forms\Components\TextInput::make('quantity')
                                ->numeric()->default(1)->minValue(0)->step('0.01')
                                ->required()->live(onBlur: true)
                                ->columnSpan(2),
                            Forms\Components\TextInput::make('unit_price')
                                ->numeric()->default(0)->prefix($currency)
                                ->required()->live(onBlur: true)
                                ->columnSpan(2),
                            Forms\Components\Placeholder::make('amount')
                                ->label('Amount')
                                ->content(fn (Get $get) => $currency . ' ' . number_format(
                                    (float) $get('quantity') * (float) $get('unit_price'), 2
                                ))
                                ->columnSpan(2),
                        ])
                        ->itemLabel(fn (array $state) => $state['description'] ?? null)
                        ->addActionLabel('Add item'),
                ]),

            Forms\Components\Grid::make(2)->schema([
                Forms\Components\Section::make('Discount & Tax')
                    ->columns(2)
                    ->schema([
                        Forms\Components\Select::make('discount_type')
                            ->options(['amount' => "Amount ($currency)", 'percent' => 'Percent (%)'])
                            ->default('amount')->live(),
                        Forms\Components\TextInput::make('discount_value')
                            ->numeric()->default(0)->minValue(0)->live(onBlur: true),
                        Forms\Components\TextInput::make('tax_rate')
                            ->label('Tax rate (%)')->numeric()->default(0)->minValue(0)->suffix('%')->live(onBlur: true)
                            ->helperText('e.g. 6 for SST. Leave 0 for none.'),
                        Forms\Components\TextInput::make('tax_label')->default('SST'),
                    ]),

                Forms\Components\Section::make('Summary')
                    ->schema([
                        Forms\Components\Placeholder::make('subtotal_d')->label('Subtotal')
                            ->content(fn (Get $get) => static::fmt(static::calc($get)['subtotal'])),
                        Forms\Components\Placeholder::make('discount_d')->label('Discount')
                            ->content(fn (Get $get) => '− ' . static::fmt(static::calc($get)['discount'])),
                        Forms\Components\Placeholder::make('tax_d')
                            ->label(fn (Get $get) => trim(($get('tax_label') ?: 'Tax') . ' (' . (float) $get('tax_rate') . '%)'))
                            ->content(fn (Get $get) => static::fmt(static::calc($get)['tax'])),
                        Forms\Components\Placeholder::make('total_d')->label('Total')
                            ->content(fn (Get $get) => static::fmt(static::calc($get)['total'])),
                    ]),
            ]),

            Forms\Components\Textarea::make('notes')
                ->rows(2)
                ->placeholder('Thank you for visiting Angel Krown!')
                ->columnSpanFull(),
        ]);
    }

    /** Live totals from the form state. */
    public static function calc(Get $get): array
    {
        $subtotal = collect($get('items') ?? [])->sum(
            fn ($i) => (float) ($i['quantity'] ?? 0) * (float) ($i['unit_price'] ?? 0)
        );
        $discount = $get('discount_type') === 'percent'
            ? $subtotal * ((float) $get('discount_value')) / 100
            : (float) $get('discount_value');
        $discount = min($discount, $subtotal);
        $taxable = $subtotal - $discount;
        $tax = $taxable * ((float) $get('tax_rate')) / 100;

        return [
            'subtotal' => $subtotal,
            'discount' => $discount,
            'tax' => $tax,
            'total' => $taxable + $tax,
        ];
    }

    public static function fmt($v): string
    {
        return config('salon.currency', 'RM') . ' ' . number_format((float) $v, 2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('invoice_number')->label('Invoice #')->weight('bold')->searchable(),
                Tables\Columns\TextColumn::make('customer_name')->searchable()->description(fn (Invoice $r) => $r->customer_phone),
                Tables\Columns\TextColumn::make('issue_date')->date('d M Y')->sortable(),
                Tables\Columns\TextColumn::make('total')->money('myr')->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => ucfirst($state))
                    ->color(fn (string $state) => match ($state) {
                        'paid' => 'success',
                        'unpaid' => 'warning',
                        'cancelled' => 'danger',
                        default => 'gray',
                    }),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')->options([
                    'draft' => 'Draft', 'unpaid' => 'Unpaid', 'paid' => 'Paid', 'cancelled' => 'Cancelled',
                ]),
            ])
            ->actions([
                Tables\Actions\Action::make('pdf')
                    ->label('PDF')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('gray')
                    ->url(fn (Invoice $r) => route('invoices.pdf', $r))
                    ->openUrlInNewTab(),
                Tables\Actions\Action::make('markPaid')
                    ->label('Mark paid')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (Invoice $r) => $r->status !== 'paid')
                    ->requiresConfirmation()
                    ->action(fn (Invoice $r) => $r->markPaid()),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('No invoices yet')
            ->emptyStateDescription('Create an invoice, or generate one from a booking.');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListInvoices::route('/'),
            'create' => Pages\CreateInvoice::route('/create'),
            'edit' => Pages\EditInvoice::route('/{record}/edit'),
        ];
    }
}
