<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BroadcastRecipient extends Model
{
    protected $fillable = ['broadcast_id', 'name', 'phone', 'status', 'wa_message_id', 'error', 'sent_at'];

    protected $casts = ['sent_at' => 'datetime'];

    public function broadcast()
    {
        return $this->belongsTo(Broadcast::class);
    }
}
