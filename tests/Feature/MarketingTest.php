<?php

namespace Tests\Feature;

use App\Filament\Resources\ContactResource;
use App\Models\Broadcast;
use App\Models\Contact;
use App\Models\ContactGroup;
use App\Models\Setting;
use App\Models\User;
use App\Models\WhatsappTemplate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class MarketingTest extends TestCase
{
    use RefreshDatabase;

    public function test_import_parses_normalizes_and_dedupes(): void
    {
        // Both lines normalise to the same number → one contact.
        ContactResource::importContacts("Aisha, 0123456789\nMei, 012-345 6789");

        $this->assertSame(1, Contact::count());
        $this->assertSame('60123456789', Contact::first()->phone);
        $this->assertSame('Aisha', Contact::first()->name);
    }

    public function test_group_audience_builds_recipients(): void
    {
        $group = ContactGroup::create(['name' => 'VIP']);
        $a = Contact::create(['name' => 'A', 'phone' => '60111', 'subscribed' => true]);
        $b = Contact::create(['name' => 'B', 'phone' => '60222', 'subscribed' => true]);
        $c = Contact::create(['name' => 'C', 'phone' => '60333', 'subscribed' => false]);
        $group->contacts()->attach([$a->id, $b->id, $c->id]);

        $broadcast = Broadcast::create(['name' => 'X', 'audience_type' => 'group', 'contact_group_id' => $group->id]);
        $broadcast->queueForSending();

        $this->assertSame(2, $broadcast->fresh()->total); // unsubscribed excluded
        $this->assertSame('queued', $broadcast->fresh()->status);
    }

    public function test_template_components_include_header_footer_buttons(): void
    {
        $tpl = WhatsappTemplate::create([
            'name' => 'Promo', 'body' => 'Hi {{name}}, enjoy 20% off!', 'language' => 'en', 'category' => 'MARKETING',
            'header_type' => 'text', 'header_text' => 'Special Offer', 'footer_text' => 'Reply STOP to opt out',
            'buttons' => [
                ['type' => 'URL', 'text' => 'Book now', 'url' => 'https://angelkrown.com'],
                ['type' => 'QUICK_REPLY', 'text' => 'Stop'],
            ],
        ]);

        $components = $tpl->toMetaComponents();
        $this->assertEqualsCanonicalizing(['HEADER', 'BODY', 'FOOTER', 'BUTTONS'], array_column($components, 'type'));

        $body = collect($components)->firstWhere('type', 'BODY');
        $this->assertStringContainsString('{{1}}', $body['text']);
        $this->assertSame('Aisha', $body['example']['body_text'][0][0]);

        $buttons = collect($components)->firstWhere('type', 'BUTTONS')['buttons'];
        $this->assertSame('URL', $buttons[0]['type']);
        $this->assertSame('https://angelkrown.com', $buttons[0]['url']);
    }

    public function test_broadcast_command_sends_via_cloud_api(): void
    {
        Setting::current()->update(['whatsapp_enabled' => true, 'wa_phone_number_id' => 'PNID', 'wa_access_token' => 'TOKEN']);
        $tpl = WhatsappTemplate::create([
            'name' => 'Promo', 'body' => 'Hi {{name}}!', 'language' => 'en',
            'meta_template_name' => 'promo', 'meta_status' => 'approved',
        ]);
        Contact::create(['name' => 'Aisha', 'phone' => '0123456789', 'subscribed' => true]);
        Contact::create(['name' => 'Opted out', 'phone' => '0111111111', 'subscribed' => false]);

        $broadcast = Broadcast::create(['name' => 'Blast', 'whatsapp_template_id' => $tpl->id, 'audience_type' => 'all_contacts']);
        $broadcast->queueForSending();
        $this->assertSame(1, $broadcast->fresh()->total);

        Http::fake(['graph.facebook.com/*' => Http::response(['messages' => [['id' => 'wamid.X']]], 200)]);

        $this->artisan('app:send-broadcasts')->assertExitCode(0);

        $broadcast->refresh();
        $this->assertSame('sent', $broadcast->status);
        $this->assertSame(1, $broadcast->sent_count);
        Http::assertSent(fn ($r) => $r['type'] === 'template'
            && data_get($r, 'template.name') === 'promo'
            && $r['to'] === '60123456789');
    }

    public function test_marketing_admin_pages_render(): void
    {
        $user = User::factory()->create();
        foreach (['contacts', 'contact-groups', 'broadcasts'] as $slug) {
            $this->actingAs($user)->get("/admin/{$slug}")->assertOk();
            $this->actingAs($user)->get("/admin/{$slug}/create")->assertOk();
        }
    }
}
