<?php

namespace App\Filament\Resources\WhatsappTemplateResource\Pages;

use App\Filament\Resources\WhatsappTemplateResource;
use Filament\Resources\Pages\CreateRecord;

class CreateWhatsappTemplate extends CreateRecord
{
    protected static string $resource = WhatsappTemplateResource::class;

    protected function afterCreate(): void
    {
        WhatsappTemplateResource::autoSubmit($this->record);
    }
}
