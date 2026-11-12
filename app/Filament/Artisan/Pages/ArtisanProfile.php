<?php

namespace App\Filament\Artisan\Pages;

use App\Models\Artisan;
use App\Models\Category;
use App\Models\Country;
use App\Models\State;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class ArtisanProfile extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-user';

    protected string $view = 'filament.artisan.pages.artisan-profile';

    public ?array $data = ['biography' => ''];

    public function mount(): void
    {
        $artisanInfo = Artisan::where('user_id', Filament::auth()->user()->id)->first();

        $this->form->fill($artisanInfo?->attributesToArray());
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make([
                    'default' => 1,
                    'sm' => 2,
                ])
                    ->schema([
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
                        Select::make('category_id')
                            ->label('Service category')
                            ->options(
                                Category::orderBy('name')->pluck('name', 'id')
                            )
                            ->live()
                            ->afterStateUpdated(fn (Set $set) => $set('profession', null))
                            ->required(),
                        Select::make('profession')
                            ->label('Profession')
                            ->options(fn (Get $get): array => Category::professionOptions(
                                $get('category_id') ? (int) $get('category_id') : null
                            ))
                            ->searchable()
                            ->live()
                            ->required(),
                        TextInput::make('main_profession')
                            ->label('Main profession')
                            ->helperText('Shown on your listing card.')
                            ->maxLength(255),
                        TagsInput::make('skills')
                            ->label('Skills')
                            ->helperText('Add 3 to 5 skills. These are shown on your listing card.')
                            ->required(fn (Get $get): bool => filled($get('profession')))
                            ->rules(fn (Get $get): array => filled($get('profession'))
                                ? ['array', 'min:3', 'max:5']
                                : [])
                            ->nestedRecursiveRules(['string', 'max:50'])
                            ->visible(fn (Get $get): bool => filled($get('profession')))
                            ->reorderable()
                            ->columnSpanFull(),
                        TextInput::make('hourly_rate')
                            ->label('Price')
                            ->numeric()
                            ->minValue(0)
                            ->prefix(fn (Get $get): string => Artisan::currencyPrefix($get('currency')))
                            ->visible(fn (Get $get): bool => Artisan::rateTypeUsesAmount($get('rate_type') ?? 'Per hour')),
                        Select::make('currency')
                            ->label('Currency')
                            ->options(Artisan::currencyOptions())
                            ->default('EUR')
                            ->searchable()
                            ->live()
                            ->required(),
                        Select::make('rate_type')
                            ->label('Rate type')
                            ->helperText('How do you charge for your services?')
                            ->options(Artisan::rateTypeOptions())
                            ->default('Per hour')
                            ->live()
                            ->afterStateUpdated(function (Set $set, ?string $state): void {
                                if (! Artisan::rateTypeUsesAmount($state)) {
                                    $set('hourly_rate', null);
                                }
                            })
                            ->required(),
                        Toggle::make('is_active')
                            ->label('Account status')
                            ->helperText('Active profiles are available for new requests.')
                            ->default(true)
                            ->onColor('success')
                            ->offColor('danger')
                            ->onIcon('heroicon-s-check')
                            ->offIcon('heroicon-s-x-mark'),

                        DatePicker::make('date_of_birth')
                            ->minDate(now()->subYears(100))
                            ->maxDate(now()->subYear(13))
                            ->required(),
                        Select::make('country_id')
                            ->label('Country')
                            ->options(Country::ordered()->pluck('name', 'id')->toArray())
                            ->searchable()
                            ->live()
                            ->afterStateUpdated(function (Set $set): void {
                                $set('state_id', null);
                                $set('city_id', null);
                            })
                            ->required(),
                        Select::make('state_id')
                            ->label('State')
                            ->key(fn (Get $get): string => 'state-'.($get('country_id') ?? 'none'))
                            ->dynamicOptions(false)
                            ->options(fn (Get $get): array => ($country = Country::find($get('country_id')))
                                ? $country->states()->orderBy('name')->pluck('name', 'id')->all()
                                : [])
                            ->placeholder(fn (Get $get): string => $get('country_id')
                                ? 'Select a state'
                                : 'Select a country first')
                            ->searchable()
                            ->live()
                            ->afterStateUpdated(fn (Set $set) => $set('city_id', null))
                            ->required(),
                        Select::make('city_id')
                            ->label('City')
                            ->key(fn (Get $get): string => 'city-'.($get('state_id') ?? 'none'))
                            ->dynamicOptions(false)
                            ->options(fn (Get $get): array => ($state = State::find($get('state_id')))
                                ? $state->cities()->orderBy('name')->pluck('name', 'id')->all()
                                : [])
                            ->placeholder(fn (Get $get): string => $get('state_id')
                                ? 'Select a city'
                                : 'Select a state first')
                            ->searchable()
                            ->required(),
                        Textarea::make('address')->required(),
                        Repeater::make('language_proficiencies')
                            ->label('Language proficiency')
                            ->schema([
                                Select::make('language')
                                    ->options([
                                        'Arabic' => 'Arabic',
                                        'Bengali' => 'Bengali',
                                        'Chinese' => 'Chinese',
                                        'English' => 'English',
                                        'French' => 'French',
                                        'German' => 'German',
                                        'Hindi' => 'Hindi',
                                        'Indonesian' => 'Indonesian',
                                        'Japanese' => 'Japanese',
                                        'Portuguese' => 'Portuguese',
                                        'Russian' => 'Russian',
                                        'Spanish' => 'Spanish',
                                        'Urdu' => 'Urdu',
                                    ])
                                    ->required(),
                                Select::make('proficiency')
                                    ->options([
                                        'Basic' => 'Basic',
                                        'Conversational' => 'Conversational',
                                        'Professional' => 'Professional',
                                        'Fluent' => 'Fluent',
                                        'Native' => 'Native',
                                    ])
                                    ->required(),
                            ])
                            ->columns(2)
                            ->defaultItems(0)
                            ->columnSpanFull(),
                        RichEditor::make('biography')
                            ->label('Write about yourself')
                            ->required()
                            ->columnSpanFull(),
                    ]),
            ])
            ->statePath('data');
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label(__('filament-panels::resources/pages/edit-record.form.actions.save.label'))
                ->color('success')
                ->submit('save'),
        ];
    }

    public function save(): void
    {
        $data = $this->form->getState();
        $userId = Filament::auth()->user()->id;
        $existingArtisanInfo = Artisan::where('user_id', $userId)->first();

        // dd($existingArtisanInfo);

        if ($existingArtisanInfo) {
            // $data['user_id'] = Filament::auth()->user()->id;
            $existingArtisanInfo->update($data);
        } else {
            $data['user_id'] = $userId;
            // dd($this->form->getState());
            Artisan::create($data);
        }

        // $data = $this->form->getState();
        // $userId = Filament::auth()->user()->id;

        // $existingArtisanInfo = Artisan::firstOrNew(['user_id' => $userId]);
        // $existingArtisanInfo->fill($data);
        // $existingArtisanInfo->save();

        Notification::make()
            ->success()
            ->title(__('filament-panels::resources/pages/edit-record.notifications.saved.title'))
            ->send();
    }
}
