<?php

namespace App\Http\Middleware;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'wa' => [
                'number' => $this->publicWaNumber(),
                'apiEnabled' => $this->whatsappApiEnabled(),
            ],
            'specialists' => $this->specialists(),
        ];
    }

    /** Whether the WhatsApp Cloud API is configured & enabled. */
    protected function whatsappApiEnabled(): bool
    {
        try {
            if (! Schema::hasTable('settings')) {
                return false;
            }

            return app(\App\Services\WhatsAppCloud::class)->enabled();
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Active specialists for the public site (Specialists section + booking dropdown).
     */
    protected function specialists(): array
    {
        try {
            if (! Schema::hasTable('specialists')) {
                return [];
            }

            return \App\Models\Specialist::activeOrdered()->get()->map(fn ($s) => [
                'name' => $s->name,
                'role' => $s->role,
                'blurb' => $s->blurb,
                'img' => $s->photo_url,
                'option' => $s->option_label,
            ])->all();
        } catch (\Throwable $e) {
            return [];
        }
    }

    /**
     * The public WhatsApp number, configurable from the admin Settings page.
     * Falls back to the default if the settings table isn't migrated yet.
     */
    protected function publicWaNumber(): string
    {
        $fallback = '60162674626';

        try {
            if (! Schema::hasTable('settings')) {
                return $fallback;
            }

            return Setting::current()->business_whatsapp_number ?: $fallback;
        } catch (\Throwable $e) {
            return $fallback;
        }
    }
}
