<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = [
        'business_whatsapp_number',
        'owner_notify_number',
        'whatsapp_enabled',
        'admin_template_id',
        'customer_template_id',
        'wa_phone_number_id',
        'wa_business_account_id',
        'wa_access_token',
        'wa_verify_token',
        'wa_confirm_template',
        'wa_reminder_template',
        'wa_template_language',
    ];

    protected $casts = [
        'whatsapp_enabled' => 'boolean',
    ];

    public function adminTemplate()
    {
        return $this->belongsTo(WhatsappTemplate::class, 'admin_template_id');
    }

    public function customerTemplate()
    {
        return $this->belongsTo(WhatsappTemplate::class, 'customer_template_id');
    }

    /**
     * The single settings row (created on first access).
     */
    public static function current(): self
    {
        return static::query()->firstOrCreate(['id' => 1], [
            'business_whatsapp_number' => '60162674626',
        ]);
    }
}
