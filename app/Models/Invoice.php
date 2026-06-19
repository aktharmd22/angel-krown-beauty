<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class Invoice extends Model
{
    protected $fillable = [
        'invoice_number', 'booking_id', 'customer_id',
        'customer_name', 'customer_phone', 'customer_email',
        'issue_date', 'status', 'payment_method',
        'discount_type', 'discount_value', 'tax_rate', 'tax_label',
        'subtotal', 'discount_amount', 'tax_amount', 'total',
        'notes', 'paid_at',
    ];

    protected $casts = [
        'issue_date' => 'date',
        'paid_at' => 'datetime',
        'discount_value' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    public const STATUSES = ['draft', 'unpaid', 'paid', 'cancelled'];

    protected static function booted(): void
    {
        static::creating(function (Invoice $invoice) {
            $invoice->invoice_number ??= static::nextNumber();
            $invoice->issue_date ??= now()->toDateString();

            if (blank($invoice->customer_id)) {
                if ($invoice->booking_id && ($booking = Booking::find($invoice->booking_id)) && $booking->customer_id) {
                    $invoice->customer_id = $booking->customer_id;
                } elseif (filled($invoice->customer_phone)) {
                    $customer = Customer::upsertByPhone($invoice->customer_phone, $invoice->customer_name);
                    $invoice->customer_id = $customer?->id;
                }
            }
        });
    }

    public function items()
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    /** Generate the next sequential invoice number, e.g. AK-2026-0007. */
    public static function nextNumber(): string
    {
        $year = now()->year;
        $count = static::whereYear('created_at', $year)->count() + 1;

        return sprintf('AK-%d-%04d', $year, $count);
    }

    /** Recompute totals from the line items + discount/tax. */
    public function recalculateTotals(): static
    {
        $subtotal = $this->items->sum(fn (InvoiceItem $i) => (float) $i->quantity * (float) $i->unit_price);

        $discount = $this->discount_type === 'percent'
            ? $subtotal * ((float) $this->discount_value) / 100
            : (float) $this->discount_value;
        $discount = min($discount, $subtotal);

        $taxable = $subtotal - $discount;
        $tax = $taxable * ((float) $this->tax_rate) / 100;

        $this->subtotal = round($subtotal, 2);
        $this->discount_amount = round($discount, 2);
        $this->tax_amount = round($tax, 2);
        $this->total = round($taxable + $tax, 2);

        return $this;
    }

    public function markPaid(?string $method = null): void
    {
        $this->forceFill([
            'status' => 'paid',
            'payment_method' => $method ?: $this->payment_method,
            'paid_at' => Carbon::now(),
        ])->save();
    }

    public function money($value): string
    {
        return config('salon.currency', 'RM') . ' ' . number_format((float) $value, 2);
    }
}
