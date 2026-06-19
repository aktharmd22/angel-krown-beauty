<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use Barryvdh\DomPDF\Facade\Pdf;

class InvoiceController extends Controller
{
    public function pdf(Invoice $invoice)
    {
        abort_unless(auth()->check(), 403);

        $invoice->load('items');

        // Embed the logo as base64 so DomPDF renders it reliably.
        $logo = null;
        $path = config('salon.logo');
        if ($path && is_file($path)) {
            $logo = 'data:image/png;base64,' . base64_encode(file_get_contents($path));
        }

        $pdf = Pdf::loadView('invoices.pdf', [
            'invoice' => $invoice,
            'salon' => config('salon'),
            'logo' => $logo,
        ])->setPaper('a4');

        return $pdf->stream("invoice-{$invoice->invoice_number}.pdf");
    }
}
