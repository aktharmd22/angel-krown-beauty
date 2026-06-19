<?php

namespace Database\Seeders;

use App\Models\Booking;
use App\Models\Customer;
use App\Models\Invoice;
use Illuminate\Database\Seeder;

class CustomerBackfillSeeder extends Seeder
{
    public function run(): void
    {
        Booking::whereNull('customer_id')->whereNotNull('phone')->get()->each(function (Booking $b) {
            if ($customer = Customer::upsertByPhone($b->phone, $b->name)) {
                $b->customer_id = $customer->id;
                $b->saveQuietly();
            }
        });

        Invoice::whereNull('customer_id')->get()->each(function (Invoice $i) {
            if ($i->booking_id && ($b = Booking::find($i->booking_id)) && $b->customer_id) {
                $i->customer_id = $b->customer_id;
            } elseif (filled($i->customer_phone)) {
                $i->customer_id = Customer::upsertByPhone($i->customer_phone, $i->customer_name)?->id;
            }
            $i->saveQuietly();
        });
    }
}
