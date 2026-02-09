<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CandidateProfileResource\Pages;
use App\Models\CandidateProfile;
use App\Models\Customer;
use App\Models\Location;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class CandidateProfileResource extends Resource
{
    protected static ?string $model = CandidateProfile::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-circle';

    protected static ?string $navigationGroup = 'Candidate Management';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Profile Details')
                    ->columns(2)
                    ->schema([
                        Forms\Components\Select::make('customer_id')
                            ->label('Customer')
                            ->options(function ($get, $record) {
                                // Return recent customers for initial load
                                $options = Customer::select(
                                    DB::raw("CONCAT(first_name,' ',last_name,' | ',email) AS full_name"),
                                    'customer_id'
                                )
                                    ->orderBy('created_at', 'desc')
                                    ->limit(50)
                                    ->pluck('full_name', 'customer_id');
                                
                                // When editing, include the current customer even if not in recent list
                                if ($record && $record->customer_id && !$options->has($record->customer_id)) {
                                    $customer = Customer::find($record->customer_id);
                                    if ($customer) {
                                        $options->put($record->customer_id, $customer->first_name . ' ' . $customer->last_name . ' | ' . $customer->email);
                                    }
                                }
                                
                                return $options->all();
                            })
                            ->getSearchResultsUsing(function (string $search) {
                                if (empty($search)) {
                                    // Return recent customers when no search query
                                    return Customer::select(
                                        DB::raw("CONCAT(first_name,' ',last_name,' | ',email) AS full_name"),
                                        'customer_id'
                                    )
                                        ->orderBy('created_at', 'desc')
                                        ->limit(10)
                                        ->pluck('full_name', 'customer_id');
                                }
                                
                                // Search customers when query is provided
                                return Customer::select(
                                    DB::raw("CONCAT(first_name,' ',last_name,' | ',email) AS full_name"),
                                    'customer_id'
                                )
                                    ->where(function($q) use ($search) {
                                        $q->where('first_name', 'like', "%{$search}%")
                                          ->orWhere('last_name', 'like', "%{$search}%")
                                          ->orWhere('email', 'like', "%{$search}%");
                                    })
                                    ->limit(50)
                                    ->pluck('full_name', 'customer_id');
                            })
                            ->getOptionLabelUsing(function ($value) {
                                $customer = Customer::find($value);
                                if ($customer) {
                                    return $customer->first_name . ' ' . $customer->last_name . ' | ' . $customer->email;
                                }
                                return null;
                            })
                            ->required()
                            ->searchable()
                            ->preload(),
                        Forms\Components\Select::make('location_id')
                            ->label('Location')
                            ->options(function ($get, $record) {
                                // Return recent locations for initial load
                                $options = Location::with(['customer'])
                                    ->orderBy('created_at', 'desc')
                                    ->limit(20)
                                    ->get()
                                    ->mapWithKeys(function ($location) {
                                        $label = $location->city ?? 'Unknown';
                                        if ($location->zone_name) {
                                            $label .= ', ' . $location->zone_name;
                                        }
                                        if ($location->country_name) {
                                            $label .= ', ' . $location->country_name;
                                        }
                                        if ($location->customer) {
                                            $label .= ' (Customer: ' . $location->customer->first_name . ' ' . $location->customer->last_name . ')';
                                        }
                                        return [$location->location_id => $label];
                                    });
                                
                                // When editing, include the current location even if not in recent list
                                if ($record && $record->location_id && !$options->has($record->location_id)) {
                                    $location = Location::with(['customer'])->find($record->location_id);
                                    if ($location) {
                                        $label = $location->city ?? 'Unknown';
                                        if ($location->zone_name) {
                                            $label .= ', ' . $location->zone_name;
                                        }
                                        if ($location->country_name) {
                                            $label .= ', ' . $location->country_name;
                                        }
                                        if ($location->customer) {
                                            $label .= ' (Customer: ' . $location->customer->first_name . ' ' . $location->customer->last_name . ')';
                                        }
                                        $options->put($record->location_id, $label);
                                    }
                                }
                                
                                return $options->all();
                            })
                            ->getSearchResultsUsing(function (string $search) {
                                if (empty($search)) {
                                    // Return recent locations when no search query
                                    return Location::with(['customer'])
                                        ->orderBy('created_at', 'desc')
                                        ->limit(20)
                                        ->get()
                                        ->mapWithKeys(function ($location) {
                                            $label = $location->city ?? 'Unknown';
                                            if ($location->zone_name) {
                                                $label .= ', ' . $location->zone_name;
                                            }
                                            if ($location->country_name) {
                                                $label .= ', ' . $location->country_name;
                                            }
                                            if ($location->customer) {
                                                $label .= ' (Customer: ' . $location->customer->first_name . ' ' . $location->customer->last_name . ')';
                                            }
                                            return [$location->location_id => $label];
                                        });
                                }
                                
                                // Search locations when query is provided
                                return Location::with(['customer'])
                                    ->where(function($q) use ($search) {
                                        $q->where('city', 'like', "%{$search}%")
                                          ->orWhereHas('customer', function($query) use ($search) {
                                              $query->where('first_name', 'like', "%{$search}%")
                                                    ->orWhere('last_name', 'like', "%{$search}%")
                                                    ->orWhere('email', 'like', "%{$search}%");
                                          });
                                    })
                                    ->limit(50)
                                    ->get()
                                    ->mapWithKeys(function ($location) {
                                        $label = $location->city ?? 'Unknown';
                                        if ($location->zone_name) {
                                            $label .= ', ' . $location->zone_name;
                                        }
                                        if ($location->country_name) {
                                            $label .= ', ' . $location->country_name;
                                        }
                                        if ($location->customer) {
                                            $label .= ' (Customer: ' . $location->customer->first_name . ' ' . $location->customer->last_name . ')';
                                        }
                                        return [$location->location_id => $label];
                                    });
                            })
                            ->getOptionLabelUsing(function ($value) {
                                $location = Location::with(['customer'])->find($value);
                                if ($location) {
                                    $label = $location->city ?? 'Unknown';
                                    if ($location->zone_name) {
                                        $label .= ', ' . $location->zone_name;
                                    }
                                    if ($location->country_name) {
                                        $label .= ', ' . $location->country_name;
                                    }
                                    if ($location->customer) {
                                        $label .= ' (Customer: ' . $location->customer->first_name . ' ' . $location->customer->last_name . ')';
                                    }
                                    return $label;
                                }
                                return null;
                            })
                            ->searchable()
                            ->preload()
                            ->helperText('Locations come from customers\' addresses and listings. Format: City, State/Province, Country (Customer: Name)'),
                        Forms\Components\TextInput::make('headline')
                            ->maxLength(255),
                        Forms\Components\Textarea::make('summary')
                            ->rows(4)
                            ->columnSpanFull(),
                        Forms\Components\TagsInput::make('skills')
                            ->placeholder('Add skills')
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('cv_url')
                            ->label('CV URL')
                            ->url()
                            ->maxLength(255),
                        Forms\Components\Select::make('visibility')
                            ->options([
                                'public' => 'Public',
                                'private' => 'Private',
                            ])
                            ->default('public')
                            ->required(),
                    ]),
                Forms\Components\Section::make('Upsell Features')
                    ->columns(2)
                    ->schema([
                        Forms\Components\Toggle::make('is_featured')
                            ->label('Featured Profile')
                            ->default(false),
                        Forms\Components\DateTimePicker::make('featured_expires_at')
                            ->label('Featured Expires At'),
                        Forms\Components\Toggle::make('has_job_alerts_boost')
                            ->label('Job Alerts Boost')
                            ->default(false),
                        Forms\Components\DateTimePicker::make('job_alerts_boost_expires_at')
                            ->label('Job Alerts Boost Expires At'),
                    ])
                    ->collapsible(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('customer.name')
                    ->label('Customer')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('headline')
                    ->searchable()
                    ->limit(50),
                Tables\Columns\TextColumn::make('location.city')
                    ->label('Location')
                    ->sortable(),
                Tables\Columns\TextColumn::make('skills')
                    ->label('Skills')
                    ->badge()
                    ->separator(',')
                    ->limit(3),
                Tables\Columns\IconColumn::make('is_featured')
                    ->label('Featured')
                    ->boolean()
                    ->sortable(),
                Tables\Columns\IconColumn::make('has_job_alerts_boost')
                    ->label('Alerts Boost')
                    ->boolean()
                    ->sortable(),
                Tables\Columns\TextColumn::make('featured_expires_at')
                    ->label('Featured Expires')
                    ->dateTime()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('job_alerts_boost_expires_at')
                    ->label('Boost Expires')
                    ->dateTime()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\SelectColumn::make('visibility')
                    ->options([
                        'public' => 'Public',
                        'private' => 'Private',
                    ]),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('visibility')
                    ->options([
                        'public' => 'Public',
                        'private' => 'Private',
                    ]),
                Tables\Filters\TernaryFilter::make('is_featured')
                    ->label('Featured'),
                Tables\Filters\TernaryFilter::make('has_job_alerts_boost')
                    ->label('Job Alerts Boost'),
                Tables\Filters\SelectFilter::make('location_id')
                    ->label('Location')
                    ->relationship('location', 'city')
                    ->searchable()
                    ->preload(),
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
            ->defaultSort('created_at', 'desc');
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
            'index' => Pages\ListCandidateProfiles::route('/'),
            'create' => Pages\CreateCandidateProfile::route('/create'),
            'view' => Pages\ViewCandidateProfile::route('/{record}'),
            'edit' => Pages\EditCandidateProfile::route('/{record}/edit'),
        ];
    }
}

