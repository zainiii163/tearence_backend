<?php

namespace App\Filament\Pages\Settings;

use Filament\Pages\Page;
use Filament\Forms\Form;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\HtmlString;

/**
 * Clive: track money-in across cards / Stripe / PayPal / crypto.
 * Values persist in cache (admin UI). Live checkout still uses .env for PayPal/crypto;
 * Stripe card checkout is not wired in the storefront yet.
 */
class GatewaySettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-credit-card';

    protected static ?string $navigationGroup = 'Settings';

    protected static ?string $navigationLabel = 'Payment Gateways';

    protected static ?string $title = 'Payment Gateways';

    protected static ?string $slug = 'settings/gateways';

    protected static ?int $navigationSort = 2;

    protected static string $view = 'filament.pages.settings.gateway-settings';

    public static function canView(): bool
    {
        $user = auth()->user();
        if (! $user) {
            return false;
        }

        return (bool) $user->is_super_admin;
    }

    public ?array $data = [];

    public function mount(): void
    {
        $saved = Cache::get('wwa_gateway_settings', []);

        $this->form->fill([
            'paypal_enabled' => $saved['paypal_enabled'] ?? (bool) config('paypal.client_id'),
            'paypal_client_id' => $saved['paypal_client_id'] ?? (string) config('paypal.client_id', ''),
            'paypal_secret' => $saved['paypal_secret'] ?? (string) config('paypal.client_secret', ''),
            'paypal_mode' => $saved['paypal_mode'] ?? (string) config('paypal.mode', 'sandbox'),
            'stripe_enabled' => $saved['stripe_enabled'] ?? (bool) env('STRIPE_SECRET'),
            'stripe_publishable_key' => $saved['stripe_publishable_key'] ?? (string) env('STRIPE_KEY', env('STRIPE_PUBLISHABLE_KEY', '')),
            'stripe_secret_key' => $saved['stripe_secret_key'] ?? (string) env('STRIPE_SECRET', ''),
            'stripe_webhook_secret' => $saved['stripe_webhook_secret'] ?? (string) env('STRIPE_WEBHOOK_SECRET', ''),
            'bank_transfer_enabled' => $saved['bank_transfer_enabled'] ?? true,
            'bank_account_name' => $saved['bank_account_name'] ?? '',
            'bank_account_number' => $saved['bank_account_number'] ?? '',
            'bank_name' => $saved['bank_name'] ?? '',
            'bank_swift_code' => $saved['bank_swift_code'] ?? '',
            'crypto_enabled' => (bool) env('NOWPAYMENTS_API_KEY') || (bool) env('CRYPTO_PAYMENTS_ENABLED', true),
            'crypto_provider' => env('NOWPAYMENTS_API_KEY') ? 'NOWPayments' : 'Mock / env',
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Money-in overview')
                    ->description('Clive: track revenue from cards, bank cards, Stripe, PayPal, and crypto.')
                    ->schema([
                        Placeholder::make('status')
                            ->label('')
                            ->content(new HtmlString(
                                '<ul class="list-disc pl-5 text-sm text-gray-600 space-y-1">'
                                .'<li><strong>PayPal</strong> — live in checkout (orders + capture). Filament: Commerce / verified payments.</li>'
                                .'<li><strong>Crypto</strong> — live via NOWPayments (or mock). Filament: Crypto Payments.</li>'
                                .'<li><strong>Stripe / card</strong> — live in PaymentProcessor when STRIPE_SECRET + STRIPE_KEY are set (mock when empty).</li>'
                                .'<li><strong>Bank transfer</strong> — details for manual reconciliation.</li>'
                                .'</ul>'
                            )),
                    ]),

                Section::make('PayPal Settings')
                    ->schema([
                        Toggle::make('paypal_enabled')
                            ->label('Enable PayPal')
                            ->helperText('Checkout uses PAYPAL_* from .env; this toggle is admin preference.'),
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

                Section::make('Stripe / card Settings')
                    ->schema([
                        Toggle::make('stripe_enabled')
                            ->label('Enable Stripe')
                            ->helperText('Checkout uses STRIPE_* from .env. Mock when secret missing (STRIPE_MOCK=auto).'),
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

                Section::make('Crypto (NOWPayments)')
                    ->schema([
                        Toggle::make('crypto_enabled')
                            ->label('Crypto enabled (from env)')
                            ->disabled()
                            ->dehydrated(false),
                        TextInput::make('crypto_provider')
                            ->label('Provider')
                            ->disabled()
                            ->dehydrated(false),
                        Placeholder::make('crypto_hint')
                            ->label('')
                            ->content('Configure NOWPAYMENTS_API_KEY / CRYPTO_* in .env. Completed invoices appear under Commerce → Crypto Payments.'),
                    ])->columns(2),

                Section::make('Bank Transfer Settings')
                    ->schema([
                        Toggle::make('bank_transfer_enabled')
                            ->label('Enable Bank Transfer'),
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
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();
        unset($data['crypto_enabled'], $data['crypto_provider']);

        Cache::forever('wwa_gateway_settings', $data);

        Notification::make()
            ->title('Gateway settings saved')
            ->body('Admin preferences stored. Live PayPal / Stripe / crypto still follow .env on the server.')
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
