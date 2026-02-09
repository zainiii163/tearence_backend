<?php

namespace App\Filament\Pages\Settings;

use Filament\Pages\Page;
use Filament\Forms\Form;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;

class SocialSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-share';

    protected static ?string $navigationGroup = 'Settings';

    protected static ?string $title = 'Social Media Settings';

    protected static ?string $slug = 'settings/social';

    protected static ?int $navigationSort = 4;

    protected static string $view = 'filament.pages.settings.social-settings';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'facebook_enabled' => true,
            'facebook_app_id' => '',
            'facebook_app_secret' => '',
            'facebook_page_url' => '',
            'twitter_enabled' => true,
            'twitter_api_key' => '',
            'twitter_api_secret' => '',
            'twitter_username' => '',
            'instagram_enabled' => false,
            'instagram_client_id' => '',
            'instagram_client_secret' => '',
            'instagram_username' => '',
            'linkedin_enabled' => false,
            'linkedin_client_id' => '',
            'linkedin_client_secret' => '',
            'linkedin_company_url' => '',
            'youtube_enabled' => false,
            'youtube_channel_url' => '',
            'youtube_api_key' => '',
            'google_analytics_id' => '',
            'facebook_pixel_id' => '',
            'social_share_enabled' => true,
            'social_login_enabled' => true,
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Facebook Settings')
                    ->schema([
                        Toggle::make('facebook_enabled')
                            ->label('Enable Facebook Integration')
                            ->helperText('Enable Facebook login and sharing'),
                        TextInput::make('facebook_app_id')
                            ->label('Facebook App ID')
                            ->maxLength(255),
                        TextInput::make('facebook_app_secret')
                            ->label('Facebook App Secret')
                            ->password()
                            ->maxLength(255),
                        TextInput::make('facebook_page_url')
                            ->label('Facebook Page URL')
                            ->url()
                            ->maxLength(255),
                    ])->columns(2),

                Section::make('Twitter Settings')
                    ->schema([
                        Toggle::make('twitter_enabled')
                            ->label('Enable Twitter Integration')
                            ->helperText('Enable Twitter login and sharing'),
                        TextInput::make('twitter_api_key')
                            ->label('Twitter API Key')
                            ->maxLength(255),
                        TextInput::make('twitter_api_secret')
                            ->label('Twitter API Secret')
                            ->password()
                            ->maxLength(255),
                        TextInput::make('twitter_username')
                            ->label('Twitter Username')
                            ->maxLength(255),
                    ])->columns(2),

                Section::make('Instagram Settings')
                    ->schema([
                        Toggle::make('instagram_enabled')
                            ->label('Enable Instagram Integration')
                            ->helperText('Enable Instagram login and sharing'),
                        TextInput::make('instagram_client_id')
                            ->label('Instagram Client ID')
                            ->maxLength(255),
                        TextInput::make('instagram_client_secret')
                            ->label('Instagram Client Secret')
                            ->password()
                            ->maxLength(255),
                        TextInput::make('instagram_username')
                            ->label('Instagram Username')
                            ->maxLength(255),
                    ])->columns(2),

                Section::make('LinkedIn Settings')
                    ->schema([
                        Toggle::make('linkedin_enabled')
                            ->label('Enable LinkedIn Integration')
                            ->helperText('Enable LinkedIn login and sharing'),
                        TextInput::make('linkedin_client_id')
                            ->label('LinkedIn Client ID')
                            ->maxLength(255),
                        TextInput::make('linkedin_client_secret')
                            ->label('LinkedIn Client Secret')
                            ->password()
                            ->maxLength(255),
                        TextInput::make('linkedin_company_url')
                            ->label('LinkedIn Company URL')
                            ->url()
                            ->maxLength(255),
                    ])->columns(2),

                Section::make('YouTube Settings')
                    ->schema([
                        Toggle::make('youtube_enabled')
                            ->label('Enable YouTube Integration')
                            ->helperText('Enable YouTube video embedding'),
                        TextInput::make('youtube_channel_url')
                            ->label('YouTube Channel URL')
                            ->url()
                            ->maxLength(255),
                        TextInput::make('youtube_api_key')
                            ->label('YouTube API Key')
                            ->maxLength(255),
                    ])->columns(2),

                Section::make('Analytics & Tracking')
                    ->schema([
                        TextInput::make('google_analytics_id')
                            ->label('Google Analytics ID')
                            ->helperText('GA4 Measurement ID (e.g., G-XXXXXXXXXX)')
                            ->maxLength(255),
                        TextInput::make('facebook_pixel_id')
                            ->label('Facebook Pixel ID')
                            ->helperText('Facebook Pixel ID for tracking')
                            ->maxLength(255),
                    ])->columns(2),

                Section::make('Social Features')
                    ->schema([
                        Toggle::make('social_share_enabled')
                            ->label('Enable Social Sharing')
                            ->helperText('Allow users to share content on social media'),
                        Toggle::make('social_login_enabled')
                            ->label('Enable Social Login')
                            ->helperText('Allow users to login with social media accounts'),
                    ])->columns(2),
            ]);
    }

    public function save(): void
    {
        $data = $this->form->getState();

        // Here you would typically save to a settings table or config file
        Notification::make()
            ->title('Social media settings saved successfully')
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
            \Filament\Actions\Action::make('test_connection')
                ->label('Test Connection')
                ->icon('heroicon-o-wifi')
                ->color('secondary')
                ->action(function () {
                    // Here you would test the social media connections
                    Notification::make()
                        ->title('Connection test completed')
                        ->info()
                        ->send();
                }),
        ];
    }
} 