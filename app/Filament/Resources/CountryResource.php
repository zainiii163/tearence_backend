<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CountryResource\Pages;
use App\Models\Country;
use App\Support\WorldCountries;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class CountryResource extends Resource
{
    protected static ?string $navigationLabel = 'Countries';

    protected static ?string $model = Country::class;

    protected static ?string $navigationIcon = 'heroicon-o-flag';

    protected static ?string $navigationGroup = 'Settings';

    protected static ?int $navigationSort = 22;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Country')
                    ->description('Worldwide ISO country used across Filament forms and the public site.')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('code')
                            ->required()
                            ->maxLength(3)
                            ->dehydrateStateUsing(fn ($state) => strtoupper($state ?? ''))
                            ->helperText('ISO 3166-1 alpha-3 (e.g. USA, GBR)'),
                        Forms\Components\TextInput::make('iso_code')
                            ->required()
                            ->maxLength(2)
                            ->dehydrateStateUsing(fn ($state) => strtoupper($state ?? ''))
                            ->live(onBlur: true)
                            ->afterStateUpdated(function ($state, Forms\Set $set) {
                                $iso = strtolower((string) $state);
                                if (strlen($iso) === 2) {
                                    $set('flag', $iso);
                                }
                            })
                            ->helperText('ISO 3166-1 alpha-2 — drives the flag (e.g. US, GB)'),
                        Forms\Components\TextInput::make('flag')
                            ->maxLength(8)
                            ->helperText('Usually the lowercase ISO-2 code (used for flagcdn.com images)'),
                        Forms\Components\Placeholder::make('flag_preview')
                            ->label('Flag preview')
                            ->content(function (Forms\Get $get) {
                                $iso = strtoupper((string) ($get('iso_code') ?: $get('flag') ?: ''));
                                if (strlen($iso) !== 2) {
                                    return 'Enter a 2-letter ISO code to preview the flag.';
                                }
                                $emoji = WorldCountries::flagEmoji($iso);
                                $url = WorldCountries::flagUrl($iso, 80);

                                return new \Illuminate\Support\HtmlString(
                                    '<div style="display:flex;align-items:center;gap:12px;">'
                                    . '<span style="font-size:2rem;line-height:1;">' . e($emoji) . '</span>'
                                    . ($url ? '<img src="' . e($url) . '" alt="' . e($iso) . ' flag" width="80" height="60" style="border-radius:4px;border:1px solid #e5e7eb;" />' : '')
                                    . '<span style="color:#64748b;">' . e($iso) . '</span>'
                                    . '</div>'
                                );
                            }),
                        Forms\Components\Toggle::make('is_active')
                            ->default(true)
                            ->required(),
                        Forms\Components\TextInput::make('sort_order')
                            ->numeric()
                            ->default(0),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('name')
            ->columns([
                Tables\Columns\ImageColumn::make('flag_url')
                    ->label('Flag')
                    ->circular()
                    ->size(28)
                    ->defaultImageUrl(fn () => 'https://flagcdn.com/w40/un.png'),
                Tables\Columns\TextColumn::make('flag_emoji')
                    ->label('')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->description(fn (Country $record) => ($record->flag_emoji ?? '') . ' ' . ($record->iso_code ?? '')),
                Tables\Columns\TextColumn::make('code')
                    ->searchable()
                    ->sortable()
                    ->badge(),
                Tables\Columns\TextColumn::make('iso_code')
                    ->label('ISO')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean(),
                Tables\Columns\TextColumn::make('sort_order')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active'),
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

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCountries::route('/'),
            'create' => Pages\CreateCountry::route('/create'),
            'edit' => Pages\EditCountry::route('/{record}/edit'),
        ];
    }
}
