<?php

namespace Tests\Feature;

use App\Filament\Pages\Inbox;
use App\Models\Conversation;
use App\Models\Setting;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Tests\TestCase;

class InboxTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Filament::setCurrentPanel(Filament::getPanel('admin'));
    }

    public function test_inbound_webhook_creates_conversation_and_message(): void
    {
        $this->postJson('/api/whatsapp/webhook', [
            'entry' => [['changes' => [['value' => [
                'contacts' => [['profile' => ['name' => 'Aisha']]],
                'messages' => [[
                    'from' => '60123456789', 'id' => 'wamid.in', 'type' => 'text',
                    'text' => ['body' => 'Hi, are you open today?'],
                ]],
            ]]]]],
        ])->assertOk();

        $conversation = Conversation::first();
        $this->assertNotNull($conversation);
        $this->assertSame('60123456789', $conversation->wa_phone);
        $this->assertSame('Aisha', $conversation->name);
        $this->assertSame(1, $conversation->unread_count);

        $message = $conversation->messages()->first();
        $this->assertSame('inbound', $message->direction);
        $this->assertSame('Hi, are you open today?', $message->body);
    }

    public function test_status_webhook_updates_message_status(): void
    {
        $conversation = Conversation::create(['wa_phone' => '60123', 'name' => 'A']);
        $message = $conversation->messages()->create(['direction' => 'outbound', 'body' => 'hi', 'wa_message_id' => 'wamid.out', 'status' => 'sent']);

        $this->postJson('/api/whatsapp/webhook', [
            'entry' => [['changes' => [['value' => ['statuses' => [['id' => 'wamid.out', 'status' => 'read']]]]]]],
        ])->assertOk();

        $this->assertSame('read', $message->fresh()->status);
    }

    public function test_reply_sends_via_cloud_api_and_stores_outbound(): void
    {
        Setting::current()->update(['whatsapp_enabled' => true, 'wa_phone_number_id' => 'PNID', 'wa_access_token' => 'TOKEN']);
        $conversation = Conversation::create(['wa_phone' => '60123456789', 'name' => 'Aisha', 'unread_count' => 2]);

        Http::fake(['graph.facebook.com/*' => Http::response(['messages' => [['id' => 'wamid.r']]], 200)]);

        $this->actingAs(User::factory()->create());

        Livewire::test(Inbox::class)
            ->call('selectConversation', $conversation->id)
            ->assertSet('selectedId', $conversation->id)
            ->set('reply', 'Yes, we are open till 6pm!')
            ->call('sendReply')
            ->assertSet('reply', '');

        $this->assertSame(0, $conversation->fresh()->unread_count); // marked read on open

        $message = $conversation->messages()->where('direction', 'outbound')->first();
        $this->assertNotNull($message);
        $this->assertSame('Yes, we are open till 6pm!', $message->body);
        $this->assertSame('sent', $message->status);

        Http::assertSent(fn ($r) => $r['type'] === 'text' && $r['to'] === '60123456789');
    }

    public function test_inbox_page_renders(): void
    {
        $this->actingAs(User::factory()->create())->get('/admin/inbox')->assertOk();
    }
}
