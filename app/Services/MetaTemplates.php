<?php

namespace App\Services;

use App\Models\Setting;
use App\Models\WhatsappTemplate;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Manages WhatsApp message templates in Meta via the Business Management API.
 * Uses the WABA ID + access token from the admin Settings.
 */
class MetaTemplates
{
    protected const VERSION = 'v21.0';

    protected Setting $settings;

    public function __construct(?Setting $settings = null)
    {
        // Guard against the container auto-injecting an empty Setting model.
        $this->settings = ($settings && $settings->exists) ? $settings : Setting::current();
    }

    public function configured(): bool
    {
        return filled($this->settings->wa_business_account_id) && filled($this->settings->wa_access_token);
    }

    /**
     * Create the template in Meta, or edit it if already submitted (and editable).
     */
    public function submit(WhatsappTemplate $template): array
    {
        if (! $this->configured()) {
            return ['ok' => false, 'error' => 'Add your WABA ID and Access Token in Settings & API first.'];
        }

        $metaName = $template->metaName();

        // Editing is only allowed once a template is approved/rejected — not while pending.
        if (filled($template->meta_template_id)) {
            if ($template->meta_status === 'pending') {
                return ['ok' => false, 'error' => 'Still pending Meta review — you can edit it once it is approved or rejected.'];
            }

            $res = $this->request($this->url($template->meta_template_id), [
                'category' => $template->category ?: 'UTILITY',
                'components' => $template->toMetaComponents(),
            ]);

            if (! $res['ok']) {
                return $this->fail($template, $res['error']);
            }

            // An approved template edited goes back to pending review.
            $template->forceFill(['meta_status' => 'pending', 'meta_rejected_reason' => null])->save();

            return ['ok' => true, 'status' => 'pending'];
        }

        // Create new
        $res = $this->request($this->url($this->settings->wa_business_account_id . '/message_templates'), [
            'name' => $metaName,
            'language' => $template->language ?: 'en',
            'category' => $template->category ?: 'UTILITY',
            'components' => $template->toMetaComponents(),
        ]);

        // If it already exists in Meta, just adopt its current status.
        if (! $res['ok']) {
            if (str_contains(strtolower($res['error']), 'already exists')) {
                return $this->syncStatus($template);
            }

            return $this->fail($template, $res['error']);
        }

        $template->forceFill([
            'meta_template_name' => $metaName,
            'meta_template_id' => $res['data']['id'] ?? null,
            'meta_status' => strtolower($res['data']['status'] ?? 'pending'),
            'meta_rejected_reason' => null,
        ])->save();

        return ['ok' => true, 'status' => $template->meta_status];
    }

    /**
     * Refresh approval status from Meta.
     */
    public function syncStatus(WhatsappTemplate $template): array
    {
        if (! $this->configured()) {
            return ['ok' => false, 'error' => 'Not configured.'];
        }

        $metaName = filled($template->meta_template_name) ? $template->meta_template_name : $template->metaName();
        $url = $this->url($this->settings->wa_business_account_id . '/message_templates');

        try {
            $resp = Http::withToken($this->settings->wa_access_token)
                ->timeout(15)->acceptJson()
                ->get($url, ['name' => $metaName, 'fields' => 'name,status,id,category']);
        } catch (\Throwable $e) {
            return ['ok' => false, 'error' => $e->getMessage()];
        }

        if ($resp->failed()) {
            return ['ok' => false, 'error' => data_get($resp->json(), 'error.message', 'Request failed.')];
        }

        $match = collect($resp->json('data', []))->firstWhere('name', $metaName);

        if (! $match) {
            return ['ok' => false, 'error' => 'Template not found in Meta yet.'];
        }

        $template->forceFill([
            'meta_template_name' => $metaName,
            'meta_template_id' => $match['id'] ?? $template->meta_template_id,
            'meta_status' => strtolower($match['status'] ?? 'pending'),
        ])->save();

        return ['ok' => true, 'status' => $template->meta_status];
    }

    protected function request(string $url, array $payload): array
    {
        try {
            $resp = Http::withToken($this->settings->wa_access_token)
                ->timeout(20)->acceptJson()->post($url, $payload);
        } catch (\Throwable $e) {
            Log::warning('Meta template request failed', ['error' => $e->getMessage()]);

            return ['ok' => false, 'error' => $e->getMessage()];
        }

        if ($resp->failed()) {
            $error = data_get($resp->json(), 'error.error_user_msg')
                ?? data_get($resp->json(), 'error.message', 'Request failed (' . $resp->status() . ').');
            Log::warning('Meta template request failed', ['status' => $resp->status(), 'body' => $resp->json()]);

            return ['ok' => false, 'error' => $error];
        }

        return ['ok' => true, 'data' => $resp->json()];
    }

    protected function fail(WhatsappTemplate $template, string $error): array
    {
        $template->forceFill(['meta_status' => 'rejected', 'meta_rejected_reason' => $error])->save();

        return ['ok' => false, 'error' => $error];
    }

    protected function url(string $path): string
    {
        return sprintf('https://graph.facebook.com/%s/%s', self::VERSION, $path);
    }
}
