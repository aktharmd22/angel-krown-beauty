<?php

namespace Database\Seeders;

use App\Models\Specialist;
use Illuminate\Database\Seeder;

class SpecialistSeeder extends Seeder
{
    public function run(): void
    {
        $specialists = [
            ['name' => 'Aisyah', 'role' => 'Lead Nail Artist', 'blurb' => 'Gel art, intricate designs & extensions.', 'photo' => '/assets/img/team-aisyah.jpg', 'sort_order' => 1],
            ['name' => 'Mei Ling', 'role' => 'Senior Stylist', 'blurb' => 'Colour, cuts & scalp care.', 'photo' => '/assets/img/team-meiling.jpg', 'sort_order' => 2],
            ['name' => 'Priya', 'role' => 'Lash & Brow Artist', 'blurb' => 'Lash lifts, extensions & brow shaping.', 'photo' => '/assets/img/team-priya.jpg', 'sort_order' => 3],
            ['name' => 'Farah', 'role' => 'Spa & Facials', 'blurb' => 'Glow facials & hand & foot spa.', 'photo' => '/assets/img/team-farah.jpg', 'sort_order' => 4],
        ];

        foreach ($specialists as $s) {
            Specialist::firstOrCreate(['name' => $s['name']], $s);
        }
    }
}
