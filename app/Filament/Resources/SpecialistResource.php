<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SpecialistResource\Pages;
use App\Models\Specialist;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class SpecialistResource extends Resource
{
    protected static ?string $model = Specialist::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationLabel = 'Specialists';

    protected static ?string $navigationGroup = 'Salon';

    protected static ?int $navigationSort = 4;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make()
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('name')
                        ->required()
                        ->maxLength(120),
                    Forms\Components\TextInput::make('role')
                        ->placeholder('e.g. Lead Nail Artist')
                        ->maxLength(120),
                    Forms\Components\Textarea::make('blurb')
                        ->label('Short description')
                        ->rows(2)
                        ->maxLength(255)
                        ->columnSpanFull(),
                    Forms\Components\FileUpload::make('photo')
                        ->image()
                        ->imageEditor()
                        ->disk('uploads')
                        ->directory('specialists')
                        ->visibility('public')
                        ->maxSize(4096)
                        ->helperText('Square photo works best.')
                        ->columnSpanFull(),
                    Forms\Components\Toggle::make('is_active')
                        ->label('Show on website')
                        ->default(true),
                    Forms\Components\TextInput::make('sort_order')
                        ->numeric()
                        ->default(0)
                        ->helperText('Lower numbers appear first.'),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->columns([
                Tables\Columns\ImageColumn::make('photo')
                    ->label('Photo')
                    ->getStateUsing(fn (Specialist $r) => $r->photo_url ? url($r->photo_url) : null)
                    ->circular(),
                Tables\Columns\TextColumn::make('name')
                    ->weight('bold')
                    ->searchable(),
                Tables\Columns\TextColumn::make('role')
                    ->searchable(),
                Tables\Columns\ToggleColumn::make('is_active')
                    ->label('On website'),
                Tables\Columns\TextColumn::make('sort_order')
                    ->label('Order')
                    ->sortable(),
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
            ->emptyStateHeading('No specialists yet')
            ->emptyStateDescription('Add your team — they appear on the website and in the booking form.');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSpecialists::route('/'),
            'create' => Pages\CreateSpecialist::route('/create'),
            'edit' => Pages\EditSpecialist::route('/{record}/edit'),
        ];
    }
}
