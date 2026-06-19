<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Conversation extends Model
{
    protected $fillable = ['wa_phone', 'name', 'last_message', 'last_message_at', 'unread_count', 'status'];

    protected $casts = ['last_message_at' => 'datetime'];

    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    /** Find or create the conversation for a phone, filling the name if missing. */
    public static function forPhone(string $phone, ?string $name = null): self
    {
        $conversation = static::firstOrCreate(['wa_phone' => $phone], ['name' => $name]);

        if ($name && blank($conversation->name)) {
            $conversation->update(['name' => $name]);
        }

        return $conversation;
    }

    public function display(): string
    {
        return $this->name ?: $this->wa_phone;
    }
}
