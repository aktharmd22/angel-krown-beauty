<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Invoice;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Renders the Livewire table components with rows of every status, so the
 * column closures (color/format) are actually evaluated — GET page tests
 * miss this because Filament defers table rendering.
 */
class AdminRenderTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Filament::setCurrentPanel(Filament::getPanel('admin'));
        $this->actingAs(User::factory()->create());
    }

    public function test_latest_bookings_widget_renders_all_statuses(): void
    {
        foreach (['new', 'confirmed', 'done', 'cancelled'] as $st) {
            Booking::create(['name' => 'A', 'phone' => '012' . $st, 'status' => $st, 'location' => 'salon']);
        }

        Livewire::test(\App\Filament\Widgets\LatestBookings::class)->assertOk();
    }

    public function test_bookings_list_renders_all_statuses(): void
    {
        foreach (['new', 'confirmed', 'done', 'cancelled'] as $st) {
            Booking::create(['name' => 'A', 'phone' => '012' . $st, 'status' => $st, 'location' => $st === 'confirmed' ? 'home' : 'salon']);
        }

        Livewire::test(\App\Filament\Resources\BookingResource\Pages\ListBookings::class)->assertOk();
    }

    public function test_invoice_list_renders_all_statuses(): void
    {
        foreach (['draft', 'unpaid', 'paid', 'cancelled'] as $st) {
            Invoice::create(['customer_name' => 'A', 'status' => $st]);
        }

        Livewire::test(\App\Filament\Resources\InvoiceResource\Pages\ListInvoices::class)->assertOk();
    }
}
