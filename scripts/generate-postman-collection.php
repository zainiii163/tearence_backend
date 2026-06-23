<?php

/**
 * Generate complete Postman collection from Laravel routes (api/v1 only)
 */
$routeListFile = $argv[1] ?? null;
$outputFile = $argv[2] ?? __DIR__ . '/../WWA API Collection - Complete.json';

if (!$routeListFile || !file_exists($routeListFile)) {
    fwrite(STDERR, "Usage: php generate-postman-collection.php <route-list.json> [output.json]\n");
    exit(1);
}

$routes = json_decode(file_get_contents($routeListFile), true);
if (!is_array($routes)) {
    fwrite(STDERR, "Failed to parse route list JSON\n");
    exit(1);
}

// Sample request bodies for common endpoints
$sampleBodies = [
    'POST api/v1/auth/register' => "{\n    \"first_name\": \"John\",\n    \"last_name\": \"Doe\",\n    \"email\": \"user@example.com\",\n    \"password\": \"password123\",\n    \"password_confirmation\": \"password123\"\n}",
    'POST api/v1/auth/login' => "{\n    \"email\": \"user@example.com\",\n    \"password\": \"password123\"\n}",
    'POST api/v1/auth/login-admin' => "{\n    \"email\": \"admin@example.com\",\n    \"password\": \"password123\"\n}",
    'POST api/v1/auth/forgot-password' => "{\n    \"email\": \"user@example.com\"\n}",
    'POST api/v1/auth/reset-password' => "{\n    \"email\": \"user@example.com\",\n    \"token\": \"reset-token\",\n    \"password\": \"newpassword123\",\n    \"password_confirmation\": \"newpassword123\"\n}",
    'POST api/v1/auth/change-password' => "{\n    \"current_password\": \"password123\",\n    \"password\": \"newpassword123\",\n    \"password_confirmation\": \"newpassword123\"\n}",
    'POST api/v1/auth/web-login' => "{\n    \"email\": \"user@example.com\",\n    \"password\": \"password123\"\n}",
    'POST api/v1/jobs/{id}/apply' => "{\n    \"cover_letter\": \"I am interested in this position.\",\n    \"resume_url\": \"https://example.com/resume.pdf\"\n}",
    'POST api/v1/vehicles/{id}/enquiry' => "{\n    \"name\": \"John Doe\",\n    \"email\": \"john@example.com\",\n    \"phone\": \"+1234567890\",\n    \"message\": \"I am interested in this vehicle.\"\n}",
    'POST api/v1/sponsored-adverts' => "{\n    \"title\": \"My Sponsored Advert\",\n    \"description\": \"Advert description\",\n    \"category_id\": 1,\n    \"price\": 100\n}",
    'POST api/v1/buy-sell' => "{\n    \"title\": \"Item for sale\",\n    \"description\": \"Item description\",\n    \"price\": 50,\n    \"category_id\": 1\n}",
    'POST api/v1/properties' => "{\n    \"title\": \"Beautiful Home\",\n    \"description\": \"Property description\",\n    \"price\": 250000,\n    \"property_type\": \"residential\"\n}",
    'POST api/v1/services' => "{\n    \"title\": \"Professional Service\",\n    \"description\": \"Service description\",\n    \"category_id\": 1,\n    \"price\": 100\n}",
];

// Routes that always require auth regardless of middleware string
$authRequiredPatterns = [
    'api/v1/auth/logout',
    'api/v1/auth/user-profile',
    'api/v1/auth/change-password',
];

// Skip debug/test routes
$skipPatterns = [
    '/debug',
    '/test-route',
    '/debug-auth',
    '/debug-policy',
    '/test-vehicle-store-auth',
    '-simple-test',
    '/sponsored-adverts-simple-test',
];

function normalizeMethod(string $method): array
{
    $parts = explode('|', strtoupper($method));
    $priority = ['POST', 'PUT', 'PATCH', 'DELETE', 'GET'];
    foreach ($priority as $p) {
        if (in_array($p, $parts, true)) {
            return [$p];
        }
    }
    return [$parts[0] ?? 'GET'];
}

function shouldSkip(string $uri): bool
{
    global $skipPatterns;
    foreach ($skipPatterns as $pattern) {
        if (str_contains($uri, $pattern)) {
            return true;
        }
    }
    return false;
}

// Filter API v1 routes only
$apiRoutes = [];
foreach ($routes as $route) {
    $uri = $route['uri'];
    if (!str_starts_with($uri, 'api/v1/') && $uri !== 'api/v1') {
        continue;
    }
    if (shouldSkip($uri)) {
        continue;
    }
    foreach (normalizeMethod($route['method']) as $method) {
        $key = $method . ' ' . $uri;
        $route['method'] = $method;
        $apiRoutes[$key] = $route;
    }
}
$apiRoutes = array_values($apiRoutes);

// Group routes
$groups = [];
foreach ($apiRoutes as $route) {
    $parts = explode('/', $route['uri']);
    $group = $parts[2] ?? 'general';
    $groups[$group][] = $route;
}

function humanizeGroupName(string $group): string
{
    $map = [
        'auth' => 'Authentication',
        'books-adverts' => 'Books Adverts',
        'books' => 'Books',
        'banner-ads' => 'Banner Ads',
        'banner' => 'Banners',
        'buy-sell' => 'Buy & Sell',
        'vehicles' => 'Vehicles',
        'vehicles-adverts' => 'Vehicles Adverts',
        'properties' => 'Properties',
        'jobs' => 'Jobs',
        'services' => 'Services',
        'sponsored-adverts' => 'Sponsored Adverts',
        'sponsored-pricing-plans' => 'Sponsored Pricing Plans',
        'featured-adverts' => 'Featured Adverts',
        'promoted-adverts' => 'Promoted Adverts',
        'funding-projects' => 'Funding Projects',
        'funding-pledges' => 'Funding Pledges',
        'donations' => 'Donations',
        'resorts-travel' => 'Resorts & Travel',
        'resorts-travel-categories' => 'Resorts Travel Categories',
        'events' => 'Events',
        'venues' => 'Venues',
        'venue-services' => 'Venue Services',
        'communities' => 'Communities',
        'affiliate' => 'Affiliate',
        'affiliate-programs' => 'Affiliate Programs',
        'affiliate-posts' => 'Affiliate Posts',
        'affiliates' => 'Affiliates',
        'listing' => 'Listings',
        'listing-approval' => 'Listing Approval',
        'listing-favorite' => 'Listing Favorites',
        'listing-package' => 'Listing Packages',
        'categories' => 'Categories',
        'category' => 'Categories',
        'customer' => 'Customers',
        'business' => 'Business',
        'chat' => 'Chat',
        'upload' => 'File Upload',
        'search' => 'Search',
        'analytics' => 'Analytics',
        'user-analytics' => 'User Analytics',
        'admin-analytics' => 'Admin Analytics',
        'admin' => 'Admin',
        'kyc' => 'KYC',
        'ads' => 'Ads Moderation',
        'classified' => 'Classifieds',
        'campaign' => 'Campaigns',
        'blog' => 'Blog',
        'donor' => 'Donors',
        'dashboard' => 'Dashboard',
        'staff' => 'Staff Management',
        'upsell' => 'Upsells',
        'upsells' => 'Upsells',
        'promotions' => 'Promotions',
        'reviews' => 'Reviews',
        'providers' => 'Providers',
        'service-analytics' => 'Service Analytics',
        'service-comparison' => 'Service Comparison',
        'service-orders' => 'Service Orders',
        'referral' => 'Referral',
        'store' => 'Store',
        'location' => 'Locations',
        'master' => 'Master Data',
        'calculators' => 'Calculators',
        'images-adverts' => 'Images Adverts',
        'job-alert' => 'Job Alerts',
        'job-upsell' => 'Job Upsells',
        'candidate-profile' => 'Candidate Profiles',
        'candidate-upsell' => 'Candidate Upsells',
        'ad-pricing-plans' => 'Ad Pricing Plans',
        'events-venues' => 'Events & Venues',
        'file-upload' => 'File Upload',
        'health' => 'System',
        'cors-test' => 'System',
        'buysell' => 'Buy & Sell',
        'buysell-categories' => 'Buy & Sell Categories',
        'buysell-promotions' => 'Buy & Sell Promotions',
        'buysell-upload' => 'Buy & Sell Upload',
        'buy-sell-items' => 'Buy & Sell Items',
        'funding' => 'Funding Projects',
        'funding-upsells' => 'Funding Upsells',
        'authors' => 'Authors',
        'banner-categories' => 'Banner Categories',
        'banner-marketplace' => 'Banner Marketplace',
        'banner-upload' => 'Banner Upload',
        'comments' => 'Comments',
        'community-posts' => 'Community Posts',
        'job-categories' => 'Job Categories',
        'job-seekers' => 'Job Seekers',
        'promoted-advert-categories' => 'Promoted Advert Categories',
        'property-upsells' => 'Property Upsells',
        'vehicle-categories' => 'Vehicle Categories',
        'vehicle-makes' => 'Vehicle Makes',
    ];
    return $map[$group] ?? ucwords(str_replace(['-', '_'], ' ', $group));
}

function buildPathArray(string $uri): array
{
    $parts = explode('/', $uri);
    $path = [];
    foreach ($parts as $part) {
        if (preg_match('/^\{(.+)\}$/', $part, $m)) {
            $path[] = '{{' . $m[1] . '}}';
        } else {
            $path[] = $part;
        }
    }
    return $path;
}

function buildRawUrl(string $uri): string
{
    return '{{base_url}}/' . implode('/', buildPathArray($uri));
}

function needsAuth(array $route): bool
{
    global $authRequiredPatterns;
    $uri = $route['uri'];
    foreach ($authRequiredPatterns as $pattern) {
        if ($uri === $pattern || str_starts_with($uri, $pattern . '/')) {
            return true;
        }
    }
    $middleware = $route['middleware'] ?? '';
    if (is_array($middleware)) {
        $middleware = implode(',', $middleware);
    }
    $middlewareLower = strtolower($middleware);
    return str_contains($middlewareLower, 'auth')
        || str_contains($middlewareLower, 'jwt')
        || str_contains($middlewareLower, 'admin');
}

function buildRequestName(array $route): string
{
    $method = $route['method'];
    $uri = $route['uri'];
    $action = $route['action'] ?? '';

    if (preg_match('/@(\w+)$/', $action, $m)) {
        $methodName = $m[1];
        $name = preg_replace('/([A-Z])/', ' $1', $methodName);
        $name = trim(ucwords($name));
        return $name;
    }

    $parts = explode('/', $uri);
    $last = end($parts);
    if ($last === 'v1' || $last === 'api') {
        return 'Root';
    }
    if (preg_match('/^\{(.+)\}$/', $last, $m)) {
        return 'Get By ' . ucfirst($m[1]);
    }
    return ucwords(str_replace(['-', '_'], ' ', $last));
}

function buildDescription(array $route): string
{
    $parts = [
        '**Endpoint:** `/' . $route['uri'] . '`',
        '**Method:** `' . $route['method'] . '`',
        '**Controller:** `' . ($route['action'] ?? 'N/A') . '`',
    ];
    if (!empty($route['name'])) {
        $parts[] = '**Route Name:** `' . $route['name'] . '`';
    }
    $parts[] = needsAuth($route) ? '**Authentication:** Required — Bearer `{{auth_token}}`' : '**Authentication:** Not required';
    return implode("\n\n", $parts);
}

function buildBody(array $route): ?array
{
    global $sampleBodies;
    $method = $route['method'];
    if (!in_array($method, ['POST', 'PUT', 'PATCH'], true)) {
        return null;
    }
    $key = $method . ' ' . $route['uri'];
    $raw = $sampleBodies[$key] ?? "{\n    \n}";
    return [
        'mode' => 'raw',
        'raw' => $raw,
        'options' => ['raw' => ['language' => 'json']],
    ];
}

function buildRequest(array $route): array
{
    $method = $route['method'];
    $uri = $route['uri'];
    $hasBody = in_array($method, ['POST', 'PUT', 'PATCH'], true);

    $request = [
        'method' => $method,
        'header' => [
            ['key' => 'Accept', 'value' => 'application/json'],
        ],
        'url' => [
            'raw' => buildRawUrl($uri),
            'host' => ['{{base_url}}'],
            'path' => buildPathArray($uri),
        ],
        'description' => buildDescription($route),
    ];

    if ($hasBody) {
        $request['header'][] = ['key' => 'Content-Type', 'value' => 'application/json'];
        $request['body'] = buildBody($route);
    }

    if (needsAuth($route)) {
        $request['auth'] = [
            'type' => 'bearer',
            'bearer' => [['key' => 'token', 'value' => '{{auth_token}}', 'type' => 'string']],
        ];
    } else {
        $request['auth'] = ['type' => 'noauth'];
    }

    // Auto-save token on login
    if ($uri === 'api/v1/auth/login' && $method === 'POST') {
        $request['event'] = [[
            'listen' => 'test',
            'script' => [
                'type' => 'text/javascript',
                'exec' => [
                    "if (pm.response.code === 200) {",
                    "    var json = pm.response.json();",
                    "    if (json.data && json.data.access_token) {",
                    "        pm.collectionVariables.set('auth_token', json.data.access_token);",
                    "    }",
                    "}",
                ],
            ],
        ]];
    }

    return $request;
}

$groupOrder = [
    'auth', 'health', 'cors-test',
    'books-adverts', 'books', 'jobs', 'vehicles', 'vehicles-adverts', 'properties',
    'buy-sell', 'services', 'service-analytics', 'service-orders', 'service-comparison',
    'sponsored-adverts', 'sponsored-pricing-plans', 'featured-adverts', 'promoted-adverts',
    'banner-ads', 'banner', 'funding-projects', 'funding-pledges', 'donations',
    'resorts-travel', 'resorts-travel-categories', 'events', 'venues', 'venue-services',
    'events-venues', 'communities', 'affiliate', 'affiliate-programs', 'affiliate-posts',
    'affiliates', 'listing', 'listing-approval', 'listing-favorite', 'listing-package',
    'category', 'customer', 'business', 'chat', 'upload', 'search', 'analytics',
    'user-analytics', 'admin-analytics', 'admin', 'kyc', 'ads', 'classified', 'campaign',
    'blog', 'donor', 'dashboard', 'staff', 'upsell', 'upsells', 'promotions', 'reviews',
    'providers', 'referral', 'store', 'location', 'master', 'calculators', 'images-adverts',
    'job-alert', 'job-upsell', 'candidate-profile', 'candidate-upsell', 'ad-pricing-plans',
];

$sortedGroups = [];
foreach ($groupOrder as $g) {
    if (isset($groups[$g])) {
        $sortedGroups[$g] = $groups[$g];
        unset($groups[$g]);
    }
}
foreach ($groups as $g => $gr) {
    $sortedGroups[$g] = $gr;
}

// Merge system routes
$systemRoutes = [];
foreach (['health', 'cors-test'] as $sys) {
    if (isset($sortedGroups[$sys])) {
        $systemRoutes = array_merge($systemRoutes, $sortedGroups[$sys]);
        unset($sortedGroups[$sys]);
    }
}
if (!empty($systemRoutes)) {
    $sortedGroups = ['system' => $systemRoutes] + $sortedGroups;
}

$items = [];
foreach ($sortedGroups as $groupKey => $groupRoutes) {
    usort($groupRoutes, fn($a, $b) => strcmp($a['uri'] . $a['method'], $b['uri'] . $b['method']));

    $groupItems = [];
    $seenNames = [];
    foreach ($groupRoutes as $route) {
        $name = buildRequestName($route);
        // Deduplicate names within group
        $baseName = $name;
        $counter = 2;
        while (isset($seenNames[$name])) {
            $name = $baseName . ' (' . $counter . ')';
            $counter++;
        }
        $seenNames[$name] = true;

        $groupItems[] = [
            'name' => $name,
            'request' => buildRequest($route),
        ];
    }

    if (!empty($groupItems)) {
        $items[] = [
            'name' => $groupKey === 'system' ? 'System' : humanizeGroupName($groupKey),
            'description' => 'API endpoints for ' . ($groupKey === 'system' ? 'system utilities' : humanizeGroupName($groupKey)),
            'item' => $groupItems,
        ];
    }
}

$collection = [
    'info' => [
        '_postman_id' => 'wwa-api-collection-2026-complete',
        'name' => 'WWA API Collection - Complete Backend',
        'description' => "Complete API collection for WWA (World Wide Ads) Laravel backend.\n\n**Base URL:** `{{base_url}}/api/v1`\n\n**Authentication:** Most protected endpoints require `Authorization: Bearer {{auth_token}}`. Use the Login request first — it auto-saves the token.\n\n**Generated from:** Laravel route:list (" . count($apiRoutes) . " endpoints)",
        'schema' => 'https://schema.getpostman.com/json/collection/v2.1.0/collection.json',
        '_exporter_id' => 'wwa-api',
    ],
    'variable' => [
        ['key' => 'base_url', 'value' => 'http://localhost:8000', 'type' => 'string'],
        ['key' => 'auth_token', 'value' => '', 'type' => 'string'],
        ['key' => 'id', 'value' => '1', 'type' => 'string'],
        ['key' => 'slug', 'value' => 'sample-slug', 'type' => 'string'],
    ],
    'auth' => [
        'type' => 'bearer',
        'bearer' => [['key' => 'token', 'value' => '{{auth_token}}', 'type' => 'string']],
    ],
    'item' => $items,
];

file_put_contents($outputFile, json_encode($collection, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . "\n");

$groupCount = count($items);
echo "Generated {$groupCount} groups with " . count($apiRoutes) . " API v1 routes\n";
echo "Output: {$outputFile}\n";

// Print group summary
foreach ($items as $item) {
    echo "  - {$item['name']}: " . count($item['item']) . " endpoints\n";
}
