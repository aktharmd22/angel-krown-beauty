<?php

namespace App\Services;

use App\Models\Setting;
use App\Models\WhatsappTemplate;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Thin wrapper around the Meta WhatsApp Cloud API.
 * Credentials are read from the admin Settings page (Setting model).
 */
class WhatsAppCloud
{
    protected const VERSION = 'v21.0';

    protected Setting $settings;

    public function __construct(?Setting $settings = null)
    {
        // Guard against the container auto-injecting an empty Setting model.
        $this->settings = ($settings && $settings->exists) ? $settings : Setting::current();
    }

    /** Automation is usable only when enabled and the core credentials exist. */
    public function enabled(): bool
    {
        return (bool) $this->settings->whatsapp_enabled
            && filled($this->settings->wa_phone_number_id)
            && filled($this->settings->wa_access_token);
    }

    public function language(): string
    {
        return $this->settings->wa_template_language ?: 'en';
    }

    /**
     * Normalise a Malaysian-style number to international digits (no +).
     * 012-345 6789 -> 60123456789
     */
    public static function normalize(?string $phone): string
    {
        $digits = preg_replace('/\D/', '', (string) $phone);

        if ($digits === '') {
            return '';
        }
        if (str_starts_with($digits, '0')) {
            return '60' . substr($digits, 1);
        }

        return $digits;
    }

    /** Free-form text — only delivered inside the 24h customer-service window. */
    public function sendText(string $to, string $body): array
    {
        return $this->send([
            'messaging_product' => 'whatsapp',
            'to' => static::normalize($to),
            'type' => 'text',
            'text' => ['preview_url' => false, 'body' => $body],
        ]);
    }

    /**
     * Template message — required for business-initiated messages.
     * $bodyParams fill the {{1}}, {{2}}… placeholders in the template body.
     */
    public function sendTemplate(string $to, string $template, array $bodyParams = [], ?string $language = null): array
    {
        $payload = [
            'messaging_product' => 'whatsapp',
            'to' => static::normalize($to),
            'type' => 'template',
            'template' => [
                'name' => $template,
                'language' => ['code' => $language ?: $this->language()],
            ],
        ];

        if (! empty($bodyParams)) {
            $payload['template']['components'] = [[
                'type' => 'body',
                'parameters' => array_map(
                    fn ($p) => ['type' => 'text', 'text' => (string) $p],
                    $bodyParams,
                ),
            ]];
        }

        return $this->send($payload);
    }

    /**
     * Send a stored WhatsappTemplate to a number, filling its placeholders.
     * Uses a Meta-approved template if configured, otherwise plain text.
     */
    public function sendUsingTemplate(WhatsappTemplate $template, string $to, array $data): array
    {
        if ($template->usesMetaTemplate()) {
            return $this->sendTemplate($to, $template->meta_template_name, $template->orderedParams($data), $template->language);
        }

        return $this->sendText($to, $template->render($data));
    }

    protected function send(array $payload): array
    {
        if (! $this->enabled()) {
            return ['ok' => false, 'error' => 'WhatsApp Cloud API is not enabled or configured.'];
        }
        if ($payload['to'] === '') {
            return ['ok' => false, 'error' => 'No destination phone number.'];
        }

        $url = sprintf(
            'https://graph.facebook.com/%s/%s/messages',
            self::VERSION,
            $this->settings->wa_phone_number_id,
        );

        try {
            /** @var Response $res */
            $res = Http::withToken($this->settings->wa_access_token)
                ->timeout(15)
                ->acceptJson()
                ->post($url, $payload);
        } catch (\Throwable $e) {
            Log::warning('WhatsApp send failed (transport)', ['error' => $e->getMessage()]);

            return ['ok' => false, 'error' => $e->getMessage()];
        }

        if ($res->failed()) {
            Log::warning('WhatsApp send failed', ['status' => $res->status(), 'body' => $res->json()]);

            return [
                'ok' => false,
                'error' => data_get($res->json(), 'error.message', 'Request failed (' . $res->status() . ').'),
            ];
        }

        return [
            'ok' => true,
            'message_id' => data_get($res->json(), 'messages.0.id'),
        ];
    }
}
