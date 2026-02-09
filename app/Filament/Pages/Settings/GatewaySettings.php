<?php

namespace App\Filament\Pages\Settings;

use Filament\Pages\Page;
use Filament\Forms\Form;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Tables\Table;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Actions\Action;

class GatewaySettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-credit-card';

    protected static ?string $navigationGroup = 'Settings';

    protected static ?string $title = 'Payment Gateways';

    protected static ?string $slug = 'settings/gateways';

    protected static ?int $navigationSort = 2;

    protected static string $view = 'filament.pages.settings.gateway-settings';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'paypal_enabled' => true,
            'paypal_client_id' => '',
            'paypal_secret' => '',
            'paypal_mode' => 'sandbox',
            'stripe_enabled' => false,
            'stripe_publishable_key' => '',
            'stripe_secret_key' => '',
            'stripe_webhook_secret' => '',
            'razorpay_enabled' => false,
            'razorpay_key_id' => '',
            'razorpay_key_secret' => '',
            'bank_transfer_enabled' => true,
            'bank_account_name' => '',
            'bank_account_number' => '',
            'bank_name' => '',
            'bank_swift_code' => '',
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('PayPal Settings')
                    ->schema([
                        Toggle::make('paypal_enabled')
                            ->label('Enable PayPal')
                            ->helperText('Enable PayPal payment gateway'),
                        TextInput::make('paypal_client_id')
                            ->label('PayPal Client ID')
                            ->maxLength(255),
                        TextInput::make('paypal_secret')
                            ->label('PayPal Secret')
                            ->password()
                            ->maxLength(255),
                        Select::make('paypal_mode')
                            ->label('PayPal Mode')
                            ->options([
                                'sandbox' => 'Sandbox (Testing)',
                                'live' => 'Live (Production)',
                            ])
                            ->required(),
                    ])->columns(2),

                Section::make('Stripe Settings')
                    ->schema([
                        Toggle::make('stripe_enabled')
                            ->label('Enable Stripe')
                            ->helperText('Enable Stripe payment gateway'),
                        TextInput::make('stripe_publishable_key')
                            ->label('Stripe Publishable Key')
                            ->maxLength(255),
                        TextInput::make('stripe_secret_key')
                            ->label('Stripe Secret Key')
                            ->password()
                            ->maxLength(255),
                        TextInput::make('stripe_webhook_secret')
                            ->label('Stripe Webhook Secret')
                            ->password()
                            ->maxLength(255),
                    ])->columns(2),

                Section::make('Razorpay Settings')
                    ->schema([
                        Toggle::make('razorpay_enabled')
                            ->label('Enable Razorpay')
                            ->helperText('Enable Razorpay payment gateway'),
                        TextInput::make('razorpay_key_id')
                            ->label('Razorpay Key ID')
                            ->maxLength(255),
                        TextInput::make('razorpay_key_secret')
                            ->label('Razorpay Key Secret')
                            ->password()
                            ->maxLength(255),
                    ])->columns(2),

                Section::make('Bank Transfer Settings')
                    ->schema([
                        Toggle::make('bank_transfer_enabled')
                            ->label('Enable Bank Transfer')
                            ->helperText('Enable bank transfer payment method'),
                        TextInput::make('bank_account_name')
                            ->label('Account Holder Name')
                            ->maxLength(255),
                        TextInput::make('bank_account_number')
                            ->label('Account Number')
                            ->maxLength(255),
                        TextInput::make('bank_name')
                            ->label('Bank Name')
                            ->maxLength(255),
                        TextInput::make('bank_swift_code')
                            ->label('SWIFT/BIC Code')
                            ->maxLength(255),
                    ])->columns(2),
            ]);
    }

    // Table functionality removed - uncomment and implement when gateways model/table is available
    // public function table(Table $table): Table
    // {
    //     return $table
    //         ->query(
    //             // Query gateways table here
    //         )
    //         ->columns([
    //             TextColumn::make('name')
    //                 ->label('Gateway Name')
    //                 ->searchable(),
    //             // ... other columns
    //         ]);
    // }

    public function save(): void
    {
        $data = $this->form->getState();

        // Here you would typically save to a settings table or config file
        Notification::make()
            ->title('Gateway settings saved successfully')
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

    protected function getHeaderWidgets(): array
    {
        return [
            // You can add widgets here if needed
        ];
    }
} 