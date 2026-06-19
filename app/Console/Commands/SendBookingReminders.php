<?php

namespace App\Console\Commands;

use App\Models\Booking;
use App\Models\Setting;
use App\Services\WhatsAppCloud;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class SendBookingReminders extends Command
{
    protected $signature = 'app:send-booking-reminders {--date= : Override the target date (Y-m-d)}';

    protected $description = 'Send WhatsApp reminders for bookings happening tomorrow';

    public function handle(WhatsAppCloud $wa): int
    {
        if (! $wa->enabled()) {
            $this->warn('WhatsApp Cloud API is not enabled — nothing sent.');

            return self::SUCCESS;
        }

        $template = Setting::current()->wa_reminder_template;

        if (blank($template)) {
            $this->warn('No reminder template configured — nothing sent.');

            return self::SUCCESS;
        }

        $target = $this->option('date') ?: Carbon::tomorrow()->toDateString();

        $bookings = Booking::query()
            ->whereIn('status', ['new', 'confirmed'])
            ->whereNull('reminded_at')
            ->whereNotNull('phone')
            ->where('preferred_date', $target)
            ->get();

        $this->info("Found {$bookings->count()} booking(s) for {$target}.");

        $sent = 0;
        foreach ($bookings as $booking) {
            $result = $wa->sendTemplate($booking->phone, $template, [
                $booking->name ?: 'there',
                trim(($booking->preferred_time ?: '') . ' — ' . ($booking->package ?: $booking->service ?: 'your appointment')),
            ]);

            if ($result['ok']) {
                $booking->forceFill(['reminded_at' => Carbon::now()])->save();
                $sent++;
            } else {
                $this->error("  Failed for #{$booking->id}: " . ($result['error'] ?? 'unknown'));
            }
        }

        $this->info("Sent {$sent} reminder(s).");

        return self::SUCCESS;
    }
}
