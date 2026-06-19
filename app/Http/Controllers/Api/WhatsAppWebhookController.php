<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\Setting;
use App\Models\WhatsappTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class WhatsAppWebhookController extends Controller
{
    /**
     * Meta webhook verification handshake (GET).
     * PHP converts the dotted query keys (hub.mode) to underscores.
     */
    public function verify(Request $request)
    {
        $verifyToken = Setting::current()->wa_verify_token;

        if (
            $request->query('hub_mode') === 'subscribe'
            && filled($verifyToken)
            && hash_equals((string) $verifyToken, (string) $request->query('hub_verify_token'))
        ) {
            return response($request->query('hub_challenge'), 200)
                ->header('Content-Type', 'text/plain');
        }

        return response('Forbidden', 403);
    }

    /**
     * Incoming events (POST): message status updates + inbound messages.
     * Always acknowledge with 200 so Meta does not retry.
     */
    public function handle(Request $request)
    {
        $entries = (array) $request->input('entry', []);

        foreach ($entries as $entry) {
            foreach ((array) data_get($entry, 'changes', []) as $change) {
                $value = data_get($change, 'value', []);

                // Template approval status updates
                if (data_get($change, 'field') === 'message_template_status_update') {
                    $this->updateTemplateStatus($value);

                    continue;
                }

                // Delivery/read/failed receipts
                foreach ((array) data_get($value, 'statuses', []) as $status) {
                    $id = data_get($status, 'id');
                    $state = data_get($status, 'status');
                    if (! $id || ! $state) {
                        continue;
                    }

                    Message::where('wa_message_id', $id)->update(['status' => $state]);

                    if ($state === 'read') {
                        Booking::where('wa_message_id', $id)->update(['confirmed_at' => now()]);
                    }
                }

                // Inbound customer messages → store in the inbox
                $contactName = data_get($value, 'contacts.0.profile.name');
                foreach ((array) data_get($value, 'messages', []) as $message) {
                    $this->storeInbound($message, $contactName);
                }
            }
        }

        return response()->json(['ok' => true]);
    }

    /** Store an inbound customer message into the inbox. */
    protected function storeInbound(array $message, ?string $contactName): void
    {
        $from = data_get($message, 'from');
        if (! $from) {
            return;
        }

        $type = data_get($message, 'type', 'text');
        $body = data_get($message, 'text.body');

        if (blank($body)) {
            $body = match ($type) {
                'image' => '📷 Photo',
                'video' => '🎥 Video',
                'audio' => '🎵 Voice message',
                'document' => '📄 Document',
                'sticker' => '🌟 Sticker',
                'location' => '📍 Location',
                'contacts' => '👤 Contact',
                'button' => data_get($message, 'button.text'),
                'interactive' => data_get($message, 'interactive.button_reply.title')
                    ?? data_get($message, 'interactive.list_reply.title'),
                default => '[' . $type . ']',
            };
        }

        $conversation = Conversation::forPhone($from, $contactName);

        $conversation->messages()->create([
            'direction' => 'inbound',
            'type' => $type,
            'body' => $body,
            'wa_message_id' => data_get($message, 'id'),
            'status' => 'received',
        ]);

        $conversation->forceFill([
            'last_message' => Str::limit((string) $body, 120),
            'last_message_at' => now(),
            'unread_count' => $conversation->unread_count + 1,
            'status' => 'open',
        ])->save();
    }

    /** Apply a Meta template approval/rejection to the local template. */
    protected function updateTemplateStatus(array $value): void
    {
        $id = data_get($value, 'message_template_id');
        $name = data_get($value, 'message_template_name');
        $event = strtolower((string) data_get($value, 'event')); // approved | rejected | ...

        $template = WhatsappTemplate::query()
            ->when($id, fn ($q) => $q->where('meta_template_id', $id))
            ->when(! $id && $name, fn ($q) => $q->where('meta_template_name', $name))
            ->first();

        if ($template && $event) {
            $template->forceFill([
                'meta_status' => $event,
                'meta_rejected_reason' => $event === 'rejected' ? data_get($value, 'reason') : null,
            ])->save();
        }
    }
}

