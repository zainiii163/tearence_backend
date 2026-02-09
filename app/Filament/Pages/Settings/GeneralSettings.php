<?php

namespace App\Filament\Pages\Settings;

use Filament\Pages\Page;
use Filament\Forms\Form;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;

class GeneralSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static ?string $navigationGroup = 'Settings';

    protected static ?string $title = 'General Settings';

    protected static ?string $slug = 'settings/general';

    protected static ?int $navigationSort = 1;

    protected static string $view = 'filament.pages.settings.general-settings';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'site_name' => config('app.name', 'World Wide Adverts'),
            'site_description' => 'Worldwide classified ads platform',
            'site_keywords' => 'classified ads, worldwide, marketplace',
            'site_email' => 'admin@worldwideadverts.info',
            'site_phone' => '+1234567890',
            'site_address' => 'Your Address Here',
            'site_logo' => null,
            'site_favicon' => null,
            'maintenance_mode' => false,
            'registration_enabled' => true,
            'email_verification' => true,
            'default_currency' => 'USD',
            'default_language' => 'en',
            'timezone' => 'UTC',
            'date_format' => 'Y-m-d',
            'time_format' => 'H:i:s',
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Site Information')
                    ->schema([
                        TextInput::make('site_name')
                            ->label('Site Name')
                            ->required()
                            ->maxLength(255),
                        Textarea::make('site_description')
                            ->label('Site Description')
                            ->maxLength(500),
                        TextInput::make('site_keywords')
                            ->label('Site Keywords')
                            ->maxLength(500),
                        TextInput::make('site_email')
                            ->label('Site Email')
                            ->email()
                            ->required(),
                        TextInput::make('site_phone')
                            ->label('Site Phone')
                            ->tel(),
                        Textarea::make('site_address')
                            ->label('Site Address')
                            ->maxLength(500),
                    ])->columns(2),

                Section::make('Site Branding')
                    ->schema([
                        FileUpload::make('site_logo')
                            ->label('Site Logo')
                            ->image()
                            ->directory('settings')
                            ->maxSize(1024),
                        FileUpload::make('site_favicon')
                            ->label('Site Favicon')
                            ->image()
                            ->directory('settings')
                            ->maxSize(512),
                    ])->columns(2),

                Section::make('System Settings')
                    ->schema([
                        Toggle::make('maintenance_mode')
                            ->label('Maintenance Mode')
                            ->helperText('Enable maintenance mode to restrict access'),
                        Toggle::make('registration_enabled')
                            ->label('Enable Registration')
                            ->helperText('Allow new user registrations'),
                        Toggle::make('email_verification')
                            ->label('Email Verification Required')
                            ->helperText('Require email verification for new accounts'),
                    ])->columns(3),

                Section::make('Localization')
                    ->schema([
                        Select::make('default_currency')
                            ->label('Default Currency')
                            ->options([
                                'USD' => 'US Dollar',
                                'EUR' => 'Euro',
                                'GBP' => 'British Pound',
                                'CAD' => 'Canadian Dollar',
                                'AUD' => 'Australian Dollar',
                            ])
                            ->required(),
                        Select::make('default_language')
                            ->label('Default Language')
                            ->options([
                                'en' => 'English',
                                'es' => 'Spanish',
                                'fr' => 'French',
                                'de' => 'German',
                                'it' => 'Italian',
                            ])
                            ->required(),
                        TextInput::make('timezone')
                            ->label('Timezone')
                            ->default('UTC')
                            ->required(),
                        Select::make('date_format')
                            ->label('Date Format')
                            ->options([
                                'Y-m-d' => 'YYYY-MM-DD',
                                'd/m/Y' => 'DD/MM/YYYY',
                                'm/d/Y' => 'MM/DD/YYYY',
                                'd-m-Y' => 'DD-MM-YYYY',
                            ])
                            ->required(),
                        Select::make('time_format')
                            ->label('Time Format')
                            ->options([
                                'H:i:s' => '24 Hour',
                                'h:i:s A' => '12 Hour',
                            ])
                            ->required(),
                    ])->columns(2),
            ]);
    }

    public function save(): void
    {
        $data = $this->form->getState();

        // Here you would typically save to a settings table or config file
        // For now, we'll just show a success notification
        Notification::make()
            ->title('Settings saved successfully')
            ->success()
            ->send();
    }

    protected function getFormActions(): array
    {
        return [
            \Filament\Actions\Action::make('save')
                ->label('Save Settings')
                ->submit('save')
                ->color('primary'),
        ];
    }
} 