<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use App\Filament\Support\AdminAccess;
use App\Models\User;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        return DB::transaction(function () use ($record, $data): User {
            /** @var User $record */
            $roleName = $data['role_name'] ?? ($record->roles->first()?->name ?? 'user');
            $permissions = is_array($data['permissions_list'] ?? null) ? $data['permissions_list'] : [];
            unset($data['role_name'], $data['permissions_list']);

            if (AdminAccess::isSubAdmin()) {
                $currentRole = $record->roles->first()?->name ?? ($record->user_type === 'admin' ? 'admin' : 'user');
                if ($roleName !== $currentRole || $record->hasRole('admin')) {
                    abort(403, 'Sub Admin cannot change user roles or edit Admin users.');
                }
            }

            $data['user_type'] = in_array($roleName, ['admin', 'subAdmin'], true) ? 'admin' : 'user';

            $record->update($data);

            if (AdminAccess::isFullAdmin()) {
                $record->syncRoles([$roleName]);

                if ($roleName === 'admin') {
                    $record->syncPermissions(Permission::all());
                } elseif ($roleName === 'subAdmin') {
                    $record->syncPermissions($permissions);
                } else {
                    $record->syncPermissions([]);
                }
            }

            return $record;
        });
    }
}
