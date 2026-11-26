<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ContactResource\Pages;
use App\Filament\Support\AdminAccess;
use App\Models\Contact;
use BackedEnum;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Model;
use UnitEnum;

class ContactResource extends Resource
{
    protected static ?string $model = Contact::class;

    protected static string | BackedEnum | null $navigationIcon = Heroicon::OutlinedPhone;

    protected static ?int $navigationSort = 2;

    public static function getNavigationGroup(): string | UnitEnum | null
    {
        return __('Settings & Support');
    }

    public static function getNavigationLabel(): string | Htmlable
    {
        return __('Contact Information');
    }

    public static function getModelLabel(): string
    {
        return __('Contact Information');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Contact Information');
    }

    public static function canViewAny(): bool
    {
        return AdminAccess::can('view_contacts');
    }

    public static function canCreate(): bool
    {
        return AdminAccess::can('edit_contacts', false) && Contact::count() === 0;
    }

    public static function canEdit(Model $record): bool
    {
        return AdminAccess::can('edit_contacts', false);
    }

    public static function canDelete(Model $record): bool
    {
        return false;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make(__('Store Contact Details'))
                ->columns(2)
                ->schema([
                    TextInput::make('email')
                        ->label(__('Email'))
                        ->email()
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->maxLength(255),

                    TextInput::make('phone')
                        ->label(__('Phone'))
                        ->required()
                        ->maxLength(50),

                    TextInput::make('whatsapp')
                        ->label(__('WhatsApp'))
                        ->maxLength(50),

                    TextInput::make('facebook')
                        ->label(__('Facebook'))
                        ->maxLength(255),

                    Textarea::make('address_ar')
                        ->label(__('Address (Arabic)'))
                        ->required()
                        ->rows(3),

                    Textarea::make('address_en')
                        ->label(__('Address (English)'))
                        ->required()
                        ->rows(3),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('email')->label(__('Email')),
                TextColumn::make('phone')->label(__('Phone')),
                TextColumn::make('whatsapp')->label(__('WhatsApp')),
                TextColumn::make('address_ar')->label(__('Address'))->limit(40),
            ])
            ->recordActions([
                EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListContacts::route('/'),
            'create' => Pages\CreateContact::route('/create'),
            'edit' => Pages\EditContact::route('/{record}/edit'),
        ];
    }
}
