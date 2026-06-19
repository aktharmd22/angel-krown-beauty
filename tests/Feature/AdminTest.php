<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminTest extends TestCase
{
    use RefreshDatabase;

    public function test_booking_api_stores_a_booking(): void
    {
        $res = $this->postJson('/api/bookings', [
            'name' => 'Test Guest',
            'phone' => '0123456789',
            'service' => 'Nail Studio',
            'pkg' => 'The Signature Glow',
            'location' => 'home',
            'addr' => '12 Jln Test',
            'staff' => 'Aisyah',
            'date' => '2026-06-25',
            'time' => '2:30 PM',
            'message' => 'Hi Angel Krown',
        ]);

        $res->assertCreated()->assertJson(['ok' => true]);
        $this->assertDatabaseHas('bookings', [
            'name' => 'Test Guest',
            'package' => 'The Signature Glow',
            'location' => 'home',
            'specialist' => 'Aisyah',
            'status' => 'new',
        ]);
    }

    public function test_admin_pages_render_for_authenticated_user(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get('/admin')->assertOk();
        $this->actingAs($user)->get('/admin/bookings')->assertOk();
        $this->actingAs($user)->get('/admin/bookings/create')->assertOk();
        $this->actingAs($user)->get('/admin/specialists')->assertOk();
        $this->actingAs($user)->get('/admin/specialists/create')->assertOk();
        $this->actingAs($user)->get('/admin/whatsapp-admins')->assertOk();
        $this->actingAs($user)->get('/admin/whatsapp-admins/create')->assertOk();
        $this->actingAs($user)->get('/admin/whatsapp-templates')->assertOk();
        $this->actingAs($user)->get('/admin/whatsapp-templates/create')->assertOk();
        $this->actingAs($user)->get('/admin/whats-app-settings')->assertOk();
    }

    public function test_home_shares_active_specialists(): void
    {
        \App\Models\Specialist::create(['name' => 'Zara', 'role' => 'Nail Artist', 'is_active' => true]);
        \App\Models\Specialist::create(['name' => 'Hidden', 'role' => 'X', 'is_active' => false]);

        $this->get('/')->assertInertia(
            fn (\Inertia\Testing\AssertableInertia $page) => $page
                ->has('specialists', 1)
                ->where('specialists.0.name', 'Zara')
                ->where('specialists.0.option', 'Zara — Nail Artist'),
        );
    }

    public function test_booking_edit_page_renders(): void
    {
        $user = User::factory()->create();
        $booking = Booking::create(['name' => 'Edit Me', 'location' => 'salon', 'status' => 'new']);

        $this->actingAs($user)
            ->get("/admin/bookings/{$booking->id}/edit")
            ->assertOk();
    }
}
