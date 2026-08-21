<?php

namespace App\Filament\Resources\BusinessSocialPageResource\Pages;

use App\Filament\Resources\BusinessSocialPageResource;
use App\Models\Community;
use Filament\Actions;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;

class ViewBusinessSocialPage extends ViewRecord
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
            Actions\EditAction::make(),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Business ↔ Social Hub')
                    ->columns(2)
                    ->schema([
                        Infolists\Components\TextEntry::make('business.business_name')
                            ->label('Business')
                            ->placeholder('—'),
                        Infolists\Components\TextEntry::make('business_id')
                            ->label('Business ID'),
                        Infolists\Components\TextEntry::make('name')
                            ->label('Social page name'),
                        Infolists\Components\TextEntry::make('slug')
                            ->label('Slug')
                            ->copyable(),
                        Infolists\Components\TextEntry::make('description')
                            ->columnSpanFull()
                            ->placeholder('—'),
                        Infolists\Components\TextEntry::make('followers_count')
                            ->label('Followers'),
                        Infolists\Components\TextEntry::make('members_count')
                            ->label('Members'),
                        Infolists\Components\TextEntry::make('posts_count')
                            ->label('Posts'),
                        Infolists\Components\IconEntry::make('is_verified')
                            ->label('Verified')
                            ->boolean(),
                        Infolists\Components\IconEntry::make('is_featured')
                            ->label('Featured')
                            ->boolean(),
                        Infolists\Components\TextEntry::make('created_at')
                            ->dateTime(),
                        Infolists\Components\TextEntry::make('updated_at')
                            ->dateTime(),
                    ]),
            ]);
    }
}
