<?php

namespace App\Filament\Resources;

use App\Filament\Resources\HrEmployeeResource\Pages;
use App\Models\HrEmployee;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class HrEmployeeResource extends Resource
{
    protected static ?string $model = HrEmployee::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $navigationLabel = 'Employees';

    protected static ?string $modelLabel = 'Employee';

    protected static ?string $pluralModelLabel = 'Employees';

    protected static ?string $navigationGroup = 'HR & Payroll';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Identity')->schema([
                Forms\Components\TextInput::make('employee_code')->label('Employee code')->maxLength(32),
                Forms\Components\TextInput::make('first_name')->required()->maxLength(120),
                Forms\Components\TextInput::make('last_name')->required()->maxLength(120),
                Forms\Components\Select::make('status')->options([
                    'active' => 'Active',
                    'on_leave' => 'On leave',
                    'terminated' => 'Terminated',
                ])->default('active')->required(),
                Forms\Components\DatePicker::make('start_date'),
            ])->columns(3),

            Forms\Components\Section::make('Contact')->schema([
                Forms\Components\TextInput::make('email')->email()->maxLength(255),
                Forms\Components\TextInput::make('phone')->tel()->maxLength(64),
                Forms\Components\Textarea::make('address')->rows(2)->columnSpanFull(),
                Forms\Components\TextInput::make('city')->maxLength(120),
                Forms\Components\TextInput::make('country')->maxLength(100),
                Forms\Components\TextInput::make('postal_code')->maxLength(32),
            ])->columns(3),

            Forms\Components\Section::make('Role & hours')->schema([
                Forms\Components\TextInput::make('job_position')->label('Job position')->maxLength(160),
                Forms\Components\TextInput::make('work_location')->label('Work location')->maxLength(160),
                Forms\Components\TextInput::make('weekly_hours')->numeric()->step(0.25)->suffix('hrs/week'),
                Forms\Components\Textarea::make('notes')->rows(3)->columnSpanFull(),
            ])->columns(3),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('last_name')
            ->columns([
                Tables\Columns\TextColumn::make('employee_code')->label('Code')->searchable()->toggleable(),
                Tables\Columns\TextColumn::make('first_name')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('last_name')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('email')->searchable()->toggleable(),
                Tables\Columns\TextColumn::make('phone')->toggleable(),
                Tables\Columns\TextColumn::make('job_position')->label('Position')->searchable(),
                Tables\Columns\TextColumn::make('work_location')->label('Location')->toggleable(),
                Tables\Columns\TextColumn::make('weekly_hours')->label('Hours/wk')->toggleable(),
                Tables\Columns\TextColumn::make('status')->badge()->color(fn (?string $state): string => match ($state) {
                    'active' => 'success',
                    'on_leave' => 'warning',
                    default => 'danger',
                }),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')->options([
                    'active' => 'Active',
                    'on_leave' => 'On leave',
                    'terminated' => 'Terminated',
                ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListHrEmployees::route('/'),
            'create' => Pages\CreateHrEmployee::route('/create'),
            'edit' => Pages\EditHrEmployee::route('/{record}/edit'),
        ];
    }
}
