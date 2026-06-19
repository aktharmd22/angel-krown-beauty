<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WhatsappAdminResource\Pages;
use App\Models\WhatsappAdmin;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class WhatsappAdminResource extends Resource
{
    protected static ?string $model = WhatsappAdmin::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $navigationLabel = 'WhatsApp Admins';

    protected static ?string $navigationGroup = 'WhatsApp';

    protected static ?int $navigationSort = 2;

    protected const MAX = 3;

    /** Cap at 3 admins. */
    public static function canCreate(): bool
    {
        return parent::canCreate() && static::getModel()::count() < self::MAX;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('name')
                ->placeholder('e.g. Front desk')
                ->maxLength(120),
            Forms\Components\TextInput::make('phone')
                ->label('WhatsApp number')
                ->required()
                ->helperText('Country code, no “+”. e.g. 60123456789')
                ->maxLength(20),
            Forms\Components\Toggle::make('is_active')
                ->label('Receive booking alerts')
                ->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->placeholder('—')->searchable(),
                Tables\Columns\TextColumn::make('phone')->label('WhatsApp number')->searchable(),
                Tables\Columns\ToggleColumn::make('is_active')->label('Active'),
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
            ->emptyStateHeading('No WhatsApp admins yet')
            ->emptyStateDescription('Add up to 3 numbers that receive a WhatsApp alert for each booking.');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListWhatsappAdmins::route('/'),
            'create' => Pages\CreateWhatsappAdmin::route('/create'),
            'edit' => Pages\EditWhatsappAdmin::route('/{record}/edit'),
        ];
    }
}
