<?php

namespace App\Jobs;

use App\Models\Booking;
use App\Models\Setting;
use App\Models\WhatsappAdmin;
use App\Models\WhatsappTemplate;
use App\Services\WhatsAppCloud;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Carbon;

/**
 * On a new booking, sends (via WhatsApp Cloud API):
 *   - the customer thank-you template to the customer
 *   - the admin confirmation template to each active WhatsApp Admin (up to 3)
 *
 * Dispatched ->afterResponse() so it never delays the request and needs no worker.
 */
class ProcessBookingNotifications
{
    use Dispatchable;

    public function __construct(public Booking $booking) {}

    public function handle(WhatsAppCloud $wa): void
    {
        if (! $wa->enabled()) {
            return;
        }

        $settings = Setting::current();
        $booking = $this->booking;
        $data = $this->data($booking);

        // 1) Customer thank-you
        if (filled($booking->phone) && $settings->customer_template_id) {
            if ($tpl = WhatsappTemplate::find($settings->customer_template_id)) {
                $result = $wa->sendUsingTemplate($tpl, $booking->phone, $data);
                if ($result['ok']) {
                    $booking->forceFill([
                        'wa_message_id' => $result['message_id'] ?? $booking->wa_message_id,
                        'confirmation_sent_at' => Carbon::now(),
                    ])->save();
                }
            }
        }

        // 2) Admin confirmation to each WhatsApp Admin (fallback: owner number)
        if ($settings->admin_template_id) {
            if ($tpl = WhatsappTemplate::find($settings->admin_template_id)) {
                foreach ($this->adminPhones($settings) as $phone) {
                    $wa->sendUsingTemplate($tpl, $phone, $data);
                }
                $booking->forceFill(['owner_notified_at' => Carbon::now()])->save();
            }
        }
    }

    /** Up to 3 active admin numbers, falling back to the owner number. */
    protected function adminPhones(Setting $settings): array
    {
        $phones = WhatsappAdmin::active()->pluck('phone')->filter()->take(3)->values()->all();

        if (empty($phones) && filled($settings->owner_notify_number)) {
            $phones = [$settings->owner_notify_number];
        }

        return $phones;
    }

    protected function data(Booking $b): array
    {
        return [
            'name' => $b->name ?: 'there',
            'phone' => $b->phone ?: '',
            'service' => $b->service ?: '',
            'package' => $b->package ?: '',
            'date' => $b->preferred_date ?: '',
            'time' => $b->preferred_time ?: '',
            'location' => $b->location === 'home' ? 'Home service' : 'In-salon',
            'specialist' => $b->specialist ?: 'No preference',
            'address' => $b->address ?: '',
        ];
    }
}
