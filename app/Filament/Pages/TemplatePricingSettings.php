<?php

namespace App\Filament\Pages;

use App\Models\TemplateSetting;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class TemplatePricingSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';

    protected static ?string $navigationGroup = 'Templates';

    protected static ?string $navigationLabel = 'Premium Pricing';

    protected static ?string $title = 'Template Premium Pricing';

    protected static ?string $slug = 'template-pricing';

    protected static ?int $navigationSort = 2;

    protected static string $view = 'filament.pages.template-pricing-settings';

    public static function canAccess(): bool
    {
        return true;
    }

    public static function canView(): bool
    {
        return true;
    }

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'premium_monthly_fee' => TemplateSetting::premiumMonthlyFee(),
            'premium_duration_days' => TemplateSetting::premiumDurationDays(),
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Premium listing fee')
                    ->description('Sellers pay this amount to feature a template as premium. Editable by super admin — not hard-coded.')
                    ->schema([
                        TextInput::make('premium_monthly_fee')
                            ->label('Monthly premium fee (USD)')
                            ->numeric()
                            ->prefix('$')
                            ->step(0.01)
                            ->required()
                            ->minValue(0)
                            ->helperText('Example starting price: $5.00 / month'),
                        TextInput::make('premium_duration_days')
                            ->label('Premium duration (days)')
                            ->numeric()
                            ->required()
                            ->minValue(1)
                            ->maxValue(365)
                            ->helperText('How long premium status lasts after payment (default 30 days ≈ 1 month)'),
                    ])->columns(2),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $state = $this->form->getState();

        TemplateSetting::setValue(
            'premium_monthly_fee',
            number_format((float) $state['premium_monthly_fee'], 2, '.', ''),
            'Premium listing fee (USD / month)'
        );

        TemplateSetting::setValue(
            'premium_duration_days',
            (string) (int) $state['premium_duration_days'],
            'Premium listing duration (days)'
        );

        Notification::make()
            ->title('Template pricing saved')
            ->success()
            ->send();
    }
}
