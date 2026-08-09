<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DonationResource\Pages;
use App\Models\Donation;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class DonationResource extends Resource
{
    protected static ?string $model = Donation::class;

    protected static ?string $navigationIcon = 'heroicon-o-heart';

    protected static ?string $navigationLabel = 'Donations';

    protected static ?string $navigationGroup = 'Marketplace';

    protected static ?int $navigationSort = 8;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Campaign')->schema([
                Forms\Components\TextInput::make('title')->required()->maxLength(255)
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn (string $operation, $state, Forms\Set $set) =>
                        $operation === 'create' ? $set('slug', Str::slug($state)) : null
                    ),
                Forms\Components\TextInput::make('slug')->required()->unique(ignoreRecord: true),
                Forms\Components\Select::make('category')->required()->options([
                    'medical' => 'Medical',
                    'education' => 'Education',
                    'disaster' => 'Disaster Relief',
                    'community' => 'Community',
                    'animals' => 'Animals',
                    'environment' => 'Environment',
                    'other' => 'Other',
                ]),
                Forms\Components\Textarea::make('description')->required()->rows(4)->columnSpanFull(),
                Forms\Components\Textarea::make('story')->rows(5)->columnSpanFull(),
            ])->columns(2),

            Forms\Components\Section::make('Fundraising')->schema([
                Forms\Components\TextInput::make('goal_amount')->numeric()->required()->prefix('$'),
                Forms\Components\TextInput::make('current_amount')->numeric()->default(0)->prefix('$'),
                Forms\Components\Select::make('currency')->options([
                    'USD' => 'USD', 'GBP' => 'GBP', 'EUR' => 'EUR',
                ])->default('USD')->required(),
                Forms\Components\DateTimePicker::make('deadline'),
                Forms\Components\TextInput::make('donor_count')->numeric()->default(0),
            ])->columns(3),

            Forms\Components\Section::make('Organizer')->schema([
                Forms\Components\TextInput::make('organizer_name')->required(),
                Forms\Components\TextInput::make('organizer_email')->email()->required(),
                Forms\Components\TextInput::make('organizer_phone'),
                Forms\Components\TextInput::make('country')->required(),
                Forms\Components\TextInput::make('city'),
                Forms\Components\Select::make('user_id')->relationship('user', 'email')->searchable(),
            ])->columns(3),

            Forms\Components\Section::make('Status')->schema([
                Forms\Components\Select::make('status')->options([
                    'active' => 'Active',
                    'pending' => 'Pending',
                    'completed' => 'Completed',
                    'closed' => 'Closed',
                ])->default('active')->required(),
                Forms\Components\Toggle::make('is_active')->default(true),
                Forms\Components\Toggle::make('is_featured'),
                Forms\Components\Toggle::make('is_urgent'),
                Forms\Components\Toggle::make('is_verified'),
                Forms\Components\FileUpload::make('cover_image')->image()->disk('public')->directory('donations'),
                Forms\Components\DateTimePicker::make('published_at')->default(now()),
            ])->columns(3),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('id')->sortable(),
            Tables\Columns\TextColumn::make('title')->searchable()->limit(40),
            Tables\Columns\TextColumn::make('category')->badge(),
            Tables\Columns\TextColumn::make('current_amount')->money('USD')->label('Raised'),
            Tables\Columns\TextColumn::make('goal_amount')->money('USD')->label('Goal'),
            Tables\Columns\TextColumn::make('donor_count')->label('Donors'),
            Tables\Columns\TextColumn::make('status')->badge(),
            Tables\Columns\IconColumn::make('is_featured')->boolean(),
            Tables\Columns\IconColumn::make('is_urgent')->boolean(),
        ])->filters([
            Tables\Filters\SelectFilter::make('status')->options([
                'active' => 'Active', 'pending' => 'Pending', 'completed' => 'Completed',
            ]),
            Tables\Filters\TernaryFilter::make('is_featured'),
        ])->actions([
            Tables\Actions\EditAction::make(),
            Tables\Actions\DeleteAction::make(),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDonations::route('/'),
            'create' => Pages\CreateDonation::route('/create'),
            'edit' => Pages\EditDonation::route('/{record}/edit'),
        ];
    }
}
