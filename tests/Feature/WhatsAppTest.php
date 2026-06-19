<?php

namespace Tests\Feature;

use App\Jobs\ProcessBookingNotifications;
use App\Models\Booking;
use App\Models\Setting;
use App\Models\WhatsappAdmin;
use App\Models\WhatsappTemplate;
use App\Services\MetaTemplates;
use App\Services\WhatsAppCloud;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class WhatsAppTest extends TestCase
{
    use RefreshDatabase;

    public function test_webhook_verification_returns_challenge_with_correct_token(): void
    {
        Setting::current()->update(['wa_verify_token' => 'secret123']);

        $this->get('/api/whatsapp/webhook?hub.mode=subscribe&hub.verify_token=secret123&hub.challenge=42')
            ->assertOk()
            ->assertSee('42');
    }

    public function test_webhook_verification_rejects_wrong_token(): void
    {
        Setting::current()->update(['wa_verify_token' => 'secret123']);

        $this->get('/api/whatsapp/webhook?hub.mode=subscribe&hub.verify_token=nope&hub.challenge=42')
            ->assertForbidden();
    }

    public function test_webhook_handle_acknowledges_events(): void
    {
        $this->postJson('/api/whatsapp/webhook', [
            'entry' => [
                ['changes' => [['value' => ['statuses' => [['id' => 'wamid.x', 'status' => 'delivered']]]]]],
            ],
        ])->assertOk()->assertJson(['ok' => true]);
    }

    public function test_reminders_command_is_safe_when_disabled(): void
    {
        $this->artisan('app:send-booking-reminders')->assertExitCode(0);
    }

    public function test_booking_still_succeeds_when_whatsapp_disabled(): void
    {
        $this->postJson('/api/bookings', ['name' => 'Noti Test', 'phone' => '0123456789', 'location' => 'salon'])
            ->assertCreated();

        $this->assertDatabaseHas('bookings', ['name' => 'Noti Test']);
    }

    public function test_send_template_builds_correct_payload(): void
    {
        Setting::current()->update([
            'whatsapp_enabled' => true,
            'wa_phone_number_id' => '123456',
            'wa_access_token' => 'TOKEN',
        ]);

        Http::fake([
            'graph.facebook.com/*' => Http::response(['messages' => [['id' => 'wamid.ABC']]], 200),
        ]);

        $result = (new WhatsAppCloud())->sendTemplate('012-345 6789', 'booking_confirmation', ['Aisha', 'tomorrow']);

        $this->assertTrue($result['ok']);
        $this->assertSame('wamid.ABC', $result['message_id']);

        Http::assertSent(function ($request) {
            return str_contains($request->url(), '/123456/messages')
                && $request['type'] === 'template'
                && $request['to'] === '60123456789'; // 0-prefix normalised to 60
        });
    }

    public function test_booking_job_messages_customer_and_admins(): void
    {
        $customerTpl = WhatsappTemplate::create(['name' => 'Thanks', 'body' => 'Hi {{name}}, thanks for booking {{service}}.', 'language' => 'en']);
        $adminTpl = WhatsappTemplate::create(['name' => 'Admin', 'body' => 'New booking from {{name}} ({{phone}})', 'language' => 'en']);

        Setting::current()->update([
            'whatsapp_enabled' => true,
            'wa_phone_number_id' => '123456',
            'wa_access_token' => 'TOKEN',
            'customer_template_id' => $customerTpl->id,
            'admin_template_id' => $adminTpl->id,
        ]);

        WhatsappAdmin::create(['name' => 'Front desk', 'phone' => '60111111111', 'is_active' => true]);
        WhatsappAdmin::create(['name' => 'Inactive', 'phone' => '60122222222', 'is_active' => false]);

        Http::fake([
            'graph.facebook.com/*' => Http::response(['messages' => [['id' => 'wamid.Z']]], 200),
        ]);

        $booking = Booking::create(['name' => 'Aisha', 'phone' => '0123456789', 'location' => 'salon', 'status' => 'new']);

        (new ProcessBookingNotifications($booking))->handle(new WhatsAppCloud());

        $booking->refresh();
        $this->assertNotNull($booking->confirmation_sent_at);
        $this->assertNotNull($booking->owner_notified_at);

        // customer (1) + one active admin (1) = 2 sends; inactive admin excluded
        Http::assertSentCount(2);
        Http::assertSent(fn ($r) => $r['to'] === '60123456789'); // customer, 0-prefix normalised
        Http::assertSent(fn ($r) => $r['to'] === '60111111111'); // active admin
    }

    public function test_whatsapp_admins_capped_at_three(): void
    {
        WhatsappAdmin::create(['phone' => '60111111111']);
        WhatsappAdmin::create(['phone' => '60122222222']);
        WhatsappAdmin::create(['phone' => '60133333333']);

        $this->assertFalse(\App\Filament\Resources\WhatsappAdminResource::canCreate());
    }

    public function test_cloud_api_enabled_resolves_via_container(): void
    {
        Setting::current()->update([
            'whatsapp_enabled' => true,
            'wa_phone_number_id' => 'PNID',
            'wa_access_token' => 'TOKEN',
        ]);

        // app() must use the real settings row, not an empty injected model.
        $this->assertTrue(app(WhatsAppCloud::class)->enabled());
    }

    public function test_submit_template_creates_in_meta(): void
    {
        Setting::current()->update(['wa_business_account_id' => 'WABA1', 'wa_access_token' => 'TOKEN']);
        $tpl = WhatsappTemplate::create([
            'name' => 'Customer Thanks',
            'body' => 'Hi {{name}}, thanks for {{service}}.',
            'language' => 'en',
            'category' => 'UTILITY',
        ]);

        Http::fake(['graph.facebook.com/*' => Http::response(['id' => '123', 'status' => 'PENDING'], 200)]);

        $res = app(MetaTemplates::class)->submit($tpl);

        $this->assertTrue($res['ok']);
        $tpl->refresh();
        $this->assertSame('pending', $tpl->meta_status);
        $this->assertSame('123', $tpl->meta_template_id);
        $this->assertSame('customer_thanks', $tpl->meta_template_name);

        Http::assertSent(function ($r) {
            return str_contains($r->url(), '/WABA1/message_templates')
                && $r['name'] === 'customer_thanks'
                && str_contains($r['components'][0]['text'], '{{1}}')   // named -> positional
                && $r['components'][0]['example']['body_text'][0][0] === 'Aisha'; // example provided
        });
    }

    public function test_sync_status_marks_approved(): void
    {
        Setting::current()->update(['wa_business_account_id' => 'WABA1', 'wa_access_token' => 'TOKEN']);
        $tpl = WhatsappTemplate::create([
            'name' => 'Thanks', 'body' => 'Hi {{name}}', 'language' => 'en',
            'meta_template_name' => 'thanks', 'meta_template_id' => '123', 'meta_status' => 'pending',
        ]);

        Http::fake(['graph.facebook.com/*' => Http::response(['data' => [['id' => '123', 'name' => 'thanks', 'status' => 'APPROVED']]], 200)]);

        $this->assertTrue(app(MetaTemplates::class)->syncStatus($tpl)['ok']);
        $this->assertSame('approved', $tpl->fresh()->meta_status);
    }

    public function test_template_status_webhook_updates_status(): void
    {
        $tpl = WhatsappTemplate::create(['name' => 'Thanks', 'body' => 'Hi {{name}}', 'meta_template_id' => '999', 'meta_status' => 'pending']);

        $this->postJson('/api/whatsapp/webhook', [
            'entry' => [[
                'changes' => [[
                    'field' => 'message_template_status_update',
                    'value' => ['message_template_id' => '999', 'message_template_name' => 'thanks', 'event' => 'APPROVED'],
                ]],
            ]],
        ])->assertOk();

        $this->assertSame('approved', $tpl->fresh()->meta_status);
    }

    public function test_approved_template_sends_as_official_template(): void
    {
        Setting::current()->update(['whatsapp_enabled' => true, 'wa_phone_number_id' => 'PNID', 'wa_access_token' => 'TOKEN']);
        $tpl = WhatsappTemplate::create([
            'name' => 'Thanks', 'body' => 'Hi {{name}}', 'language' => 'en',
            'meta_template_name' => 'thanks', 'meta_status' => 'approved',
        ]);

        Http::fake(['graph.facebook.com/*' => Http::response(['messages' => [['id' => 'wamid.A']]], 200)]);

        app(WhatsAppCloud::class)->sendUsingTemplate($tpl, '0123456789', ['name' => 'Aisha']);

        Http::assertSent(fn ($r) => $r['type'] === 'template' && data_get($r, 'template.name') === 'thanks');
    }
}
