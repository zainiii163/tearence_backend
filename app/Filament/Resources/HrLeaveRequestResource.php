<?php

namespace App\Filament\Resources;

use App\Filament\Resources\HrLeaveRequestResource\Pages;
use App\Models\HrLeaveRequest;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class HrLeaveRequestResource extends Resource
{
    protected static ?string $model = HrLeaveRequest::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';

    protected static ?string $navigationLabel = 'Leave requests';

    protected static ?string $modelLabel = 'Leave request';

    protected static ?string $pluralModelLabel = 'Leave requests';

    protected static ?string $navigationGroup = 'HR & Payroll';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Request')->schema([
                Forms\Components\Select::make('hr_employee_id')
                    ->label('Employee')
                    ->relationship('employee', 'first_name')
                    ->getOptionLabelFromRecordUsing(fn ($record) => $record->full_name)
                    ->searchable()
                    ->preload()
                    ->required(),
                Forms\Components\Select::make('leave_type')->options([
                    'holiday' => 'Holiday',
                    'sick' => 'Sick leave',
                    'unpaid' => 'Unpaid',
                    'other' => 'Other',
                ])->required(),
                Forms\Components\DatePicker::make('start_date')->required(),
                Forms\Components\DatePicker::make('end_date')->required(),
                Forms\Components\TextInput::make('days')->numeric()->minValue(0),
                Forms\Components\Select::make('status')->options([
                    'pending' => 'Pending',
                    'approved' => 'Approved',
                    'rejected' => 'Rejected',
                    'cancelled' => 'Cancelled',
                ])->default('pending')->required(),
                Forms\Components\Textarea::make('reason')->rows(3)->columnSpanFull(),
                Forms\Components\Textarea::make('admin_notes')->rows(2)->columnSpanFull(),
            ])->columns(3),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('start_date', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('employee.full_name')
                    ->label('Employee')
                    ->getStateUsing(fn (HrLeaveRequest $record) => $record->employee?->full_name)
                    ->searchable(query: function ($query, string $search) {
                        $query->whereHas('employee', function ($q) use ($search) {
                            $q->where('first_name', 'like', "%{$search}%")
                                ->orWhere('last_name', 'like', "%{$search}%");
                        });
                    }),
                Tables\Columns\TextColumn::make('leave_type')->badge()->formatStateUsing(fn (?string $state) => match ($state) {
                    'holiday' => 'Holiday',
                    'sick' => 'Sick leave',
                    'unpaid' => 'Unpaid',
                    default => ucfirst($state ?? ''),
                })->color(fn (?string $state): string => match ($state) {
                    'holiday' => 'info',
                    'sick' => 'warning',
                    default => 'gray',
                }),
                Tables\Columns\TextColumn::make('start_date')->date()->sortable(),
                Tables\Columns\TextColumn::make('end_date')->date()->sortable(),
                Tables\Columns\TextColumn::make('days'),
                Tables\Columns\TextColumn::make('status')->badge()->color(fn (?string $state): string => match ($state) {
                    'approved' => 'success',
                    'pending' => 'warning',
                    'rejected' => 'danger',
                    default => 'gray',
                }),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('leave_type')->options([
                    'holiday' => 'Holiday',
                    'sick' => 'Sick leave',
                    'unpaid' => 'Unpaid',
                    'other' => 'Other',
                ]),
                Tables\Filters\SelectFilter::make('status')->options([
                    'pending' => 'Pending',
                    'approved' => 'Approved',
                    'rejected' => 'Rejected',
                    'cancelled' => 'Cancelled',
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
            'index' => Pages\ListHrLeaveRequests::route('/'),
            'create' => Pages\CreateHrLeaveRequest::route('/create'),
            'edit' => Pages\EditHrLeaveRequest::route('/{record}/edit'),
        ];
    }
}
