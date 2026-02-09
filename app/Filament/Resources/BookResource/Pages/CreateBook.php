<?php

namespace App\Filament\Resources\BookResource\Pages;

use App\Filament\Resources\BookResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateBook extends CreateRecord
{
    protected static string $resource = BookResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Set the category to Books category
        $booksCategory = \App\Models\Category::where('name', 'Books')->first();
        if ($booksCategory) {
            $data['category_id'] = $booksCategory->category_id;
        }

        // Set default values
        $data['currency_id'] = 1; // Default currency
        $data['package_id'] = 1; // Default package
        $data['type'] = 'international';
        $data['approval_status'] = 'pending';

        return $data;
    }
}
