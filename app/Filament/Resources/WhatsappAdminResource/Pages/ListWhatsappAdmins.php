<?php

namespace App\Filament\Resources\WhatsappAdminResource\Pages;

use App\Filament\Resources\WhatsappAdminResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListWhatsappAdmins extends ListRecords
{
    protected static string $resource = WhatsappAdminResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
