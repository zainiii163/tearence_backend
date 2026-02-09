<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ListingResource\Pages;
use App\Filament\Resources\ListingResource\RelationManagers;
use App\Models\Category;
use App\Models\Country;
use App\Models\Currency;
use App\Models\Customer;
use App\Models\Listing;
use App\Models\ListingImage;
use App\Models\Location;
use App\Models\Package;
use App\Models\Zone;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ListingResource extends Resource
{
    protected static ?string $model = Listing::class;

    protected static ?string $navigationIcon = 'heroicon-o-identification';

    protected static ?string $navigationGroup = 'Ads Management';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Ads Details')
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
                            ->preload()
                            ->reactive()
                            ->afterStateUpdated(function (callable $set, $state) {
                                $set('location_id', null);
                            }),
                        Forms\Components\Select::make('location_id')
                            ->label('Location')
                            ->options(function ($get, $record) {
                                // Fetch locations based on the selected customer
                                $customerId = $get('customer_id');
                                
                                // When editing, also get customer_id from the record
                                if (!$customerId && $record) {
                                    $customerId = $record->customer_id;
                                }
                                
                                $options = collect();
                                
                                if ($customerId) {
                                    $locations = Location::where('customer_id', $customerId)
                                        ->get()
                                        ->mapWithKeys(function ($location) {
                                            $label = $location->city;
                                            if ($location->zone_name) {
                                                $label .= ', ' . $location->zone_name;
                                            }
                                            if ($location->country_name) {
                                                $label .= ', ' . $location->country_name;
                                            }
                                            return [$location->location_id => $label];
                                        });
                                    $options = $options->merge($locations);
                                }
                                
                                // When editing, include the current location even if customer doesn't match
                                if ($record && $record->location_id) {
                                    $currentLocation = Location::find($record->location_id);
                                    if ($currentLocation && !$options->has($record->location_id)) {
                                        $label = $currentLocation->city;
                                        if ($currentLocation->zone_name) {
                                            $label .= ', ' . $currentLocation->zone_name;
                                        }
                                        if ($currentLocation->country_name) {
                                            $label .= ', ' . $currentLocation->country_name;
                                        }
                                        $options->put($record->location_id, $label);
                                    }
                                }
                                
                                return $options->all();
                            })
                            ->getOptionLabelUsing(function ($value) {
                                $location = Location::find($value);
                                if ($location) {
                                    $label = $location->city;
                                    if ($location->zone_name) {
                                        $label .= ', ' . $location->zone_name;
                                    }
                                    if ($location->country_name) {
                                        $label .= ', ' . $location->country_name;
                                    }
                                    return $label;
                                }
                                return null;
                            })
                            ->searchable()
                            ->createOptionForm([
                                Forms\Components\Select::make('country_id')
                                    ->label('Country')
                                    ->options(Country::all()->pluck('name', 'country_id'))
                                    ->searchable()
                                    ->required()
                                    ->reactive()
                                    ->afterStateUpdated(fn (callable $set) => $set('zone_id', null)),
                                Forms\Components\Select::make('zone_id')
                                    ->label('State/Province')
                                    ->options(function ($get) {
                                        $countryId = $get('country_id');
                                        if ($countryId) {
                                            return Zone::where('country_id', $countryId)
                                                ->pluck('name', 'zone_id');
                                        }
                                        return [];
                                    })
                                    ->searchable()
                                    ->reactive(),
                                Forms\Components\TextInput::make('city')
                                    ->label('City')
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('zip')
                                    ->label('Zip/Postal Code')
                                    ->maxLength(20),
                                Forms\Components\TextInput::make('latitude')
                                    ->label('Latitude')
                                    ->numeric(),
                                Forms\Components\TextInput::make('longitude')
                                    ->label('Longitude')
                                    ->numeric(),
                            ])
                            ->createOptionUsing(function (array $data, $get) {
                                $data['customer_id'] = $get('customer_id');
                                return Location::create($data)->location_id;
                            })
                            ->reactive()
                            ->required(),
                        Forms\Components\Select::make('parent_category_id')
                            ->label('Parent Category')
                            ->options(Category::whereNull('parent_id')->orderBy('name', 'ASC')->pluck('name', 'category_id'))
                            ->getOptionLabelUsing(function ($value) {
                                $category = Category::find($value);
                                return $category ? $category->name : null;
                            })
                            ->default('')
                            ->reactive()  // Make it reactive to trigger the update for child categories
                            ->required()
                            ->dehydrated(false)
                            ->searchable(),
                        Forms\Components\Select::make('category_id')
                            ->label('Category')
                            ->options(function ($get, $record) {
                                // Fetch child categories based on the selected parent category
                                $parentId = $get('parent_category_id');
                                
                                // When editing, also get parent_id from the record's category
                                if (!$parentId && $record && $record->category_id) {
                                    $category = Category::find($record->category_id);
                                    if ($category && $category->parent_id) {
                                        $parentId = $category->parent_id;
                                    }
                                }
                                
                                $options = collect();
                                
                                if ($parentId) {
                                    $categories = Category::where('parent_id', $parentId)->pluck('name', 'category_id');
                                    $options = $options->merge($categories);
                                }
                                
                                // When editing, include the current category even if parent doesn't match
                                if ($record && $record->category_id) {
                                    $currentCategory = Category::find($record->category_id);
                                    if ($currentCategory && !$options->has($record->category_id)) {
                                        $options->put($record->category_id, $currentCategory->name);
                                    }
                                }
                                
                                return $options->all();
                            })
                            ->getOptionLabelUsing(function ($value) {
                                $category = Category::find($value);
                                return $category ? $category->name : null;
                            })
                            ->default('')
                            ->required()
                            ->searchable()
                            ->disabled(fn($get) => empty($get('parent_category_id')))  // Disable if no parent category is selected
                            ->reactive(),  // Ensure it's reactive to the parent category selection
                        Forms\Components\Select::make('currency_id')
                            ->label('Currency')
                            ->options(Currency::all()->pluck('name', 'currency_id'))
                            ->getOptionLabelUsing(function ($value) {
                                $currency = Currency::find($value);
                                return $currency ? $currency->name : null;
                            })
                            ->default('')
                            ->required()
                            ->searchable(),
                        Forms\Components\TextInput::make('price')
                            ->required()
                            ->numeric()
                            ->default(0),
                        Forms\Components\Select::make('package_id')
                            ->label('Package')
                            ->options(Package::all()->pluck('title', 'package_id'))
                            ->getOptionLabelUsing(function ($value) {
                                $package = Package::find($value);
                                return $package ? $package->title : null;
                            })
                            ->default('')
                            ->required()
                            ->columnSpanFull()
                            ->searchable(),
                        Forms\Components\TextInput::make('title')
                            ->required()
                            ->columnSpanFull()
                            ->maxLength(100),
                        Forms\Components\RichEditor::make('description')
                            ->required()
                            ->columnSpanFull(),
                    ]),
                Forms\Components\Section::make('Job Details')
                    ->columns(2)
                    ->schema([
                        Forms\Components\Select::make('job_type')
                            ->label('Job Type')
                            ->options([
                                'full-time' => 'Full Time',
                                'part-time' => 'Part Time',
                                'contract' => 'Contract',
                                'freelance' => 'Freelance',
                                'internship' => 'Internship',
                            ])
                            ->searchable(),
                        Forms\Components\TextInput::make('salary_min')
                            ->label('Salary Min')
                            ->numeric()
                            ->prefix('$'),
                        Forms\Components\TextInput::make('salary_max')
                            ->label('Salary Max')
                            ->numeric()
                            ->prefix('$'),
                        Forms\Components\TextInput::make('apply_url')
                            ->label('Apply URL')
                            ->url()
                            ->maxLength(255),
                        Forms\Components\DatePicker::make('end_date')
                            ->label('End Date'),
                        Forms\Components\Toggle::make('is_featured')
                            ->label('Featured Job')
                            ->default(false),
                        Forms\Components\Toggle::make('is_suggested')
                            ->label('Suggested Job')
                            ->default(false),
                    ])
                    ->collapsible(),
                Forms\Components\Section::make('Posting Options')
                    ->columns(2)
                    ->schema([
                        Forms\Components\Toggle::make('is_paid')
                            ->label('Paid Listing')
                            ->default(false),
                        Forms\Components\DateTimePicker::make('paid_expires_at')
                            ->label('Paid Expires At')
                            ->visible(fn ($get) => $get('is_paid')),
                        Forms\Components\Toggle::make('is_promoted')
                            ->label('Promoted Listing')
                            ->default(false),
                        Forms\Components\DateTimePicker::make('promoted_expires_at')
                            ->label('Promoted Expires At')
                            ->visible(fn ($get) => $get('is_promoted')),
                        Forms\Components\Toggle::make('is_sponsored')
                            ->label('Sponsored Listing')
                            ->default(false),
                        Forms\Components\DateTimePicker::make('sponsored_expires_at')
                            ->label('Sponsored Expires At')
                            ->visible(fn ($get) => $get('is_sponsored')),
                        Forms\Components\Toggle::make('is_business')
                            ->label('Business Listing')
                            ->default(false),
                        Forms\Components\DateTimePicker::make('business_expires_at')
                            ->label('Business Expires At')
                            ->visible(fn ($get) => $get('is_business')),
                        Forms\Components\Toggle::make('is_store')
                            ->label('Store Listing')
                            ->default(false),
                        Forms\Components\DateTimePicker::make('store_expires_at')
                            ->label('Store Expires At')
                            ->visible(fn ($get) => $get('is_store')),
                    ])
                    ->collapsible(),
                Forms\Components\Section::make('Ads Images')
                    ->hiddenLabel()
                    ->collapsible()
                    ->schema([
                        Forms\Components\FileUpload::make('attachments')
                            ->multiple()
                            ->directory('listings')
                            ->image()
                            ->hiddenLabel()
                            ->maxSize(512)
                            ->columnSpan('full')
                            ->maxFiles(5),
                        // ->default(fn ($record) => $record ? Storage::url('listings/' . $record->img) : null)
                        // ->afterStateUpdated(function ($state, $set) {
                        //     if ($state) {
                        //         // Store only the file name in the database
                        //         $set('img', basename($state));
                        //     }
                        // }),
                    ]),
                Forms\Components\Section::make('Approval & Moderation')
                    ->columns(2)
                    ->schema([
                        Forms\Components\Select::make('approval_status')
                            ->label('Approval Status')
                            ->options([
                                'pending' => 'Pending',
                                'approved' => 'Approved',
                                'rejected' => 'Rejected',
                            ])
                            ->default('pending')
                            ->required(),
                        Forms\Components\Select::make('post_type')
                            ->label('Post Type')
                            ->options([
                                'regular' => 'Regular',
                                'sponsored' => 'Sponsored',
                                'promoted' => 'Promoted',
                                'admin' => 'Admin Post',
                            ])
                            ->default('regular')
                            ->required(),
                        Forms\Components\Toggle::make('is_admin_post')
                            ->label('Admin Post')
                            ->default(false),
                        Forms\Components\Toggle::make('is_harmful')
                            ->label('Harmful Content')
                            ->default(false),
                        Forms\Components\Textarea::make('rejection_reason')
                            ->label('Rejection Reason')
                            ->visible(fn ($get) => $get('approval_status') === 'rejected'),
                        Forms\Components\Textarea::make('moderation_notes')
                            ->label('Moderation Notes')
                            ->visible(fn ($get) => $get('is_harmful')),
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('package.title')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('customer.name')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('location.country_name')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('category.name')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('title')
                    ->searchable(),
                Tables\Columns\TextColumn::make('price')
                    ->formatStateUsing(function ($record) {
                        $currencyCode = $record->currency->code; // Assuming you have a relationship with currency
                        $price = number_format($record->price, 2); // Format the price with two decimal places

                        return "{$currencyCode} {$price}"; // Return formatted price with currency code
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('job_type')
                    ->badge()
                    ->color('info')
                    ->searchable(),
                Tables\Columns\TextColumn::make('salary_range')
                    ->label('Salary Range')
                    ->formatStateUsing(function ($record) {
                        if ($record->salary_min && $record->salary_max) {
                            return '$' . number_format($record->salary_min, 0) . ' - $' . number_format($record->salary_max, 0);
                        }
                        return '-';
                    })
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_featured')
                    ->label('Featured')
                    ->boolean(),
                Tables\Columns\IconColumn::make('is_suggested')
                    ->label('Suggested')
                    ->boolean(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'draft' => 'gray',
                        'active' => 'success',
                        'deactivated' => 'warning',
                        'expired' => 'danger',
                    })
                    ->searchable(),
                Tables\Columns\TextColumn::make('approval_status')
                    ->label('Approval')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'pending' => 'warning',
                        'approved' => 'success',
                        'rejected' => 'danger',
                    })
                    ->searchable(),
                Tables\Columns\TextColumn::make('post_type')
                    ->label('Type')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'regular' => 'gray',
                        'sponsored' => 'primary',
                        'promoted' => 'info',
                        'admin' => 'warning',
                    }),
                Tables\Columns\IconColumn::make('is_admin_post')
                    ->label('Admin Post')
                    ->boolean(),
                Tables\Columns\IconColumn::make('is_harmful')
                    ->label('Harmful')
                    ->boolean()
                    ->color('danger'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'draft' => 'Draft',
                        'active' => 'Active',
                        'deactivated' => 'Deactivated',
                        'expired' => 'Expired',
                    ]),
                Tables\Filters\SelectFilter::make('job_type')
                    ->options([
                        'full-time' => 'Full Time',
                        'part-time' => 'Part Time',
                        'contract' => 'Contract',
                        'freelance' => 'Freelance',
                        'internship' => 'Internship',
                    ]),
                Tables\Filters\TernaryFilter::make('is_featured')
                    ->label('Featured Jobs'),
                Tables\Filters\TernaryFilter::make('is_suggested')
                    ->label('Suggested Jobs'),
                Tables\Filters\TernaryFilter::make('is_paid')
                    ->label('Paid Listings'),
                Tables\Filters\TernaryFilter::make('is_promoted')
                    ->label('Promoted Listings'),
                Tables\Filters\TernaryFilter::make('is_sponsored')
                    ->label('Sponsored Listings'),
                Tables\Filters\TernaryFilter::make('is_business')
                    ->label('Business Listings'),
                Tables\Filters\TernaryFilter::make('is_store')
                    ->label('Store Listings'),
                Tables\Filters\SelectFilter::make('category_id')
                    ->label('Category')
                    ->relationship('category', 'name')
                    ->searchable()
                    ->preload(),
                Tables\Filters\SelectFilter::make('location_id')
                    ->label('Location')
                    ->relationship('location', 'city')
                    ->searchable()
                    ->preload(),
                Tables\Filters\SelectFilter::make('approval_status')
                    ->label('Approval Status')
                    ->options([
                        'pending' => 'Pending',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                    ]),
                Tables\Filters\SelectFilter::make('post_type')
                    ->label('Post Type')
                    ->options([
                        'regular' => 'Regular',
                        'sponsored' => 'Sponsored',
                        'promoted' => 'Promoted',
                        'admin' => 'Admin Post',
                    ]),
                Tables\Filters\TernaryFilter::make('is_admin_post')
                    ->label('Admin Posts'),
                Tables\Filters\TernaryFilter::make('is_harmful')
                    ->label('Harmful Content'),
                Tables\Filters\Filter::make('expired')
                    ->label('Expired Jobs')
                    ->query(fn (Builder $query): Builder => $query->where(function($q) {
                        $q->where('status', 'expired')
                          ->orWhere(function($q2) {
                              $q2->whereNotNull('end_date')
                                 ->where('end_date', '<', now());
                          });
                    })),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('approve')
                    ->label('Approve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn ($record) => $record->approval_status === 'pending')
                    ->form([
                        Forms\Components\Select::make('post_type')
                            ->label('Post Type')
                            ->options([
                                'regular' => 'Regular',
                                'sponsored' => 'Sponsored',
                                'promoted' => 'Promoted',
                                'admin' => 'Admin Post',
                            ])
                            ->default('regular')
                            ->required(),
                    ])
                    ->action(function ($record, $data) {
                        $record->approve(auth()->id(), $data['post_type']);
                    }),
                Tables\Actions\Action::make('reject')
                    ->label('Reject')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn ($record) => $record->approval_status === 'pending')
                    ->form([
                        Forms\Components\Textarea::make('reason')
                            ->label('Rejection Reason')
                            ->required()
                            ->maxLength(500),
                    ])
                    ->action(function ($record, $data) {
                        $record->reject($data['reason']);
                    }),
                Tables\Actions\Action::make('mark_harmful')
                    ->label('Mark Harmful')
                    ->icon('heroicon-o-shield-exclamation')
                    ->color('warning')
                    ->visible(fn ($record) => !$record->is_harmful)
                    ->form([
                        Forms\Components\Textarea::make('reason')
                            ->label('Reason')
                            ->required()
                            ->maxLength(500),
                    ])
                    ->action(function ($record, $data) {
                        $record->markAsHarmful($data['reason']);
                    }),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('activate')
                        ->label('Activate')
                        ->icon('heroicon-o-check-circle')
                        ->action(fn ($records) => $records->each->update(['status' => 'active']))
                        ->deselectRecordsAfterCompletion(),
                    Tables\Actions\BulkAction::make('deactivate')
                        ->label('Deactivate')
                        ->icon('heroicon-o-x-circle')
                        ->action(fn ($records) => $records->each->update(['status' => 'deactivated']))
                        ->deselectRecordsAfterCompletion(),
                    Tables\Actions\BulkAction::make('approve_bulk')
                        ->label('Approve Selected')
                        ->icon('heroicon-o-check')
                        ->color('success')
                        ->visible(fn ($records) => $records->where('approval_status', 'pending')->isNotEmpty())
                        ->form([
                            Forms\Components\Select::make('post_type')
                                ->label('Post Type')
                                ->options([
                                    'regular' => 'Regular',
                                    'sponsored' => 'Sponsored', 
                                    'promoted' => 'Promoted',
                                    'admin' => 'Admin Post',
                                ])
                                ->default('regular')
                                ->required(),
                        ])
                        ->action(function ($records, $data) {
                            $records->where('approval_status', 'pending')->each(function ($record) use ($data) {
                                $record->approve(auth()->id(), $data['post_type']);
                            });
                        })
                        ->deselectRecordsAfterCompletion(),
                    Tables\Actions\BulkAction::make('reject_bulk')
                        ->label('Reject Selected')
                        ->icon('heroicon-o-x')
                        ->color('danger')
                        ->visible(fn ($records) => $records->where('approval_status', 'pending')->isNotEmpty())
                        ->form([
                            Forms\Components\Textarea::make('reason')
                                ->label('Rejection Reason')
                                ->required()
                                ->maxLength(500),
                        ])
                        ->action(function ($records, $data) {
                            $records->where('approval_status', 'pending')->each(function ($record) use ($data) {
                                $record->reject($data['reason']);
                            });
                        })
                        ->deselectRecordsAfterCompletion(),
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
            'index' => Pages\ListListings::route('/'),
            'create' => Pages\CreateListing::route('/create'),
            'view' => Pages\ViewListing::route('/{record}'),
            'edit' => Pages\EditListing::route('/{record}/edit'),
        ];
    }

}
