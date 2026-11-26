<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use App\Filament\Support\AdminAccess;
use App\Models\User;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        return DB::transaction(function () use ($data): User {
            $roleName = $data['role_name'] ?? 'user';
            $permissions = is_array($data['permissions_list'] ?? null) ? $data['permissions_list'] : [];
            unset($data['role_name'], $data['permissions_list']);

            if (AdminAccess::isSubAdmin() && $roleName === 'admin') {
                abort(403, 'Sub Admin cannot create Admin users.');
            }

            $data['user_type'] = in_array($roleName, ['admin', 'subAdmin'], true) ? 'admin' : 'user';
            $data['is_verfived'] = true;

            /** @var User $user */
            $user = static::getModel()::create($data);
            $user->syncRoles([$roleName]);

            if ($roleName === 'admin') {
                $user->syncPermissions(Permission::all());
            } elseif ($roleName === 'subAdmin' && AdminAccess::isFullAdmin()) {
                $user->syncPermissions($permissions);
            } else {
                $user->syncPermissions([]);
            }

            return $user;
        });
    }
}
