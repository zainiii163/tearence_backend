<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use App\Models\Group;
use Filament\Resources\Pages\CreateRecord;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (!empty($data['is_super_admin'])) {
            return $data;
        }
        if (empty($data['group_id'])) {
            return $data;
        }
        $group = Group::find($data['group_id']);
        if (!$group || $group->isTeam()) {
            return $data;
        }
        $data['can_manage_users'] = (bool) $group->can_manage_users;
        $data['can_manage_categories'] = (bool) $group->can_manage_categories;
        $data['can_manage_listings'] = (bool) $group->can_manage_listings;
        $data['can_manage_dashboard'] = (bool) $group->can_manage_dashboard;
        $data['can_view_analytics'] = (bool) $group->can_view_analytics;
        if (is_array($group->permissions)) {
            $data['permissions'] = $group->permissions;
        }
        return $data;
    }
}
