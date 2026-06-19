<?php

namespace App\Filament\Resources\ContactGroupResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class ContactsRelationManager extends RelationManager
{
    protected static string $relationship = 'contacts';

    protected static ?string $title = 'Members';

    protected static ?string $icon = 'heroicon-o-identification';

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('name')->maxLength(120),
            Forms\Components\TextInput::make('phone')->tel()->required()->maxLength(30),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('name')->placeholder('—')->searchable(),
                Tables\Columns\TextColumn::make('phone')->searchable(),
                Tables\Columns\IconColumn::make('subscribed')->boolean()->label('Opt-in'),
            ])
            ->headerActions([
                Tables\Actions\AttachAction::make()->preloadRecordSelect()->multiple(),
            ])
            ->actions([
                Tables\Actions\DetachAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DetachBulkAction::make(),
                ]),
            ]);
    }
}
