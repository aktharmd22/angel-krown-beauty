<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    protected $fillable = ['conversation_id', 'direction', 'type', 'body', 'media_url', 'wa_message_id', 'status', 'error'];

    public function conversation()
    {
        return $this->belongsTo(Conversation::class);
    }

    public function isInbound(): bool
    {
        return $this->direction === 'inbound';
    }
}
