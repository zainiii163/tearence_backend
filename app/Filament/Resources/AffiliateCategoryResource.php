<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AffiliateCategoryResource\Pages;
use App\Models\AffiliateCategory;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class AffiliateCategoryResource extends Resource
{
    protected static ?string $model = AffiliateCategory::class;

    protected static ?string $navigationIcon = 'heroicon-o-tag';

    protected static ?string $navigationGroup = 'Affiliates Hub';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255)
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn (string $operation, $state, Forms\Set $set) => $operation === 'create' ? $set('slug', Str::slug($state)) : null),

                Forms\Components\TextInput::make('slug')
                    ->required()
                    ->maxLength(255)
                    ->unique(AffiliateCategory::class, 'slug', ignoreRecord: true),

                Forms\Components\Textarea::make('description')
                    ->maxLength(65535)
                    ->columnSpanFull(),

                Forms\Components\TextInput::make('icon')
                    ->maxLength(255)
                    ->placeholder('heroicon-o-tag'),

                Forms\Components\Toggle::make('is_active')
                    ->default(true),

                Forms\Components\TextInput::make('sort_order')
                    ->numeric()
                    ->default(0),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('slug')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('active_business_offers')
                    ->label('Business Offers')
                    ->getStateUsing(fn (AffiliateCategory $record): int => $record->businessAffiliateOffers()->active()->count())
                    ->sortable(),

                Tables\Columns\TextColumn::make('active_user_posts')
                    ->label('User Posts')
                    ->getStateUsing(fn (AffiliateCategory $record): int => $record->userAffiliatePosts()->active()->count())
                    ->sortable(),

                Tables\Columns\TextColumn::make('total_posts')
                    ->getStateUsing(fn (AffiliateCategory $record): int => $record->getTotalPostsAttribute())
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_active')
                    ->boolean()
                    ->sortable(),

                Tables\Columns\TextColumn::make('sort_order')
                    ->numeric()
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('is_active')
                    ->options([
                        true => 'Active',
                        false => 'Inactive',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->reorderable('sort_order')
            ->defaultSort('sort_order');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAffiliateCategories::route('/'),
            'create' => Pages\CreateAffiliateCategory::route('/create'),
            'view' => Pages\ViewAffiliateCategory::route('/{record}'),
            'edit' => Pages\EditAffiliateCategory::route('/{record}/edit'),
        ];
    }
}
