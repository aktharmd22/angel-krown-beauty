<?php

namespace App\Filament\Pages;

use App\Models\Conversation;
use App\Models\Message;
use App\Services\WhatsAppCloud;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;

class Inbox extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-left-right';

    protected static ?string $navigationLabel = 'Inbox';

    protected static ?string $navigationGroup = 'WhatsApp';

    protected static ?int $navigationSort = 1;

    protected static string $view = 'filament.pages.inbox';

    public ?int $selectedId = null;

    public string $reply = '';

    public string $search = '';

    public static function getNavigationBadge(): ?string
    {
        $unread = Conversation::where('unread_count', '>', 0)->count();

        return $unread ? (string) $unread : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'danger';
    }

    #[Computed]
    public function conversations(): Collection
    {
        return Conversation::query()
            ->when($this->search !== '', fn ($q) => $q->where(fn ($w) => $w
                ->where('name', 'like', "%{$this->search}%")
                ->orWhere('wa_phone', 'like', "%{$this->search}%")))
            ->orderByDesc('last_message_at')
            ->orderByDesc('updated_at')
            ->limit(100)
            ->get();
    }

    #[Computed]
    public function conversation(): ?Conversation
    {
        return $this->selectedId ? Conversation::find($this->selectedId) : null;
    }

    #[Computed]
    public function messages(): Collection
    {
        if (! $this->selectedId) {
            return collect();
        }

        return Message::where('conversation_id', $this->selectedId)
            ->orderBy('created_at')
            ->limit(300)
            ->get();
    }

    public function selectConversation(int $id): void
    {
        $this->selectedId = $id;
        $this->reply = '';
        Conversation::whereKey($id)->update(['unread_count' => 0]);
        unset($this->conversations, $this->conversation, $this->messages);
        $this->dispatch('scroll-bottom');
    }

    public function sendReply(): void
    {
        $conversation = $this->conversation;
        $text = trim($this->reply);

        if (! $conversation || $text === '') {
            return;
        }

        $result = app(WhatsAppCloud::class)->sendText($conversation->wa_phone, $text);

        $conversation->messages()->create([
            'direction' => 'outbound',
            'type' => 'text',
            'body' => $text,
            'wa_message_id' => $result['message_id'] ?? null,
            'status' => $result['ok'] ? 'sent' : 'failed',
            'error' => $result['ok'] ? null : ($result['error'] ?? null),
        ]);

        $conversation->forceFill([
            'last_message' => Str::limit($text, 120),
            'last_message_at' => now(),
        ])->save();

        $this->reply = '';
        unset($this->conversations, $this->messages);

        if (! $result['ok']) {
            Notification::make()
                ->title('Message not delivered')
                ->body(($result['error'] ?? 'Failed.') . ' — replies only work within 24h of the customer’s last message.')
                ->danger()
                ->send();
        }

        $this->dispatch('scroll-bottom');
    }
}
