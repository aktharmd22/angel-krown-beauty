<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        @page { margin: 34px 40px; }
        * { box-sizing: border-box; }
        body { font-family: 'DejaVu Sans', sans-serif; color: #3A1424; font-size: 12px; line-height: 1.5; }
        .r { text-align: right; }
        .label { font-size: 9.5px; text-transform: uppercase; letter-spacing: 1px; color: #9a8a92; }

        table.head { width: 100%; border-bottom: 2px solid #8B1A4F; padding-bottom: 4px; }
        table.head td { vertical-align: top; padding-bottom: 12px; }
        .logo { width: 66px; height: 66px; }
        .brand-name { font-size: 21px; color: #8B1A4F; font-weight: bold; letter-spacing: 1px; }
        .brand-sub { font-size: 9px; color: #C9A24B; letter-spacing: 3px; text-transform: uppercase; }
        .inv-title { font-size: 26px; color: #8B1A4F; font-weight: bold; }
        .inv-meta { font-size: 11px; margin-top: 4px; }

        .badge { display: inline-block; padding: 3px 11px; border-radius: 10px; font-size: 9.5px; font-weight: bold; text-transform: uppercase; }
        .badge-paid { background: #e6f6ec; color: #1f7a44; }
        .badge-unpaid { background: #fdf0d9; color: #9a6b00; }
        .badge-cancelled { background: #fde8e8; color: #b42424; }
        .badge-draft { background: #eeeeee; color: #666666; }

        .parties { width: 100%; margin-top: 20px; }
        .parties td { vertical-align: top; font-size: 11px; }

        table.items { width: 100%; border-collapse: collapse; margin-top: 22px; }
        table.items th { background: #8B1A4F; color: #fff; text-align: left; padding: 8px 10px; font-size: 10.5px; letter-spacing: .3px; }
        table.items th.r { text-align: right; }
        table.items td { padding: 9px 10px; border-bottom: 1px solid #eee; font-size: 11px; }

        table.totals { width: 100%; }
        table.totals td { padding: 4px 0; font-size: 12px; }
        .totals .grand td { border-top: 2px solid #8B1A4F; padding-top: 8px; font-size: 15px; font-weight: bold; color: #8B1A4F; }

        .pay { margin-top: 20px; font-size: 11px; }
        .notes { margin-top: 12px; font-size: 11px; }
        .foot { margin-top: 30px; border-top: 1px solid #eee; padding-top: 12px; font-size: 9.5px; color: #9a8a92; text-align: center; }
    </style>
</head>
<body>

    <table class="head">
        <tr>
            <td width="46%">
                @if($logo)
                    <table><tr>
                        <td><img src="{{ $logo }}" class="logo"></td>
                        <td style="padding-left:10px;">
                            <div class="brand-name">{{ $salon['name'] }}</div>
                            <div class="brand-sub">{{ $salon['tagline'] }}</div>
                        </td>
                    </tr></table>
                @else
                    <div class="brand-name">{{ $salon['name'] }}</div>
                    <div class="brand-sub">{{ $salon['tagline'] }}</div>
                @endif
            </td>
            <td width="54%" class="r">
                <div class="inv-title">INVOICE</div>
                <div class="inv-meta">
                    <strong>{{ $invoice->invoice_number }}</strong><br>
                    {{ $invoice->issue_date->format('d M Y') }}<br>
                    <span class="badge badge-{{ $invoice->status }}">{{ ucfirst($invoice->status) }}</span>
                </div>
            </td>
        </tr>
    </table>

    <table class="parties">
        <tr>
            <td width="52%">
                <div class="label">Bill To</div>
                <strong>{{ $invoice->customer_name ?: '—' }}</strong><br>
                {{ $invoice->customer_phone }}<br>
                {{ $invoice->customer_email }}
            </td>
            <td width="48%">
                <div class="label">From</div>
                {{ $salon['name'] }} {{ $salon['tagline'] }}<br>
                {{ $salon['address'] }}<br>
                {{ $salon['phone_nail'] }} &middot; {{ $salon['phone_hair'] }}
            </td>
        </tr>
    </table>

    <table class="items">
        <thead>
            <tr>
                <th width="5%">#</th>
                <th>Description</th>
                <th class="r" width="12%">Qty</th>
                <th class="r" width="18%">Unit</th>
                <th class="r" width="20%">Amount</th>
            </tr>
        </thead>
        <tbody>
            @foreach($invoice->items as $idx => $item)
                <tr>
                    <td>{{ $idx + 1 }}</td>
                    <td>{{ $item->description }}</td>
                    <td class="r">{{ rtrim(rtrim(number_format($item->quantity, 2), '0'), '.') }}</td>
                    <td class="r">{{ $invoice->money($item->unit_price) }}</td>
                    <td class="r">{{ $invoice->money($item->amount) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <table style="width:100%; margin-top:12px;">
        <tr>
            <td width="55%">
                @if($invoice->payment_method)
                    <div class="pay">
                        <span class="label">Payment</span> &nbsp; {{ ucfirst($invoice->payment_method) }}
                        @if($invoice->paid_at) &middot; Paid {{ $invoice->paid_at->format('d M Y') }} @endif
                    </div>
                @endif
                @if($invoice->notes)
                    <div class="notes"><span class="label">Notes</span><br>{{ $invoice->notes }}</div>
                @endif
            </td>
            <td width="45%">
                <table class="totals">
                    <tr><td>Subtotal</td><td class="r">{{ $invoice->money($invoice->subtotal) }}</td></tr>
                    @if($invoice->discount_amount > 0)
                        <tr><td>Discount</td><td class="r">&minus; {{ $invoice->money($invoice->discount_amount) }}</td></tr>
                    @endif
                    @if($invoice->tax_amount > 0)
                        <tr><td>{{ $invoice->tax_label }} ({{ rtrim(rtrim(number_format($invoice->tax_rate, 2), '0'), '.') }}%)</td><td class="r">{{ $invoice->money($invoice->tax_amount) }}</td></tr>
                    @endif
                    <tr class="grand"><td>Total</td><td class="r">{{ $invoice->money($invoice->total) }}</td></tr>
                </table>
            </td>
        </tr>
    </table>

    <div class="foot">
        Thank you for choosing {{ $salon['name'] }} {{ $salon['tagline'] }} &middot; {{ $salon['address'] }}<br>
        This is a computer-generated invoice.
    </div>

</body>
</html>
