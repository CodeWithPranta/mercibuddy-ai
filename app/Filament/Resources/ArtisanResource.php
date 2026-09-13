<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ArtisanResource\Pages\CreateArtisan;
use App\Filament\Resources\ArtisanResource\Pages\EditArtisan;
use App\Filament\Resources\ArtisanResource\Pages\ListArtisans;
use App\Models\Artisan;
use App\Models\Category;
use App\Models\Country;
use App\Models\State;
use App\Models\User;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ArtisanResource extends Resource
{
    protected static ?string $model = Artisan::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-paint-brush';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                FileUpload::make('cover_photo')
                    ->label('Cover photo (16:9)')
                    ->image()
                    ->imageEditor()
                    ->imageEditorAspectRatios([
                        '16:9',
                        '4:3',
                        '1:1',
                    ])
                    ->required(),
                FileUpload::make('profile_photo')
                    ->image()
                    ->imageEditor()
                    ->imageEditorAspectRatios([
                        '16:9',
                        '4:3',
                        '1:1',
                    ])
                    ->required(),
                TextInput::make('full_name')
                    ->minLength(3)
                    ->maxLength(255)
                    ->required(),
                Select::make('user_id')
                    ->label('Assign a User')
                    ->options(
                        User::orderBy('name')->pluck('email', 'id')
                    )
                    ->unique(ignoreRecord: true)
                    ->required()
                    ->searchable(),

                Select::make('category_id')
                    ->label('Service category')
                    ->options(
                        Category::orderBy('name')->pluck('name', 'id')
                    )
                    ->required(),
                TextInput::make('profession')
                    ->label('Profession (e.g. Web Developer)')
                    ->minLength(2)->required(),
                Select::make('profession_type')
                    ->options([
                        'Full-time' => 'Full-time',
                        'Part-time' => 'Part-time',
                        'Contract' => 'Contract',
                    ])
                    ->required(),
                TextInput::make('experience_in_year')
                    ->numeric()
                    ->required(),

                TextInput::make('last_education')->placeholder('e.g. Diploma in Computer Science')
                    ->label('Last educational qualification')
                    ->required(),
                DatePicker::make('date_of_birth')
                    ->minDate(now()->subYears(100))
                    ->maxDate(now()->subYear(13))
                    ->required(),
                Select::make('country_id')
                    ->label('Country')
                    ->options(Country::pluck('name', 'id')->toArray())
                    ->live()
                    ->reactive()
                    ->required(),
                Select::make('state_id')
                    ->label('Region')
                    ->options(function (callable $get) {
                        $country = Country::find($get('country_id'));
                        if (! $country) {
                            return [0001 => 'Select a country first'];
                        }

                        return $country->states->pluck('name', 'id');
                    })
                    ->reactive()
                    ->required(),
                Select::make('city_id')
                    ->label('City')
                    ->options(function (callable $get) {
                        $state = State::find($get('state_id'));
                        if (! $state) {
                            return [0001 => 'Select a region first'];
                        }

                        return $state->cities->pluck('name', 'id');
                    })
                    ->reactive()
                    ->required(),
                Textarea::make('address')->required(),
                TextInput::make('website')->url(),
                Textarea::make('biography')
                    ->label('Write about yourself')
                    ->minLength(200)
                    ->maxLength(5000)
                    ->required()
                    ->rows(5)
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('full_name')
                    ->searchable(),
                TextColumn::make('category.name')
                    ->numeric()
                    ->sortable(),
                ImageColumn::make('profile_photo'),
                ImageColumn::make('cover_photo'),
                TextColumn::make('experience_in_year')
                    ->searchable(),
                TextColumn::make('website')
                    ->searchable(),
                TextColumn::make('last_education')
                    ->searchable(),
                TextColumn::make('date_of_birth')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('profession')
                    ->searchable(),
                TextColumn::make('profession_type')
                    ->searchable(),
                TextColumn::make('country.name')
                    ->searchable(),
                TextColumn::make('state.name')
                    ->searchable(),
                TextColumn::make('city.name')
                    ->searchable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListArtisans::route('/'),
            'create' => CreateArtisan::route('/create'),
            'edit' => EditArtisan::route('/{record}/edit'),
        ];
    }
}
