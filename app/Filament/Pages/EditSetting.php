<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;

class EditSetting extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected string $view = 'filament.pages.edit-setting';

    public ?array $data = [];

    public function mount(): void
    {
        $setting = Setting::first();

        if ($setting) {
            $this->form->fill($setting->attributesToArray());
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
                        Fieldset::make('Icon and Logo')
                            ->schema([
                                FileUpload::make('favicon')
                                    ->label('Favicon PNG (32*32)px')
                                    ->image()
                                    ->imageEditor()
                                    ->imageEditorAspectRatios([
                                        '1:1',
                                    ])
                                    ->required(),
                                FileUpload::make('logo')
                                    ->label('Logo (max. 200*60)px')
                                    ->image()
                                    ->imageEditor()
                                    ->imageEditorAspectRatios([
                                        '16:9',
                                        '4:3',
                                        '1:1',
                                    ])
                                    ->required(),
                                FileUpload::make('og_image')
                                    ->label('Social share image')
                                    ->image()
                                    ->imageEditor()
                                    ->imageEditorAspectRatios([
                                        '1:1',
                                    ])
                                    ->required(),
                            ]),
                    ]),

                Grid::make([
                    'default' => 1,
                    'sm' => 2,
                ])
                    ->schema([
                        Fieldset::make('Website hero section')
                            ->schema([
                                TextInput::make('hero_title')
                                    ->minLength(3)
                                    ->maxLength(500)
                                    ->required()
                                    ->columnSpan(2),
                                Textarea::make('title_text')->columnSpan(2)->required(),
                            ]),
                        Fieldset::make('Search engine optimization')
                            ->schema([
                                Textarea::make('meta_description')
                                    ->columnSpan(2)->required(),
                                Textarea::make('keywords')
                                    ->columnSpan(2)->required(),
                            ]),
                        Textarea::make('copyright_text')->columnSpan(2)->required(),
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
        // Retrieve the data from the form
        $data = $this->form->getState();

        // Attempt to find an existing record
        $existingSetting = Setting::first();

        if ($existingSetting) {
            // If an existing record was found, update it with the new data
            $existingSetting->update($data);
        } else {
            // If no existing record was found, create a new one
            Setting::create($data);
        }

        Notification::make()
            ->success()
            ->title(__('filament-panels::resources/pages/edit-record.notifications.saved.title'))
            ->send();
    }
}
