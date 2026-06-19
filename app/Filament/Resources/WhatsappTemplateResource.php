<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WhatsappTemplateResource\Pages;
use App\Models\WhatsappTemplate;
use App\Services\MetaTemplates;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class WhatsappTemplateResource extends Resource
{
    protected static ?string $model = WhatsappTemplate::class;

    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-bottom-center-text';

    protected static ?string $navigationLabel = 'Templates';

    protected static ?string $navigationGroup = 'Marketing';

    protected static ?int $navigationSort = 3;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('name')
                ->required()
                ->maxLength(120)
                ->helperText('For your reference, e.g. “Promo — Raya Offer”.'),

            Forms\Components\Grid::make(2)->schema([
                Forms\Components\Select::make('category')
                    ->options([
                        'MARKETING' => 'Marketing (promos, offers, news)',
                        'UTILITY' => 'Utility (bookings, confirmations, updates)',
                    ])
                    ->default('MARKETING')
                    ->required()
                    ->helperText('Marketing for promotions; Utility for transactional. (OTP/Authentication is not supported here.)'),
                Forms\Components\Select::make('language')
                    ->options(static::languageOptions())
                    ->default('en')
                    ->required()
                    ->searchable()
                    ->native(false)
                    ->helperText('The template language must match Meta’s approved locale.'),
            ]),

            Forms\Components\Fieldset::make('Header (optional)')
                ->columns(2)
                ->schema([
                    Forms\Components\Select::make('header_type')
                        ->label('Type')
                        ->options(['none' => 'None', 'text' => 'Text'])
                        ->default('none')->live(),
                    Forms\Components\TextInput::make('header_text')
                        ->label('Header text')->maxLength(60)
                        ->visible(fn (Get $get) => $get('header_type') === 'text'),
                ]),

            Forms\Components\Textarea::make('body')
                ->label('Message body')
                ->required()
                ->rows(6)
                ->helperText('Use {{name}} for the recipient’s name. Placeholders: ' . collect(WhatsappTemplate::VARIABLES)->map(fn ($v) => '{{' . $v . '}}')->implode('  '))
                ->columnSpanFull(),

            Forms\Components\TextInput::make('footer_text')
                ->label('Footer (optional)')->maxLength(60)
                ->placeholder('e.g. Angel Krown · Reply STOP to opt out'),

            Forms\Components\Repeater::make('buttons')
                ->label('Buttons (optional, up to 3)')
                ->columns(2)
                ->maxItems(3)
                ->addActionLabel('Add button')
                ->columnSpanFull()
                ->schema([
                    Forms\Components\Select::make('type')
                        ->options([
                            'QUICK_REPLY' => 'Quick reply',
                            'URL' => 'Visit website (URL)',
                            'PHONE_NUMBER' => 'Call (phone)',
                        ])->default('QUICK_REPLY')->live(),
                    Forms\Components\TextInput::make('text')->label('Button text')->maxLength(25)->required(),
                    Forms\Components\TextInput::make('url')->label('URL')->url()
                        ->visible(fn (Get $get) => $get('type') === 'URL')->columnSpanFull(),
                    Forms\Components\TextInput::make('phone')->label('Phone (e.g. +60123456789)')
                        ->visible(fn (Get $get) => $get('type') === 'PHONE_NUMBER')->columnSpanFull(),
                ]),

            Forms\Components\Placeholder::make('meta_status_info')
                ->label('Meta approval status')
                ->content(fn (?WhatsappTemplate $record) => $record
                    ? strtoupper($record->meta_status) . ($record->meta_rejected_reason ? ' — ' . $record->meta_rejected_reason : '')
                    : 'Will be submitted to Meta for approval when you save.')
                ->visible(fn (?WhatsappTemplate $record) => (bool) $record),
        ]);
    }

    /** WhatsApp-supported template locales, Malaysia-relevant ones first. */
    public static function languageOptions(): array
    {
        return [
            'en' => 'English (en)',
            'en_US' => 'English — US (en_US)',
            'en_GB' => 'English — UK (en_GB)',
            'ms' => 'Malay (ms)',
            'zh_CN' => 'Chinese — Simplified (zh_CN)',
            'zh_HK' => 'Chinese — Hong Kong (zh_HK)',
            'zh_TW' => 'Chinese — Traditional (zh_TW)',
            'ta' => 'Tamil (ta)',
            'hi' => 'Hindi (hi)',
            'id' => 'Indonesian (id)',
            'th' => 'Thai (th)',
            'vi' => 'Vietnamese (vi)',
            'ar' => 'Arabic (ar)',
            'bn' => 'Bengali (bn)',
            'fil' => 'Filipino (fil)',
            'ja' => 'Japanese (ja)',
            'ko' => 'Korean (ko)',
            'fr' => 'French (fr)',
            'de' => 'German (de)',
            'es' => 'Spanish (es)',
            'es_ES' => 'Spanish — Spain (es_ES)',
            'pt_BR' => 'Portuguese — Brazil (pt_BR)',
            'pt_PT' => 'Portuguese — Portugal (pt_PT)',
            'it' => 'Italian (it)',
            'nl' => 'Dutch (nl)',
            'ru' => 'Russian (ru)',
            'tr' => 'Turkish (tr)',
            'ur' => 'Urdu (ur)',
        ];
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->weight('bold')->searchable(),
                Tables\Columns\TextColumn::make('body')->limit(50)->wrap()->color('gray'),
                Tables\Columns\TextColumn::make('category')->badge()->color('gray'),
                Tables\Columns\TextColumn::make('meta_status')
                    ->label('Meta status')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => strtoupper($state))
                    ->color(fn (string $state) => match ($state) {
                        'approved' => 'success',
                        'pending' => 'warning',
                        'rejected' => 'danger',
                        default => 'gray',
                    })
                    ->tooltip(fn (WhatsappTemplate $r) => $r->meta_rejected_reason),
            ])
            ->actions([
                Tables\Actions\Action::make('submit')
                    ->label('Submit to Meta')
                    ->icon('heroicon-o-paper-airplane')
                    ->color('primary')
                    ->requiresConfirmation()
                    ->modalDescription('Send this template to Meta for approval. You can use it for customers once Meta approves it.')
                    ->action(fn (WhatsappTemplate $record) => static::runMeta($record, 'submit')),
                Tables\Actions\Action::make('sync')
                    ->label('Refresh status')
                    ->icon('heroicon-o-arrow-path')
                    ->color('gray')
                    ->action(fn (WhatsappTemplate $record) => static::runMeta($record, 'syncStatus'))
                    ->visible(fn (WhatsappTemplate $r) => filled($r->meta_template_id)),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('No templates yet')
            ->emptyStateDescription('Create messages for booking confirmations and customer thank-yous.');
    }

    /** Auto-submit on save when the Cloud API is configured (silent otherwise). */
    public static function autoSubmit(WhatsappTemplate $record): void
    {
        if (! app(MetaTemplates::class)->configured()) {
            return;
        }

        static::runMeta($record, 'submit');
    }

    /** Run a MetaTemplates method and surface the result as a notification. */
    public static function runMeta(WhatsappTemplate $record, string $method): void
    {
        $result = app(MetaTemplates::class)->{$method}($record);

        if ($result['ok']) {
            Notification::make()
                ->title('Meta status: ' . strtoupper($result['status'] ?? 'updated'))
                ->success()
                ->send();
        } else {
            Notification::make()
                ->title('Could not reach Meta')
                ->body($result['error'] ?? 'Unknown error')
                ->danger()
                ->send();
        }
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListWhatsappTemplates::route('/'),
            'create' => Pages\CreateWhatsappTemplate::route('/create'),
            'edit' => Pages\EditWhatsappTemplate::route('/{record}/edit'),
        ];
    }
}
