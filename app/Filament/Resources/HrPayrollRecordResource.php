<?php

namespace App\Filament\Resources;

use App\Filament\Resources\HrPayrollRecordResource\Pages;
use App\Models\HrPayrollRecord;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class HrPayrollRecordResource extends Resource
{
    protected static ?string $model = HrPayrollRecord::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';

    protected static ?string $navigationLabel = 'Payroll';

    protected static ?string $modelLabel = 'Payroll record';

    protected static ?string $pluralModelLabel = 'Payroll';

    protected static ?string $navigationGroup = 'HR & Payroll';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Payroll')->schema([
                Forms\Components\Select::make('hr_employee_id')
                    ->label('Employee')
                    ->relationship('employee', 'first_name')
                    ->getOptionLabelFromRecordUsing(fn ($record) => $record->full_name)
                    ->searchable()
                    ->preload()
                    ->required()
                    ->reactive()
                    ->afterStateUpdated(function ($state, Forms\Set $set) {
                        $emp = \App\Models\HrEmployee::find($state);
                        if (! $emp) {
                            return;
                        }
                        $set('job_position', $emp->job_position);
                        $set('work_location', $emp->work_location);
                        if ($emp->weekly_hours) {
                            $set('hours_worked', round((float) $emp->weekly_hours * 4, 2));
                        }
                    }),
                Forms\Components\TextInput::make('pay_period')
                    ->label('Pay period')
                    ->placeholder('e.g. 2026-08')
                    ->required()
                    ->maxLength(32),
                Forms\Components\DatePicker::make('period_start'),
                Forms\Components\DatePicker::make('period_end'),
                Forms\Components\TextInput::make('job_position')->label('Job position')->maxLength(160),
                Forms\Components\TextInput::make('work_location')->label('Work location')->maxLength(160),
                Forms\Components\TextInput::make('hours_worked')->numeric()->step(0.25)->suffix('hrs'),
                Forms\Components\TextInput::make('hourly_rate')->numeric()->prefix('$')->step(0.01),
                Forms\Components\TextInput::make('salary_amount')->label('Salary / pay')->numeric()->prefix('$')->required()->step(0.01),
                Forms\Components\Select::make('currency')->options([
                    'USD' => 'USD',
                    'GBP' => 'GBP',
                    'EUR' => 'EUR',
                ])->default('USD')->required(),
                Forms\Components\Select::make('payment_status')->options([
                    'draft' => 'Draft',
                    'approved' => 'Approved',
                    'paid' => 'Paid',
                ])->default('draft')->required(),
                Forms\Components\DatePicker::make('paid_on'),
                Forms\Components\Textarea::make('notes')->rows(3)->columnSpanFull(),
            ])->columns(3),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('pay_period', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('employee.full_name')
                    ->label('Employee')
                    ->getStateUsing(fn (HrPayrollRecord $record) => $record->employee?->full_name)
                    ->searchable(query: function ($query, string $search) {
                        $query->whereHas('employee', function ($q) use ($search) {
                            $q->where('first_name', 'like', "%{$search}%")
                                ->orWhere('last_name', 'like', "%{$search}%");
                        });
                    }),
                Tables\Columns\TextColumn::make('pay_period')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('job_position')->toggleable(),
                Tables\Columns\TextColumn::make('work_location')->toggleable(),
                Tables\Columns\TextColumn::make('hours_worked')->label('Hours'),
                Tables\Columns\TextColumn::make('salary_amount')->money(fn (HrPayrollRecord $r) => $r->currency ?: 'USD')->label('Pay'),
                Tables\Columns\TextColumn::make('payment_status')->badge()->color(fn (?string $state): string => match ($state) {
                    'paid' => 'success',
                    'approved' => 'info',
                    default => 'gray',
                }),
                Tables\Columns\TextColumn::make('paid_on')->date()->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('payment_status')->options([
                    'draft' => 'Draft',
                    'approved' => 'Approved',
                    'paid' => 'Paid',
                ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListHrPayrollRecords::route('/'),
            'create' => Pages\CreateHrPayrollRecord::route('/create'),
            'edit' => Pages\EditHrPayrollRecord::route('/{record}/edit'),
        ];
    }
}
