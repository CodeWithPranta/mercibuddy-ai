<?php

namespace App\Filament\Artisan\Pages;

use App\Models\ContactDetail;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;

class ContactMedia extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-radio';

    protected string $view = 'filament.artisan.pages.contact-media';

    public ?array $data = [];

    public function mount(): void
    {
        $contactInfo = ContactDetail::where('user_id', Filament::auth()->user()->id)->first();

        if ($contactInfo) {
            $this->form->fill($contactInfo->attributesToArray());
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
                        TextInput::make('phone')->tel()->required(),
                        TextInput::make('email')->email()->required(),
                        TextInput::make('facebook')->label('Facebook page/profile link')
                            ->placeholder('e.g. https://web.facebook.com/webmastermazumder')
                            ->url(),
                        TextInput::make('linkedin')->label('Linkedin profile link')
                            ->placeholder('e.g. https://www.linkedin.com/in/mazumderp07')
                            ->url(),
                        TextInput::make('whatsapp')->label('Whatsapp number (with country code)')
                            ->placeholder('e.g. in France start from 33')->tel(),
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

        $existingContactInfo = ContactDetail::where('user_id', Filament::auth()->user()->id)->first();

        if ($existingContactInfo) {
            $existingContactInfo->update($data);
        } else {
            $data['user_id'] = Filament::auth()->user()->id;
            ContactDetail::create($data);
        }

        Notification::make()
            ->success()
            ->title(__('filament-panels::resources/pages/edit-record.notifications.saved.title'))
            ->send();
    }
}
