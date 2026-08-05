<?php

namespace App\Filament\Resources\GroupResource\Pages;

use App\Filament\Resources\GroupResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditGroup extends EditRecord
{
    protected static string $resource = GroupResource::class;

    public function getTitle(): string
    {
        $record = $this->getRecord();

        return $record->fullLabel();
    }

    public function getSubheading(): ?string
    {
        $record = $this->getRecord();

        return $record->isTeam()
            ? 'Department team — manage sub-roles and assign members below.'
            : 'Sub-role — assign members and edit portal permissions.';
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->visible(fn () => auth()->user()?->is_super_admin),
        ];
    }
}
