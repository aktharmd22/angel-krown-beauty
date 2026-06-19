<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ContactResource\Pages;
use App\Models\Contact;
use App\Models\ContactGroup;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ContactResource extends Resource
{
    protected static ?string $model = Contact::class;

    protected static ?string $navigationIcon = 'heroicon-o-identification';

    protected static ?string $navigationGroup = 'Marketing';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make()
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('name')->maxLength(120),
                    Forms\Components\TextInput::make('phone')
                        ->tel()->required()->maxLength(30)
                        ->helperText('Country code without “+”, or local 0… — both accepted.'),
                    Forms\Components\TextInput::make('email')->email()->maxLength(120),
                    Forms\Components\Toggle::make('subscribed')->label('Marketing opt-in')->default(true),
                    Forms\Components\Select::make('groups')
                        ->relationship('groups', 'name')
                        ->multiple()->preload()->searchable()
                        ->columnSpanFull(),
                    Forms\Components\Textarea::make('notes')->rows(2)->columnSpanFull(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('name')->weight('bold')->searchable()->placeholder('—'),
                Tables\Columns\TextColumn::make('phone')->searchable(),
                Tables\Columns\TextColumn::make('email')->toggleable()->placeholder('—'),
                Tables\Columns\IconColumn::make('subscribed')->boolean()->label('Opt-in'),
                Tables\Columns\TextColumn::make('groups.name')->badge()->label('Groups')->limitList(3),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('groups')->relationship('groups', 'name'),
                Tables\Filters\TernaryFilter::make('subscribed')->label('Opt-in'),
            ])
            ->headerActions([
                Tables\Actions\Action::make('import')
                    ->label('Import contacts')
                    ->icon('heroicon-o-arrow-up-tray')
                    ->form([
                        Forms\Components\Textarea::make('data')
                            ->label('One per line: name, phone')
                            ->rows(8)
                            ->required()
                            ->placeholder("Aisha, 0123456789\nMei Ling, 0198887777"),
                        Forms\Components\Select::make('group_id')
                            ->label('Add to group (optional)')
                            ->options(fn () => ContactGroup::pluck('name', 'id'))
                            ->searchable(),
                    ])
                    ->action(function (array $data) {
                        $count = static::importContacts($data['data'], $data['group_id'] ?? null);
                        Notification::make()->title("Imported {$count} contact(s)")->success()->send();
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->iconButton(),
                Tables\Actions\DeleteAction::make()->iconButton(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('No contacts yet')
            ->emptyStateDescription('Add contacts or import a list to start broadcasting.');
    }

    /** Parse "name, phone" lines and upsert contacts; optionally attach to a group. */
    public static function importContacts(string $raw, $groupId = null): int
    {
        $count = 0;
        foreach (preg_split('/\r\n|\r|\n/', $raw) as $line) {
            $line = trim($line);
            if ($line === '') {
                continue;
            }
            $parts = array_map('trim', explode(',', $line));
            // Allow "phone" only, or "name, phone"
            [$name, $phone] = count($parts) >= 2 ? [$parts[0], $parts[1]] : [null, $parts[0]];
            $norm = Contact::normalizePhone($phone);
            if ($norm === '') {
                continue;
            }
            $contact = Contact::firstOrCreate(['phone' => $norm], ['name' => $name, 'source' => 'import']);
            if ($name && blank($contact->name)) {
                $contact->update(['name' => $name]);
            }
            if ($groupId) {
                $contact->groups()->syncWithoutDetaching([$groupId]);
            }
            $count++;
        }

        return $count;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListContacts::route('/'),
            'create' => Pages\CreateContact::route('/create'),
            'edit' => Pages\EditContact::route('/{record}/edit'),
        ];
    }
}
