<?php

namespace Tests\Feature;

use App\Models\Invoice;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvoiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_invoice_number_is_auto_generated_sequentially(): void
    {
        $a = Invoice::create(['customer_name' => 'A']);
        $b = Invoice::create(['customer_name' => 'B']);

        $this->assertMatchesRegularExpression('/^AK-\d{4}-0001$/', $a->invoice_number);
        $this->assertMatchesRegularExpression('/^AK-\d{4}-0002$/', $b->invoice_number);
    }

    public function test_totals_recalculate_with_discount_and_tax(): void
    {
        $inv = Invoice::create([
            'customer_name' => 'A',
            'discount_type' => 'percent', 'discount_value' => 10,
            'tax_rate' => 6, 'tax_label' => 'SST',
        ]);
        $inv->items()->create(['description' => 'Gel mani', 'quantity' => 2, 'unit_price' => 50]); // 100
        $inv->items()->create(['description' => 'Facial', 'quantity' => 1, 'unit_price' => 80]);    // 80

        $inv->load('items')->recalculateTotals()->save();

        // subtotal 180, -10% = 18 discount, taxable 162, 6% tax = 9.72, total 171.72
        $this->assertEquals(180.00, (float) $inv->subtotal);
        $this->assertEquals(18.00, (float) $inv->discount_amount);
        $this->assertEquals(9.72, (float) $inv->tax_amount);
        $this->assertEquals(171.72, (float) $inv->total);
    }

    public function test_mark_paid_sets_status_and_timestamp(): void
    {
        $inv = Invoice::create(['customer_name' => 'A']);
        $inv->markPaid('cash');

        $this->assertSame('paid', $inv->fresh()->status);
        $this->assertSame('cash', $inv->fresh()->payment_method);
        $this->assertNotNull($inv->fresh()->paid_at);
    }

    public function test_admin_invoice_pages_render(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get('/admin/invoices')->assertOk();
        $this->actingAs($user)->get('/admin/invoices/create')->assertOk();

        $inv = Invoice::create(['customer_name' => 'A']);
        $this->actingAs($user)->get("/admin/invoices/{$inv->id}/edit")->assertOk();
    }

    public function test_invoice_pdf_downloads_for_authenticated_user(): void
    {
        $user = User::factory()->create();
        $inv = Invoice::create(['customer_name' => 'Aisha', 'customer_phone' => '0123456789']);
        $inv->items()->create(['description' => 'Gel manicure', 'quantity' => 1, 'unit_price' => 50]);
        $inv->load('items')->recalculateTotals()->save();

        $res = $this->actingAs($user)->get("/admin/invoices/{$inv->id}/pdf");

        $res->assertOk();
        $this->assertStringContainsString('application/pdf', (string) $res->headers->get('content-type'));
    }

    public function test_invoice_pdf_blocked_for_guests(): void
    {
        $inv = Invoice::create(['customer_name' => 'A']);

        $this->get("/admin/invoices/{$inv->id}/pdf")->assertForbidden();
    }
}
