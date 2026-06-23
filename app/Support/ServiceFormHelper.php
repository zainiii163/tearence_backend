<?php

namespace App\Support;

use Illuminate\Http\Request;

class ServiceFormHelper
{
    public const CURRENCIES = [
        'USD' => 'USD ($)',
        'EUR' => 'EUR (€)',
        'GBP' => 'GBP (£)',
        'AUD' => 'AUD (A$)',
        'CAD' => 'CAD (C$)',
        'JPY' => 'JPY (¥)',
    ];

    public const SERVICE_TYPES = [
        'freelance' => 'Freelance Service',
        'local' => 'Local Service',
        'business' => 'Business Service',
    ];

    public const STATUSES = [
        'draft' => 'Draft',
        'active' => 'Active',
        'paused' => 'Paused',
        'suspended' => 'Suspended',
    ];

    public const PROMOTION_TYPES = [
        'standard' => 'Standard',
        'promoted' => 'Promoted',
        'featured' => 'Featured',
        'sponsored' => 'Sponsored',
        'network_boost' => 'Network Boost',
    ];

    /**
     * Normalize list fields (whats_included, whats_not_included, languages) from
     * arrays, repeater objects, comma-separated strings, or newline-separated text.
     */
    public static function normalizeListField(mixed $value): ?array
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_string($value)) {
            if (str_contains($value, "\n") || str_contains($value, "\r")) {
                $items = preg_split('/\r\n|\r|\n/', $value);
            } elseif (str_contains($value, ',')) {
                $items = explode(',', $value);
            } else {
                $items = [$value];
            }

            $items = array_values(array_filter(array_map('trim', $items)));

            return $items === [] ? null : $items;
        }

        if (! is_array($value)) {
            return [trim((string) $value)];
        }

        $items = [];
        foreach ($value as $item) {
            if (is_array($item)) {
                $item = $item['item']
                    ?? $item['value']
                    ?? $item['language']
                    ?? $item['name']
                    ?? reset($item);
            }

            if (is_string($item) && trim($item) !== '') {
                $items[] = trim($item);
            }
        }

        return $items === [] ? null : array_values($items);
    }

    public static function normalizeRequestLists(Request $request): void
    {
        foreach (['whats_included', 'whats_not_included', 'languages'] as $field) {
            if ($request->has($field)) {
                $request->merge([
                    $field => self::normalizeListField($request->input($field)),
                ]);
            }
        }
    }

    /**
     * Build validated service attributes aligned with the admin panel form.
     */
    public static function buildAttributes(Request $request, bool $isUpdate = false): array
    {
        self::normalizeRequestLists($request);

        $fields = [
            'category_id',
            'title',
            'tagline',
            'description',
            'whats_included',
            'whats_not_included',
            'requirements',
            'service_type',
            'starting_price',
            'currency',
            'delivery_time',
            'availability',
            'country',
            'city',
            'latitude',
            'longitude',
            'service_area_radius',
            'languages',
            'status',
            'promotion_type',
        ];

        $attributes = [];
        foreach ($fields as $field) {
            if ($request->has($field)) {
                $attributes[$field] = $request->input($field);
            } elseif (! $isUpdate && $field === 'service_type') {
                $attributes['service_type'] = 'freelance';
            } elseif (! $isUpdate && $field === 'status') {
                $attributes['status'] = 'active';
            }
        }

        if (! $isUpdate && ! isset($attributes['promotion_type'])) {
            $attributes['promotion_type'] = 'standard';
        }

        return $attributes;
    }

    /**
     * Field definitions for the frontend service create/edit form.
     */
    public static function formSchema(): array
    {
        return [
            'sections' => [
                [
                    'key' => 'service_information',
                    'title' => 'Service Information',
                    'fields' => [
                        [
                            'name' => 'category_id',
                            'label' => 'Category',
                            'type' => 'select',
                            'required' => true,
                            'options_endpoint' => '/api/v1/services/categories',
                            'option_value' => 'id',
                            'option_label' => 'name',
                        ],
                        [
                            'name' => 'title',
                            'label' => 'Title',
                            'type' => 'text',
                            'required' => true,
                            'max_length' => 255,
                        ],
                        [
                            'name' => 'tagline',
                            'label' => 'Tagline',
                            'type' => 'text',
                            'required' => false,
                            'max_length' => 80,
                        ],
                        [
                            'name' => 'description',
                            'label' => 'Description',
                            'type' => 'textarea',
                            'required' => true,
                            'min_length' => 50,
                        ],
                        [
                            'name' => 'whats_included',
                            'label' => "What's Included",
                            'type' => 'string_list',
                            'required' => false,
                            'item_max_length' => 255,
                        ],
                        [
                            'name' => 'whats_not_included',
                            'label' => "What's Not Included",
                            'type' => 'string_list',
                            'required' => false,
                            'item_max_length' => 255,
                        ],
                        [
                            'name' => 'requirements',
                            'label' => 'Requirements from Buyer',
                            'type' => 'textarea',
                            'required' => false,
                            'max_length' => 1000,
                        ],
                    ],
                ],
                [
                    'key' => 'service_details',
                    'title' => 'Service Details',
                    'fields' => [
                        [
                            'name' => 'service_type',
                            'label' => 'Service Type',
                            'type' => 'select',
                            'required' => true,
                            'default' => 'freelance',
                            'options' => self::SERVICE_TYPES,
                        ],
                        [
                            'name' => 'starting_price',
                            'label' => 'Starting Price',
                            'type' => 'number',
                            'required' => true,
                            'min' => 0,
                        ],
                        [
                            'name' => 'currency',
                            'label' => 'Currency',
                            'type' => 'select',
                            'required' => true,
                            'default' => 'USD',
                            'options' => self::CURRENCIES,
                        ],
                        [
                            'name' => 'delivery_time',
                            'label' => 'Delivery Time (days)',
                            'type' => 'number',
                            'required' => false,
                            'min' => 1,
                            'max' => 365,
                        ],
                        [
                            'name' => 'country',
                            'label' => 'Country',
                            'type' => 'text',
                            'required' => true,
                            'max_length' => 100,
                        ],
                        [
                            'name' => 'city',
                            'label' => 'City',
                            'type' => 'text',
                            'required' => false,
                            'max_length' => 100,
                        ],
                        [
                            'name' => 'latitude',
                            'label' => 'Latitude',
                            'type' => 'number',
                            'required' => false,
                        ],
                        [
                            'name' => 'longitude',
                            'label' => 'Longitude',
                            'type' => 'number',
                            'required' => false,
                        ],
                        [
                            'name' => 'service_area_radius',
                            'label' => 'Service Area Radius (km)',
                            'type' => 'number',
                            'required' => false,
                            'min' => 0,
                            'max' => 1000,
                            'visible_when' => ['service_type' => 'local'],
                        ],
                        [
                            'name' => 'languages',
                            'label' => 'Languages Spoken',
                            'type' => 'string_list',
                            'required' => false,
                            'item_max_length' => 50,
                        ],
                        [
                            'name' => 'images',
                            'label' => 'Service Images',
                            'type' => 'image_list',
                            'required' => true,
                            'max_items' => 10,
                            'max_size_mb' => 10,
                            'accept' => ['jpeg', 'png', 'jpg', 'gif', 'webp'],
                            'upload_endpoint' => 'POST /api/v1/services/{id}/media',
                            'note' => 'First image becomes the listing thumbnail.',
                        ],
                    ],
                ],
                [
                    'key' => 'status',
                    'title' => 'Status',
                    'fields' => [
                        [
                            'name' => 'status',
                            'label' => 'Status',
                            'type' => 'select',
                            'required' => false,
                            'default' => 'active',
                            'options' => self::STATUSES,
                            'note' => 'New services are published as active by default.',
                        ],
                    ],
                ],
            ],
            'packages' => [
                'endpoint' => 'Include in POST/PUT body as packages[] (optional, max 5)',
                'fields' => [
                    'name' => ['type' => 'text', 'required' => true],
                    'description' => ['type' => 'textarea', 'required' => true],
                    'price' => ['type' => 'number', 'required' => true],
                    'delivery_time' => ['type' => 'number', 'required' => true],
                    'features' => ['type' => 'string_list', 'required' => false],
                    'revisions' => ['type' => 'number', 'required' => false, 'default' => 1],
                    'sort_order' => ['type' => 'number', 'required' => false],
                ],
            ],
            'media' => [
                'endpoint' => 'POST /api/v1/services/{id}/media',
                'fields' => [
                    'file' => ['type' => 'file', 'required' => true],
                    'type' => ['type' => 'select', 'options' => ['image', 'video', 'document']],
                    'caption' => ['type' => 'text', 'required' => false],
                    'is_thumbnail' => ['type' => 'boolean', 'required' => false],
                ],
            ],
        ];
    }
}
