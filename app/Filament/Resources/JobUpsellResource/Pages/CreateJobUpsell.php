<?php

namespace App\Filament\Resources\JobUpsellResource\Pages;

use App\Filament\Resources\JobUpsellResource;
use App\Models\Job;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Schema;

class CreateJobUpsell extends CreateRecord
{
    protected static string $resource = JobUpsellResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['upsellable_type'] = Job::class;

        if (isset($data['duration_days']) && ! isset($data['duration_months'])) {
            $data['duration_months'] = max(1, (int) ceil(((int) $data['duration_days']) / 30));
        }
        if (isset($data['payment_transaction_id']) && ! isset($data['transaction_id'])) {
            $data['transaction_id'] = $data['payment_transaction_id'];
        }

        unset($data['duration_days'], $data['payment_transaction_id'], $data['starts_at'], $data['listing_id']);

        return collect($data)
            ->filter(fn ($value, $key) => Schema::hasColumn((new \App\Models\JobUpsell)->getTable(), (string) $key))
            ->all();
    }
}
