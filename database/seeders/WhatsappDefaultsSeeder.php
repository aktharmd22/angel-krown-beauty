<?php

namespace Database\Seeders;

use App\Models\Setting;
use App\Models\WhatsappTemplate;
use Illuminate\Database\Seeder;

class WhatsappDefaultsSeeder extends Seeder
{
    public function run(): void
    {
        $adminTpl = WhatsappTemplate::firstOrCreate(
            ['name' => 'Admin — New booking'],
            [
                'body' => "🔔 New Angel Krown booking\n\nName: {{name}}\nPhone: {{phone}}\nService: {{service}} {{package}}\nLocation: {{location}}\nSpecialist: {{specialist}}\nWhen: {{date}} {{time}}\nAddress: {{address}}",
                'language' => 'en',
            ],
        );

        $customerTpl = WhatsappTemplate::firstOrCreate(
            ['name' => 'Customer — Thank you'],
            [
                'body' => "Hi {{name}}! 💖 Thank you for booking with Angel Krown.\n\nWe've received your request for {{service}} on {{date}} at {{time}} and will confirm with you shortly. See you soon! 👑",
                'language' => 'en',
            ],
        );

        $settings = Setting::current();
        $settings->forceFill([
            'admin_template_id' => $settings->admin_template_id ?: $adminTpl->id,
            'customer_template_id' => $settings->customer_template_id ?: $customerTpl->id,
        ])->save();
    }
}
