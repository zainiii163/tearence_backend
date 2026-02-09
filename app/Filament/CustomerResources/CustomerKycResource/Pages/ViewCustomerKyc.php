<?php

namespace App\Filament\CustomerResources\CustomerKycResource\Pages;

use App\Filament\CustomerResources\CustomerKycResource;
use Filament\Resources\Pages\ViewRecord;

class ViewCustomerKyc extends ViewRecord
{
    protected static string $resource = CustomerKycResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\Action::make('submit_kyc')
                ->label('Submit KYC Documents')
                ->icon('heroicon-o-document-arrow-up')
                ->color('primary')
                ->visible(fn ($record) => $record && in_array($record->kyc_status, ['pending', 'rejected']))
                ->url(fn () => route('kyc-submission'))
                ->openUrlInNewTab(),
        ];
    }
}
