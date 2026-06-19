<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BroadcastResource\Pages;
use App\Models\Broadcast;
use App\Models\ContactGroup;
use App\Models\WhatsappTemplate;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class BroadcastResource extends Resource
{
    protected static ?string $model = Broadcast::class;

    protected static ?string $navigationIcon = 'heroicon-o-megaphone';

    protected static ?string $navigationGroup = 'Marketing';

    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make()
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('name')->required()->maxLength(120)->columnSpanFull(),

                    Forms\Components\Select::make('whatsapp_template_id')
                        ->label('Template')
                        ->options(fn () => WhatsappTemplate::where('meta_status', 'approved')->pluck('name', 'id'))
                        ->searchable()->required()
                        ->helperText('Only Meta-APPROVED templates can be broadcast. Manage them under WhatsApp → Templates.'),

                    Forms\Components\Select::make('audience_type')
                        ->label('Send to')
                        ->options([
                            'group' => 'A contact group',
                            'all_contacts' => 'All contacts (opted-in)',
                            'all_customers' => 'All booking customers',
                        ])
                        ->default('group')->required()->live(),

                    Forms\Components\Select::make('contact_group_id')
                        ->label('Group')
                        ->options(fn () => ContactGroup::pluck('name', 'id'))
                        ->searchable()
                        ->visible(fn (Get $get) => $get('audience_type') === 'group')
                        ->requiredIf('audience_type', 'group'),

                    Forms\Components\DateTimePicker::make('scheduled_at')
                        ->label('Schedule (optional)')
                        ->helperText('Leave empty to send as soon as you press Send.'),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('name')->weight('bold')->searchable(),
                Tables\Columns\TextColumn::make('template.name')->label('Template')->placeholder('—'),
                Tables\Columns\TextColumn::make('audience_type')
                    ->label('Audience')->badge()
                    ->formatStateUsing(fn (string $s) => match ($s) {
                        'all_customers' => 'Customers', 'all_contacts' => 'All contacts', default => 'Group',
                    }),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (string $s) => ucfirst($s))
                    ->color(fn (string $s) => match ($s) {
                        'sent' => 'success', 'sending', 'queued' => 'warning', 'failed' => 'danger', default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('progress')
                    ->label('Sent')
                    ->state(fn (Broadcast $r) => $r->total ? "{$r->sent_count} / {$r->total}" : '—'),
            ])
            ->actions([
                Tables\Actions\Action::make('send')
                    ->label('Send')
                    ->icon('heroicon-o-paper-airplane')
                    ->color('success')
                    ->visible(fn (Broadcast $r) => in_array($r->status, ['draft', 'failed']))
                    ->requiresConfirmation()
                    ->modalHeading('Send broadcast')
                    ->modalDescription('This queues the broadcast and starts sending to every recipient via WhatsApp (50/minute).')
                    ->action(fn (Broadcast $r) => static::queue($r)),
                Tables\Actions\EditAction::make()->iconButton(),
                Tables\Actions\DeleteAction::make()->iconButton(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('No broadcasts yet')
            ->emptyStateDescription('Compose a broadcast to message a group or all customers.');
    }

    public static function queue(Broadcast $broadcast): void
    {
        if (! $broadcast->template || ! $broadcast->template->usesMetaTemplate()) {
            Notification::make()->title('Template not approved')
                ->body('Choose a Meta-approved template before sending.')->danger()->send();

            return;
        }

        $count = $broadcast->queueForSending();

        if ($count === 0) {
            $broadcast->update(['status' => 'draft']);
            Notification::make()->title('No recipients found')->warning()->send();

            return;
        }

        Notification::make()->title("Queued {$count} recipient(s)")
            ->body('Sending starts within a minute (batches of 50).')->success()->send();
    }

    public static function getRelations(): array
    {
        return [
            \App\Filament\Resources\BroadcastResource\RelationManagers\RecipientsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBroadcasts::route('/'),
            'create' => Pages\CreateBroadcast::route('/create'),
            'edit' => Pages\EditBroadcast::route('/{record}/edit'),
        ];
    }
}
