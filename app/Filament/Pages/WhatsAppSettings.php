<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use App\Models\WhatsappTemplate;
use App\Services\WhatsAppCloud;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class WhatsAppSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static ?string $navigationLabel = 'Settings & API';

    protected static ?string $navigationGroup = 'WhatsApp';

    protected static ?int $navigationSort = 3;

    protected static ?string $title = 'WhatsApp settings';

    protected static string $view = 'filament.pages.whats-app-settings';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill(Setting::current()->attributesToArray());
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('General')
                    ->description('Public site contact numbers.')
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('business_whatsapp_number')
                            ->label('Public WhatsApp number')
                            ->helperText('Country code, no “+”. e.g. 60162674626. Used by the website “Book on WhatsApp” buttons.')
                            ->maxLength(20),
                        Forms\Components\TextInput::make('owner_notify_number')
                            ->label('Fallback alert number')
                            ->helperText('Used only if no WhatsApp Admins are added.')
                            ->maxLength(20),
                    ]),

                Forms\Components\Section::make('Booking automation')
                    ->description('Choose which messages go out when a booking is submitted. Manage them under WhatsApp → Templates.')
                    ->columns(2)
                    ->schema([
                        Forms\Components\Select::make('admin_template_id')
                            ->label('Admin confirmation template')
                            ->options(fn () => WhatsappTemplate::pluck('name', 'id'))
                            ->searchable()
                            ->helperText('Sent to your WhatsApp Admins for every booking.'),
                        Forms\Components\Select::make('customer_template_id')
                            ->label('Customer thank-you template')
                            ->options(fn () => WhatsappTemplate::pluck('name', 'id'))
                            ->searchable()
                            ->helperText('Sent to the customer after they book.'),
                    ]),

                Forms\Components\Section::make('WhatsApp Cloud API (Meta)')
                    ->description('Connect Meta WhatsApp Cloud API to auto-confirm bookings and send reminders. Get these from Meta → WhatsApp → API Setup.')
                    ->columns(2)
                    ->schema([
                        Forms\Components\Toggle::make('whatsapp_enabled')
                            ->label('Enable Cloud API automation')
                            ->helperText('When on, the site sends automatic confirmations & reminders.')
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('wa_phone_number_id')
                            ->label('Phone Number ID')
                            ->helperText('Meta → WhatsApp → API Setup → “Phone number ID”.'),
                        Forms\Components\TextInput::make('wa_business_account_id')
                            ->label('WhatsApp Business Account ID (WABA ID)'),
                        Forms\Components\Textarea::make('wa_access_token')
                            ->label('Access Token')
                            ->helperText('A permanent System User token is recommended for production.')
                            ->rows(3)
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('wa_verify_token')
                            ->label('Webhook Verify Token')
                            ->helperText('Any secret string you choose — enter the SAME value in Meta → Webhooks.'),
                        Forms\Components\TextInput::make('wa_confirm_template')
                            ->label('Confirmation template name')
                            ->placeholder('booking_confirmation'),
                        Forms\Components\TextInput::make('wa_reminder_template')
                            ->label('Reminder template name')
                            ->placeholder('booking_reminder'),
                        Forms\Components\TextInput::make('wa_template_language')
                            ->label('Template language code')
                            ->placeholder('en')
                            ->helperText('Must match your approved templates, e.g. en, en_US, ms.'),
                    ]),
            ])
            ->statePath('data');
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('sendTest')
                ->label('Send test message')
                ->icon('heroicon-o-paper-airplane')
                ->color('gray')
                ->requiresConfirmation()
                ->modalDescription('Sends the Meta "hello_world" template to your owner alert number using the SAVED settings. Save first if you just changed credentials.')
                ->action(function () {
                    $settings = Setting::current();
                    $to = $settings->owner_notify_number ?: $settings->business_whatsapp_number;

                    if (blank($to)) {
                        Notification::make()->title('Add an owner alert number first')->warning()->send();

                        return;
                    }

                    $result = (new WhatsAppCloud($settings))->sendTemplate($to, 'hello_world', [], 'en_US');

                    if ($result['ok']) {
                        Notification::make()->title('Test sent')->body("Check WhatsApp on {$to}.")->success()->send();
                    } else {
                        Notification::make()->title('Test failed')->body($result['error'] ?? 'Unknown error')->danger()->send();
                    }
                }),
        ];
    }

    public function save(): void
    {
        Setting::current()->update($this->form->getState());

        Notification::make()
            ->title('Settings saved')
            ->success()
            ->send();
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label('Save settings')
                ->submit('save'),
        ];
    }
}
