<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerTest extends TestCase
{
    use RefreshDatabase;

    public function test_booking_creates_and_links_a_customer(): void
    {
        $booking = Booking::create(['name' => 'Aisha', 'phone' => '012-345 6789', 'location' => 'salon']);

        $this->assertNotNull($booking->fresh()->customer_id);
        $customer = Customer::first();
        $this->assertSame('60123456789', $customer->phone); // normalized
        $this->assertSame('Aisha', $customer->name);
    }

    public function test_same_phone_reuses_one_customer(): void
    {
        Booking::create(['name' => 'Aisha', 'phone' => '0123456789', 'location' => 'salon']);
        Booking::create(['name' => 'Aisha', 'phone' => '012-345 6789', 'location' => 'home']);

        $this->assertSame(1, Customer::count());
        $this->assertSame(2, Customer::first()->visits_count);
    }

    public function test_total_spent_sums_paid_invoices(): void
    {
        Booking::create(['name' => 'Aisha', 'phone' => '0123456789', 'location' => 'salon']);
        Invoice::create(['customer_phone' => '0123456789', 'customer_name' => 'Aisha', 'status' => 'paid', 'total' => 120]);
        Invoice::create(['customer_phone' => '0123456789', 'customer_name' => 'Aisha', 'status' => 'unpaid', 'total' => 80]);

        $this->assertEquals(120.0, Customer::first()->total_spent); // unpaid excluded
    }

    public function test_customer_admin_pages_render(): void
    {
        $user = User::factory()->create();
        Booking::create(['name' => 'A', 'phone' => '0123456789', 'location' => 'salon']);
        $customer = Customer::first();

        $this->actingAs($user)->get('/admin/customers')->assertOk();
        $this->actingAs($user)->get("/admin/customers/{$customer->id}/edit")->assertOk();
    }

    public function test_dashboard_renders_with_widgets(): void
    {
        $user = User::factory()->create();
        Booking::create(['name' => 'A', 'phone' => '0123456789', 'location' => 'salon']);

        $this->actingAs($user)->get('/admin')->assertOk();
    }
}
