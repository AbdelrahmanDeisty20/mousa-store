<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Filament\Support\AdminAccess;
use App\Models\User;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Permission;
use UnitEnum;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string | BackedEnum | null $navigationIcon = Heroicon::OutlinedUsers;

    protected static ?int $navigationSort = 2;

    public static function getNavigationGroup(): string | UnitEnum | null
    {
        return __('Sales & Customers');
    }

    public static function getNavigationLabel(): string | Htmlable
    {
        return __('Users');
    }

    public static function getModelLabel(): string
    {
        return __('User');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Users');
    }

    public static function canViewAny(): bool
    {
        return AdminAccess::can('view_users');
    }

    public static function canCreate(): bool
    {
        return AdminAccess::can('create_users');
    }

    public static function canEdit(Model $record): bool
    {
        if (! AdminAccess::can('edit_users')) {
            return false;
        }

        // Prevent subAdmin from editing full Admin accounts
        if (AdminAccess::isSubAdmin() && $record instanceof User && $record->hasRole('admin')) {
            return false;
        }

        return true;
    }

    public static function canDelete(Model $record): bool
    {
        return AdminAccess::can('delete_users', false);
    }

    public static function canDeleteAny(): bool
    {
        return AdminAccess::can('delete_users', false);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['roles', 'permissions'])
            ->when(Auth::id(), fn (Builder $query, int $authId) => $query->where('id', '!=', $authId));
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make(__('User Details'))
                ->columns(2)
                ->schema([
                    TextInput::make('first_name')
                        ->label(__('First Name'))
                        ->required()
                        ->maxLength(255),

                    TextInput::make('last_name')
                        ->label(__('Last Name'))
                        ->required()
                        ->maxLength(255),

                    TextInput::make('email')
                        ->label(__('Email'))
                        ->email()
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->maxLength(255),

                    TextInput::make('phone')
                        ->label(__('Phone'))
                        ->tel()
                        ->required()
                        ->maxLength(20),

                    TextInput::make('password')
                        ->label(__('Password'))
                        ->password()
                        ->revealable()
                        ->minLength(6)
                        ->required(fn (string $operation): bool => $operation === 'create')
                        ->dehydrated(fn (?string $state): bool => filled($state)),

                    Select::make('role_name')
                        ->label(__('Role'))
                        ->options(function (): array {
                            $roles = [
                                'user' => __('Customer'),
                                'subAdmin' => __('Sub Admin'),
                            ];

                            if (AdminAccess::isFullAdmin()) {
                                $roles['admin'] = __('Admin');
                            }

                            return $roles;
                        })
                        ->default('user')
                        ->required()
                        ->live()
                        ->formatStateUsing(function (?User $record): string {
                            if (! $record) {
                                return 'user';
                            }

                            return $record->roles->first()?->name
                                ?? ($record->user_type === 'admin' ? 'admin' : 'user');
                        })
                        ->disabled(fn (string $operation): bool => $operation === 'edit' && AdminAccess::isSubAdmin())
                        ->dehydrated(true),

                    CheckboxList::make('permissions_list')
                        ->label(__('Permissions'))
                        ->options(fn (): array => Permission::query()->orderBy('name')->pluck('name', 'name')->all())
                        ->columns(3)
                        ->columnSpanFull()
                        ->visible(fn (Get $get): bool => AdminAccess::isFullAdmin() && $get('role_name') === 'subAdmin')
                        ->formatStateUsing(fn (?User $record): array => $record ? $record->permissions->pluck('name')->all() : []),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('id', 'desc')
            ->columns([
                TextColumn::make('full_name')
                    ->label(__('Name'))
                    ->state(fn (User $record): string => $record->getFilamentName())
                    ->searchable(['first_name', 'last_name']),

                TextColumn::make('email')
                    ->label(__('Email'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('phone')
                    ->label(__('Phone'))
                    ->searchable(),

                TextColumn::make('role_display')
                    ->label(__('Role'))
                    ->badge()
                    ->state(fn (User $record): string => $record->roles->first()?->name ?? $record->user_type ?? 'user')
                    ->color(fn (string $state): string => match ($state) {
                        'admin' => 'danger',
                        'subAdmin' => 'warning',
                        default => 'gray',
                    }),

                IconColumn::make('is_verfived')
                    ->label(__('Verified'))
                    ->boolean(),

                TextColumn::make('created_at')
                    ->label(__('Created At'))
                    ->dateTime('Y-m-d')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('user_type')
                    ->label(__('User Type'))
                    ->options([
                        'admin' => __('Admin'),
                        'user' => __('Customer'),
                    ]),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
