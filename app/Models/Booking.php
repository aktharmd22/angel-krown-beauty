<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    protected $fillable = [
        'customer_id',
        'name',
        'phone',
        'service',
        'package',
        'location',
        'address',
        'specialist',
        'preferred_date',
        'preferred_time',
        'status',
        'message',
        'source',
        'wa_message_id',
        'confirmed_at',
        'owner_notified_at',
        'confirmation_sent_at',
        'reminded_at',
    ];

    protected $casts = [
        'confirmed_at' => 'datetime',
        'owner_notified_at' => 'datetime',
        'confirmation_sent_at' => 'datetime',
        'reminded_at' => 'datetime',
    ];

    public const STATUSES = ['new', 'confirmed', 'done', 'cancelled'];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    protected static function booted(): void
    {
        static::created(function (Booking $booking) {
            if (blank($booking->customer_id) && filled($booking->phone)) {
                $customer = Customer::upsertByPhone($booking->phone, $booking->name);
                if ($customer) {
                    $booking->customer_id = $customer->id;
                    $booking->saveQuietly();
                }
            }
        });
    }
}
