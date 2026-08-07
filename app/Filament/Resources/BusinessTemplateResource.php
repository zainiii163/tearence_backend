<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BusinessTemplateResource\Pages;
use App\Models\BusinessTemplate;
use App\Models\TemplateSetting;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class BusinessTemplateResource extends Resource
{
    protected static ?string $model = BusinessTemplate::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-duplicate';

    protected static ?string $navigationGroup = 'Templates';

    protected static ?string $navigationLabel = 'All Templates';

    protected static ?string $modelLabel = 'Template';

    protected static ?string $pluralModelLabel = 'Business Templates';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Listing')
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('slug')
                            ->maxLength(255)
                            ->helperText('Leave blank on create — auto-generated from title'),
                        Forms\Components\Textarea::make('blurb')
                            ->rows(2)
                            ->maxLength(500),
                        Forms\Components\Textarea::make('description')
                            ->rows(4)
                            ->columnSpanFull(),
                        Forms\Components\Select::make('vertical')
                            ->options([
                                'business' => 'Business',
                                'services' => 'Services',
                                'buy-sell' => 'Buy & Sell',
                                'vehicles' => 'Vehicles',
                                'books' => 'Books',
                                'property' => 'Property',
                                'businesses-for-sale' => 'Businesses for Sale',
                                'jobs' => 'Jobs',
                            ])
                            ->required()
                            ->searchable(),
                        Forms\Components\TextInput::make('category_slug')
                            ->default('default')
                            ->maxLength(100),
                        Forms\Components\Select::make('template_type')
                            ->options([
                                'pitch_deck' => 'Pitch deck',
                                'grant' => 'Grant',
                                'business_plan' => 'Business plan',
                                'proposal' => 'Proposal',
                                'business_doc' => 'Other document',
                                'agreement' => 'Agreement',
                                'resume' => 'Resume',
                                'cv' => 'CV',
                                'letter' => 'Letter',
                                'hiring' => 'Hiring pack',
                            ])
                            ->searchable(),
                        Forms\Components\Select::make('status')
                            ->options([
                                'draft' => 'Draft',
                                'active' => 'Active',
                                'paused' => 'Paused',
                                'sold' => 'Sold',
                            ])
                            ->required()
                            ->default('active'),
                    ])->columns(2),

                Forms\Components\Section::make('Pricing')
                    ->schema([
                        Forms\Components\TextInput::make('price')
                            ->numeric()
                            ->prefix('$')
                            ->step(0.01)
                            ->required()
                            ->default(0),
                        Forms\Components\TextInput::make('price_label')
                            ->maxLength(50)
                            ->placeholder('From $29'),
                        Forms\Components\TextInput::make('currency')
                            ->default('USD')
                            ->maxLength(3),
                        Forms\Components\TextInput::make('sort_order')
                            ->numeric()
                            ->default(0),
                    ])->columns(2),

                Forms\Components\Section::make('Premium listing')
                    ->description('Premium fee is configured under Template Pricing (default $'
                        .number_format(TemplateSetting::premiumMonthlyFee(), 2).'/month).')
                    ->schema([
                        Forms\Components\Toggle::make('is_premium')
                            ->label('Premium')
                            ->default(false),
                        Forms\Components\DateTimePicker::make('premium_until')
                            ->label('Premium until'),
                        Forms\Components\TextInput::make('premium_fee_paid')
                            ->label('Fee paid ($)')
                            ->numeric()
                            ->prefix('$')
                            ->step(0.01),
                    ])->columns(3),

                Forms\Components\Section::make('Files & flags')
                    ->schema([
                        Forms\Components\TextInput::make('file_url')
                            ->url()
                            ->maxLength(500),
                        Forms\Components\TextInput::make('preview_image')
                            ->url()
                            ->maxLength(500),
                        Forms\Components\Toggle::make('is_catalog')
                            ->label('Platform catalog pack')
                            ->default(false),
                        Forms\Components\TextInput::make('headline')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('section_description')
                            ->maxLength(500),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->sortable()
                    ->limit(40),
                Tables\Columns\BadgeColumn::make('vertical')
                    ->sortable(),
                Tables\Columns\TextColumn::make('template_type')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('price')
                    ->money('USD')
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_premium')
                    ->label('Premium')
                    ->boolean(),
                Tables\Columns\TextColumn::make('premium_until')
                    ->dateTime()
                    ->toggleable(),
                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'success' => 'active',
                        'warning' => 'paused',
                        'secondary' => 'draft',
                        'danger' => 'sold',
                    ]),
                Tables\Columns\IconColumn::make('is_catalog')
                    ->label('Catalog')
                    ->boolean()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('views')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('vertical')
                    ->options([
                        'business' => 'Business',
                        'services' => 'Services',
                        'buy-sell' => 'Buy & Sell',
                        'vehicles' => 'Vehicles',
                        'books' => 'Books',
                        'property' => 'Property',
                        'businesses-for-sale' => 'Businesses for Sale',
                        'jobs' => 'Jobs',
                    ]),
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'draft' => 'Draft',
                        'active' => 'Active',
                        'paused' => 'Paused',
                        'sold' => 'Sold',
                    ]),
                Tables\Filters\TernaryFilter::make('is_premium')
                    ->label('Premium'),
                Tables\Filters\TernaryFilter::make('is_catalog')
                    ->label('Catalog'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('toggle_premium')
                    ->label(fn (BusinessTemplate $record) => $record->is_premium_active ? 'Remove premium' : 'Make premium')
                    ->icon('heroicon-o-star')
                    ->color(fn (BusinessTemplate $record) => $record->is_premium_active ? 'secondary' : 'warning')
                    ->action(function (BusinessTemplate $record) {
                        if ($record->is_premium_active) {
                            $record->clearPremium();
                        } else {
                            $record->applyPremium();
                        }
                    }),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('activate')
                        ->label('Activate')
                        ->icon('heroicon-o-check')
                        ->action(fn ($records) => $records->each->update(['status' => 'active'])),
                    Tables\Actions\BulkAction::make('pause')
                        ->label('Pause')
                        ->icon('heroicon-o-pause')
                        ->action(fn ($records) => $records->each->update(['status' => 'paused'])),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with('user');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBusinessTemplates::route('/'),
            'create' => Pages\CreateBusinessTemplate::route('/create'),
            'edit' => Pages\EditBusinessTemplate::route('/{record}/edit'),
        ];
    }
}
