<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PageResource\Pages;
use App\Filament\Support\AdminAccess;
use App\Models\Page;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Model;
use UnitEnum;

class PageResource extends Resource
{
    protected static ?string $model = Page::class;

    protected static string | BackedEnum | null $navigationIcon = Heroicon::OutlinedDocumentText;

    protected static ?int $navigationSort = 3;

    public static function getNavigationGroup(): string | UnitEnum | null
    {
        return __('Marketing & CMS');
    }

    public static function getNavigationLabel(): string | Htmlable
    {
        return __('Policy Pages');
    }

    public static function getModelLabel(): string
    {
        return __('Page');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Policy Pages');
    }

    public static function canViewAny(): bool
    {
        return AdminAccess::can('view_pages');
    }

    public static function canCreate(): bool
    {
        return AdminAccess::can('create_pages');
    }

    public static function canEdit(Model $record): bool
    {
        return AdminAccess::can('edit_pages');
    }

    public static function canDelete(Model $record): bool
    {
        return AdminAccess::can('delete_pages');
    }

    private static function sectionsRepeater(string $field, string $label): Repeater
    {
        return Repeater::make($field)
            ->label($label)
            ->default([])
            ->collapsible()
            ->addActionLabel(__('Add Section'))
            ->schema([
                TextInput::make('heading')
                    ->label(__('Section Heading'))
                    ->maxLength(255),

                Textarea::make('body')
                    ->label(__('Section Body'))
                    ->rows(3),

                Repeater::make('points')
                    ->label(__('Bullet Points'))
                    ->simple(
                        TextInput::make('point')
                            ->label(__('Point'))
                            ->required()
                    )
                    ->default([])
                    ->addActionLabel(__('Add Point')),
            ]);
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make(__('Page Identity'))
                ->columns(2)
                ->schema([
                    TextInput::make('slug')
                        ->label(__('Slug'))
                        ->helperText(__('Storefront slugs: privacy-policy, return-policy, terms-and-conditions (/terms), shipping-policy, consumer-protection'))
                        ->required()
                        ->maxLength(100)
                        ->alphaDash()
                        ->unique(ignoreRecord: true)
                        ->dehydrateStateUsing(fn (?string $state): string => strtolower(trim((string) $state))),

                    Toggle::make('is_active')
                        ->label(__('Active (Visible on API)'))
                        ->default(true)
                        ->inline(false),

                    TextInput::make('title_ar')
                        ->label(__('Title (Arabic)'))
                        ->required()
                        ->maxLength(255),

                    TextInput::make('title_en')
                        ->label(__('Title (English)'))
                        ->required()
                        ->maxLength(255),

                    Textarea::make('subtitle_ar')
                        ->label(__('Subtitle (Arabic)'))
                        ->rows(2),

                    Textarea::make('subtitle_en')
                        ->label(__('Subtitle (English)'))
                        ->rows(2),
                ]),

            Tabs::make('SectionsContent')
                ->columnSpanFull()
                ->tabs([
                    Tab::make(__('Arabic Sections'))
                        ->schema([
                            self::sectionsRepeater('sections_ar', __('Arabic Sections')),
                        ]),

                    Tab::make(__('English Sections'))
                        ->schema([
                            self::sectionsRepeater('sections_en', __('English Sections')),
                        ]),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('id', 'asc')
            ->columns([
                TextColumn::make('slug')
                    ->label(__('Slug'))
                    ->badge()
                    ->color('gray')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('title_ar')
                    ->label(__('Title (Arabic)'))
                    ->searchable(),

                TextColumn::make('title_en')
                    ->label(__('Title (English)'))
                    ->searchable(),

                IconColumn::make('is_active')
                    ->label(__('Active'))
                    ->boolean()
                    ->sortable(),

                TextColumn::make('updated_at')
                    ->label(__('Updated At'))
                    ->dateTime('Y-m-d H:i')
                    ->sortable(),
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
            'index' => Pages\ListPages::route('/'),
            'create' => Pages\CreatePage::route('/create'),
            'edit' => Pages\EditPage::route('/{record}/edit'),
        ];
    }
}
