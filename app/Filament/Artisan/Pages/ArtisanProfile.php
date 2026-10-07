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
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Grid;
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

        if ($artisanInfo) {
            $this->form->fill($artisanInfo->attributesToArray());
        }
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
                        TextInput::make('hourly_rate')
                            ->label('Hourly price')
                            ->numeric()
                            ->minValue(0)
                            ->prefix(fn (callable $get): string => match ($get('currency')) {
                                'GBP' => '£',
                                'USD' => '$',
                                'CHF' => 'CHF ',
                                default => '€',
                            })
                            ->suffix('/ hour'),
                        Select::make('currency')
                            ->label('Currency')
                            ->options([
                                'EUR' => 'EUR (€)',
                                'USD' => 'USD ($)',
                                'GBP' => 'GBP (£)',
                                'CHF' => 'CHF',
                            ])
                            ->default('EUR')
                            ->live()
                            ->required(),
                        Toggle::make('is_active')
                            ->label('Account status')
                            ->helperText('Active profiles are available for new requests.')
                            ->default(true)
                            ->onColor('success')
                            ->offColor('danger')
                            ->onIcon('heroicon-s-check')
                            ->offIcon('heroicon-s-x-mark'),
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
                        TextInput::make('website')->placeholder('e.g. https://codewithpranta.com')->url(),
                        TextInput::make('video_cv')->label('Video CV link')->placeholder('e.g. https://youtu.be/dZABLXpN3Lk?si=zDowB-mx72jz3rw0')->url(),
                        Repeater::make('language_proficiencies')
                            ->label('Language proficiency')
                            ->schema([
                                Select::make('language')
                                    ->options([
                                        'English' => 'English',
                                        'French' => 'French',
                                        'German' => 'German',
                                        'Bengali' => 'Bengali',
                                        'Spanish' => 'Spanish',
                                        'Arabic' => 'Arabic',
                                        'Portuguese' => 'Portuguese',
                                        'Italian' => 'Italian',
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
