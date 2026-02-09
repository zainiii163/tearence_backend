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
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;

class InvoiceSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationGroup = 'Settings';

    protected static ?string $title = 'Invoice Settings';

    protected static ?string $slug = 'settings/invoice';

    protected static ?int $navigationSort = 3;

    protected static string $view = 'filament.pages.settings.invoice-settings';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'company_name' => 'World Wide Adverts',
            'company_address' => 'Your Company Address',
            'company_phone' => '+1234567890',
            'company_email' => 'billing@worldwideadverts.info',
            'company_website' => 'https://worldwideadverts.info',
            'tax_number' => '',
            'invoice_prefix' => 'INV-',
            'invoice_start_number' => '1000',
            'invoice_logo' => null,
            'invoice_footer' => 'Thank you for your business!',
            'auto_generate_invoice' => true,
            'send_invoice_email' => true,
            'invoice_due_days' => '30',
            'currency_symbol' => '$',
            'currency_code' => 'USD',
            'tax_rate' => '0.00',
            'invoice_color' => '#3B82F6',
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Company Information')
                    ->schema([
                        TextInput::make('company_name')
                            ->label('Company Name')
                            ->required()
                            ->maxLength(255),
                        Textarea::make('company_address')
                            ->label('Company Address')
                            ->maxLength(500),
                        TextInput::make('company_phone')
                            ->label('Company Phone')
                            ->tel()
                            ->maxLength(255),
                        TextInput::make('company_email')
                            ->label('Company Email')
                            ->email()
                            ->required(),
                        TextInput::make('company_website')
                            ->label('Company Website')
                            ->url()
                            ->maxLength(255),
                        TextInput::make('tax_number')
                            ->label('Tax Number/VAT')
                            ->maxLength(255),
                    ])->columns(2),

                Section::make('Invoice Configuration')
                    ->schema([
                        TextInput::make('invoice_prefix')
                            ->label('Invoice Prefix')
                            ->helperText('Prefix for invoice numbers (e.g., INV-)')
                            ->maxLength(10),
                        TextInput::make('invoice_start_number')
                            ->label('Starting Invoice Number')
                            ->numeric()
                            ->helperText('First invoice number to use')
                            ->required(),
                        FileUpload::make('invoice_logo')
                            ->label('Invoice Logo')
                            ->image()
                            ->directory('invoices')
                            ->maxSize(1024),
                        Textarea::make('invoice_footer')
                            ->label('Invoice Footer Text')
                            ->maxLength(500),
                    ])->columns(2),

                Section::make('Invoice Behavior')
                    ->schema([
                        Toggle::make('auto_generate_invoice')
                            ->label('Auto Generate Invoice')
                            ->helperText('Automatically generate invoice when payment is received'),
                        Toggle::make('send_invoice_email')
                            ->label('Send Invoice Email')
                            ->helperText('Automatically send invoice email to customer'),
                        TextInput::make('invoice_due_days')
                            ->label('Invoice Due Days')
                            ->numeric()
                            ->helperText('Number of days until invoice is due')
                            ->required(),
                    ])->columns(3),

                Section::make('Currency & Tax')
                    ->schema([
                        TextInput::make('currency_symbol')
                            ->label('Currency Symbol')
                            ->maxLength(10)
                            ->required(),
                        Select::make('currency_code')
                            ->label('Currency Code')
                            ->options([
                                'USD' => 'US Dollar',
                                'EUR' => 'Euro',
                                'GBP' => 'British Pound',
                                'CAD' => 'Canadian Dollar',
                                'AUD' => 'Australian Dollar',
                            ])
                            ->required(),
                        TextInput::make('tax_rate')
                            ->label('Tax Rate (%)')
                            ->numeric()
                            ->step(0.01)
                            ->helperText('Default tax rate for invoices'),
                        ColorPicker::make('invoice_color')
                            ->label('Invoice Theme Color')
                            ->helperText('Primary color for invoice design'),
                    ])->columns(2),
            ]);
    }

    public function save(): void
    {
        $data = $this->form->getState();

        // Here you would typically save to a settings table or config file
        Notification::make()
            ->title('Invoice settings saved successfully')
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
            \Filament\Actions\Action::make('preview')
                ->label('Preview Invoice')
                ->icon('heroicon-o-eye')
                ->color('secondary')
                ->action(function () {
                    // Here you would generate a preview invoice
                    Notification::make()
                        ->title('Invoice preview generated')
                        ->info()
                        ->send();
                }),
        ];
    }
} 