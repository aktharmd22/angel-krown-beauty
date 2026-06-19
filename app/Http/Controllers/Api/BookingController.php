<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessBookingNotifications;
use App\Models\Booking;
use App\Services\WhatsAppCloud;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['nullable', 'string', 'max:120'],
            'phone' => ['nullable', 'string', 'max:40'],
            'service' => ['nullable', 'string', 'max:120'],
            'pkg' => ['nullable', 'string', 'max:120'],
            'location' => ['nullable', 'in:salon,home'],
            'addr' => ['nullable', 'string', 'max:255'],
            'staff' => ['nullable', 'string', 'max:120'],
            'date' => ['nullable', 'string', 'max:40'],
            'time' => ['nullable', 'string', 'max:40'],
            'message' => ['nullable', 'string', 'max:2000'],
        ]);

        $booking = Booking::create([
            'name' => $data['name'] ?? null,
            'phone' => $data['phone'] ?? null,
            'service' => $data['service'] ?? null,
            'package' => $data['pkg'] ?? null,
            'location' => $data['location'] ?? 'salon',
            'address' => $data['addr'] ?? null,
            'specialist' => $data['staff'] ?? null,
            'preferred_date' => $data['date'] ?? null,
            'preferred_time' => $data['time'] ?? null,
            'message' => $data['message'] ?? null,
            'status' => 'new',
            'source' => 'website',
        ]);

        // Send WhatsApp messages after the response is sent (no queue worker needed).
        ProcessBookingNotifications::dispatch($booking)->afterResponse();

        // Tells the frontend whether the Cloud API will message the customer,
        // so it can show a success screen instead of opening the wa.me deep link.
        $whatsappEnabled = app(WhatsAppCloud::class)->enabled();

        return response()->json([
            'ok' => true,
            'id' => $booking->id,
            'whatsapp_enabled' => $whatsappEnabled,
        ], 201);
    }
}
