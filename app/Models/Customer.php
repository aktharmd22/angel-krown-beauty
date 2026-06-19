<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $fillable = ['name', 'phone', 'email', 'notes'];

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    /** 012-345 6789 -> 60123456789 */
    public static function normalizePhone(?string $phone): string
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

    /** Find or create a customer by phone, filling the name if missing. */
    public static function upsertByPhone(?string $phone, ?string $name = null): ?self
    {
        $norm = static::normalizePhone($phone);
        if ($norm === '') {
            return null;
        }

        $customer = static::firstOrCreate(['phone' => $norm], ['name' => $name]);

        if (blank($customer->name) && filled($name)) {
            $customer->update(['name' => $name]);
        }

        return $customer;
    }

    public function getTotalSpentAttribute(): float
    {
        return (float) $this->invoices()->where('status', 'paid')->sum('total');
    }

    public function getVisitsCountAttribute(): int
    {
        return $this->bookings()->count();
    }

    public function getLastVisitAttribute()
    {
        return $this->bookings()->max('created_at');
    }
}
