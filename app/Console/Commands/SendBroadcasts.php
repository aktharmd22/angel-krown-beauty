<?php

namespace App\Console\Commands;

use App\Models\Broadcast;
use App\Services\WhatsAppCloud;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class SendBroadcasts extends Command
{
    protected $signature = 'app:send-broadcasts {--batch=50 : Recipients to send per run}';

    protected $description = 'Send queued WhatsApp broadcasts in batches via the Cloud API';

    public function handle(WhatsAppCloud $wa): int
    {
        if (! $wa->enabled()) {
            $this->warn('WhatsApp Cloud API is not enabled — nothing sent.');

            return self::SUCCESS;
        }

        $batchSize = (int) $this->option('batch');

        $broadcasts = Broadcast::query()
            ->whereIn('status', ['queued', 'sending'])
            ->where(fn ($q) => $q->whereNull('scheduled_at')->orWhere('scheduled_at', '<=', now()))
            ->get();

        foreach ($broadcasts as $broadcast) {
            $template = $broadcast->template;

            if (! $template || ! $template->usesMetaTemplate()) {
                $broadcast->update(['status' => 'failed', 'finished_at' => Carbon::now()]);
                $this->error("Broadcast #{$broadcast->id}: template not approved by Meta — skipped.");

                continue;
            }

            $broadcast->update(['status' => 'sending']);

            $recipients = $broadcast->recipients()->where('status', 'pending')->limit($batchSize)->get();

            foreach ($recipients as $recipient) {
                $result = $wa->sendUsingTemplate($template, $recipient->phone, ['name' => $recipient->name ?: 'there']);

                if ($result['ok']) {
                    $recipient->update([
                        'status' => 'sent',
                        'wa_message_id' => $result['message_id'] ?? null,
                        'sent_at' => Carbon::now(),
                    ]);
                    $broadcast->increment('sent_count');
                } else {
                    $recipient->update(['status' => 'failed', 'error' => mb_substr((string) ($result['error'] ?? 'unknown'), 0, 240)]);
                    $broadcast->increment('failed_count');
                }
            }

            if ($broadcast->recipients()->where('status', 'pending')->count() === 0) {
                $broadcast->update(['status' => 'sent', 'finished_at' => Carbon::now()]);
                $this->info("Broadcast #{$broadcast->id} complete: {$broadcast->sent_count} sent, {$broadcast->failed_count} failed.");
            }
        }

        return self::SUCCESS;
    }
}
