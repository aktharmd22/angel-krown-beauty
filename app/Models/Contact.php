<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
    protected $fillable = ['name', 'phone', 'email', 'subscribed', 'source', 'notes'];

    protected $casts = ['subscribed' => 'boolean'];

    public function groups()
    {
        return $this->belongsToMany(ContactGroup::class);
    }

    public function scopeSubscribed(Builder $query): Builder
    {
        return $query->where('subscribed', true);
    }

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
}
