<?php

namespace App\Filament\Resources\BusinessSocialPageResource\Pages;

use App\Filament\Resources\BusinessSocialPageResource;
use App\Models\Community;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditBusinessSocialPage extends EditRecord
{
    protected static string $resource = BusinessSocialPageResource::class;

    protected function getHeaderActions(): array
    {
        /** @var Community $record */
        $record = $this->getRecord();

        return [
            Actions\Action::make('open_social')
                ->label('Open Social Hub')
                ->icon('heroicon-o-share')
                ->url(BusinessSocialPageResource::socialUrl($record))
                ->openUrlInNewTab(),
            Actions\Action::make('open_business')
                ->label('Open business page')
                ->icon('heroicon-o-building-storefront')
                ->url(fn (): ?string => BusinessSocialPageResource::businessUrl($record))
                ->openUrlInNewTab()
                ->visible(fn (): bool => filled(BusinessSocialPageResource::businessUrl($record))),
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
