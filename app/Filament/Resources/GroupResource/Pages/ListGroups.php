<?php

namespace App\Filament\Resources\GroupResource\Pages;

use App\Filament\Resources\GroupResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListGroups extends ListRecords
{
    protected static string $resource = GroupResource::class;

    public function getTitle(): string
    {
        return 'Teams & Roles';
    }

    public function getHeading(): string
    {
        return 'Teams & Roles';
    }

    public function getSubheading(): ?string
    {
        return 'Department teams (HR, Accounts, Legal, …) and their sub-roles. Assign people from Users or from a role’s Members tab.';
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('New team / role'),
        ];
    }
}
