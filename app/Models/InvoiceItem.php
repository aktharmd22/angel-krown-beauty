<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvoiceItem extends Model
{
    protected $fillable = ['invoice_id', 'description', 'quantity', 'unit_price'];

    protected $casts = [
        'quantity' => 'decimal:2',
        'unit_price' => 'decimal:2',
    ];

    public function getAmountAttribute(): float
    {
        return round((float) $this->quantity * (float) $this->unit_price, 2);
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }
}
