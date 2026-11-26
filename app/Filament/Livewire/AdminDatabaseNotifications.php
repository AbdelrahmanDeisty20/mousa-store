<?php

namespace App\Filament\Livewire;

use Filament\Livewire\DatabaseNotifications;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;

class AdminDatabaseNotifications extends DatabaseNotifications
{
    public function getNotificationsQuery(): Builder | Relation
    {
        $user = $this->getUser();

        if (! $user) {
            abort(401);
        }

        return $user->filamentNotifications()->where('data->format', 'filament');
    }
}
