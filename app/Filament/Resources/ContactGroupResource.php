<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ContactGroupResource\Pages;
use App\Models\ContactGroup;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ContactGroupResource extends Resource
{
    protected static ?string $model = ContactGroup::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $navigationLabel = 'Groups';

    protected static ?string $navigationGroup = 'Marketing';

    protected static ?int $navigationSort = 2;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('name')->required()->maxLength(120),
            Forms\Components\TextInput::make('description')->maxLength(200),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->weight('bold')->searchable(),
                Tables\Columns\TextColumn::make('description')->color('gray')->placeholder('—'),
                Tables\Columns\TextColumn::make('contacts_count')->counts('contacts')->label('Members')->badge()->color('primary'),
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
            ->emptyStateHeading('No groups yet')
            ->emptyStateDescription('Create segments to target broadcasts.');
    }

    public static function getRelations(): array
    {
        return [
            \App\Filament\Resources\ContactGroupResource\RelationManagers\ContactsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListContactGroups::route('/'),
            'create' => Pages\CreateContactGroup::route('/create'),
            'edit' => Pages\EditContactGroup::route('/{record}/edit'),
        ];
    }
}
