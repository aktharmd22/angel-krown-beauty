<?php

namespace App\Filament\Resources\WhatsappTemplateResource\Pages;

use App\Filament\Resources\WhatsappTemplateResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditWhatsappTemplate extends EditRecord
{
    protected static string $resource = WhatsappTemplateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('submit')
                ->label('Submit to Meta')
                ->icon('heroicon-o-paper-airplane')
                ->action(fn () => WhatsappTemplateResource::runMeta($this->record, 'submit')),
            Actions\Action::make('sync')
                ->label('Refresh status')
                ->icon('heroicon-o-arrow-path')
                ->color('gray')
                ->action(fn () => WhatsappTemplateResource::runMeta($this->record, 'syncStatus'))
                ->visible(fn () => filled($this->record->meta_template_id)),
            Actions\DeleteAction::make(),
        ];
    }

    protected function afterSave(): void
    {
        WhatsappTemplateResource::autoSubmit($this->record);
    }
}
