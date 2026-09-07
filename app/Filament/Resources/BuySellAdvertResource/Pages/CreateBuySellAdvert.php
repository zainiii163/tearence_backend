<?php

namespace App\Filament\Resources\BuySellAdvertResource\Pages;

use App\Filament\Resources\BuySellAdvertResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class CreateBuySellAdvert extends CreateRecord
{
    protected static string $resource = BuySellAdvertResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (empty($data['user_id'])) {
            $data['user_id'] = Auth::id();
        }

        if (empty($data['status'])) {
            $data['status'] = 'active';
        }

        if (empty($data['currency'])) {
            $data['currency'] = 'USD';
        }

        // UUID primary key (HasUuids usually handles this; keep an explicit fallback)
        if (empty($data['id'])) {
            $data['id'] = (string) Str::uuid();
        }

        return $data;
    }
}
