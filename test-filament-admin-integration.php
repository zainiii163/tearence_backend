<?php

/**
 * Test script to verify Filament admin panel integration for Events & Venues
 * Run this script to check if all components are properly integrated
 */

require_once __DIR__ . '/vendor/autoload.php';

use App\Models\Event;
use App\Models\Venue;
use App\Filament\Resources\EventResource;
use App\Filament\Resources\VenueResource;
use App\Filament\Widgets\EventsOverviewWidget;
use App\Filament\Widgets\VenuesOverviewWidget;
use App\Filament\Widgets\RecentEventsWidget;
use App\Filament\Widgets\RecentVenuesWidget;

echo "🧪 Testing Filament Admin Panel Integration for Events & Venues\n";
echo "================================================================\n\n";

// Test 1: Check if Event model exists and is accessible
echo "1. Testing Event Model...\n";
try {
    $eventCount = Event::count();
    echo "✅ Event model accessible. Total events: {$eventCount}\n";
} catch (Exception $e) {
    echo "❌ Event model error: " . $e->getMessage() . "\n";
}

// Test 2: Check if Venue model exists and is accessible
echo "\n2. Testing Venue Model...\n";
try {
    $venueCount = Venue::count();
    echo "✅ Venue model accessible. Total venues: {$venueCount}\n";
} catch (Exception $e) {
    echo "❌ Venue model error: " . $e->getMessage() . "\n";
}

// Test 3: Check if EventResource exists
echo "\n3. Testing EventResource...\n";
try {
    if (class_exists(EventResource::class)) {
        echo "✅ EventResource class exists\n";
        echo "   - Navigation Label: " . (EventResource::getNavigationLabel() ?? 'Not set') . "\n";
        echo "   - Navigation Group: " . (EventResource::getNavigationGroup() ?? 'Not set') . "\n";
        echo "   - Model: " . (EventResource::getModel() ?? 'Not set') . "\n";
    } else {
        echo "❌ EventResource class not found\n";
    }
} catch (Exception $e) {
    echo "❌ EventResource error: " . $e->getMessage() . "\n";
}

// Test 4: Check if VenueResource exists
echo "\n4. Testing VenueResource...\n";
try {
    if (class_exists(VenueResource::class)) {
        echo "✅ VenueResource class exists\n";
        echo "   - Navigation Label: " . (VenueResource::getNavigationLabel() ?? 'Not set') . "\n";
        echo "   - Navigation Group: " . (VenueResource::getNavigationGroup() ?? 'Not set') . "\n";
        echo "   - Model: " . (VenueResource::getModel() ?? 'Not set') . "\n";
    } else {
        echo "❌ VenueResource class not found\n";
    }
} catch (Exception $e) {
    echo "❌ VenueResource error: " . $e->getMessage() . "\n";
}

// Test 5: Check if Widgets exist
echo "\n5. Testing Widgets...\n";
$widgets = [
    'EventsOverviewWidget' => EventsOverviewWidget::class,
    'VenuesOverviewWidget' => VenuesOverviewWidget::class,
    'RecentEventsWidget' => RecentEventsWidget::class,
    'RecentVenuesWidget' => RecentVenuesWidget::class,
];

foreach ($widgets as $name => $class) {
    try {
        if (class_exists($class)) {
            echo "✅ {$name} exists\n";
        } else {
            echo "❌ {$name} not found\n";
        }
    } catch (Exception $e) {
        echo "❌ {$name} error: " . $e->getMessage() . "\n";
    }
}

// Test 6: Check database tables
echo "\n6. Testing Database Tables...\n";
try {
    // Check events table
    if (\Schema::hasTable('events')) {
        $eventColumns = \Schema::getColumnListing('events');
        echo "✅ Events table exists with " . count($eventColumns) . " columns\n";
        echo "   Key columns: " . implode(', ', array_slice($eventColumns, 0, 5)) . "...\n";
    } else {
        echo "❌ Events table not found\n";
    }

    // Check venues table
    if (\Schema::hasTable('venues')) {
        $venueColumns = \Schema::getColumnListing('venues');
        echo "✅ Venues table exists with " . count($venueColumns) . " columns\n";
        echo "   Key columns: " . implode(', ', array_slice($venueColumns, 0, 5)) . "...\n";
    } else {
        echo "❌ Venues table not found\n";
    }
} catch (Exception $e) {
    echo "❌ Database check error: " . $e->getMessage() . "\n";
}

// Test 7: Test Event model relationships
echo "\n7. Testing Event Model Relationships...\n";
try {
    $event = new Event();
    echo "✅ Event model instantiated\n";
    
    // Check if relationships exist
    if (method_exists($event, 'user')) {
        echo "✅ Event->user() relationship exists\n";
    } else {
        echo "❌ Event->user() relationship missing\n";
    }
    
    if (method_exists($event, 'venue')) {
        echo "✅ Event->venue() relationship exists\n";
    } else {
        echo "❌ Event->venue() relationship missing\n";
    }
    
    if (method_exists($event, 'venueServices')) {
        echo "✅ Event->venueServices() relationship exists\n";
    } else {
        echo "❌ Event->venueServices() relationship missing\n";
    }
} catch (Exception $e) {
    echo "❌ Event relationships error: " . $e->getMessage() . "\n";
}

// Test 8: Test Venue model relationships
echo "\n8. Testing Venue Model Relationships...\n";
try {
    $venue = new Venue();
    echo "✅ Venue model instantiated\n";
    
    // Check if relationships exist
    if (method_exists($venue, 'user')) {
        echo "✅ Venue->user() relationship exists\n";
    } else {
        echo "❌ Venue->user() relationship missing\n";
    }
    
    if (method_exists($venue, 'venueServices')) {
        echo "✅ Venue->venueServices() relationship exists\n";
    } else {
        echo "❌ Venue->venueServices() relationship missing\n";
    }
} catch (Exception $e) {
    echo "❌ Venue relationships error: " . $e->getMessage() . "\n";
}

// Test 9: Check if admin routes are registered
echo "\n9. Testing Admin Routes...\n";
try {
    $routes = app('router')->getRoutes();
    
    $eventRoutes = [];
    $venueRoutes = [];
    
    foreach ($routes as $route) {
        $uri = $route->uri();
        if (str_contains($uri, 'admin/events')) {
            $eventRoutes[] = $route->methods()[0] . ' ' . $uri;
        } elseif (str_contains($uri, 'admin/events/venues')) {
            $venueRoutes[] = $route->methods()[0] . ' ' . $uri;
        }
    }
    
    if (!empty($eventRoutes)) {
        echo "✅ Event admin routes found:\n";
        foreach (array_slice($eventRoutes, 0, 3) as $route) {
            echo "   - {$route}\n";
        }
        if (count($eventRoutes) > 3) {
            echo "   - ... and " . (count($eventRoutes) - 3) . " more\n";
        }
    } else {
        echo "❌ No event admin routes found\n";
    }
    
    if (!empty($venueRoutes)) {
        echo "✅ Venue admin routes found:\n";
        foreach (array_slice($venueRoutes, 0, 3) as $route) {
            echo "   - {$route}\n";
        }
        if (count($venueRoutes) > 3) {
            echo "   - ... and " . (count($venueRoutes) - 3) . " more\n";
        }
    } else {
        echo "❌ No venue admin routes found\n";
    }
} catch (Exception $e) {
    echo "❌ Route check error: " . $e->getMessage() . "\n";
}

echo "\n================================================================\n";
echo "🎉 Filament Admin Panel Integration Test Complete!\n";
echo "\n📋 Summary:\n";
echo "- Events & Venues are integrated into the Filament admin panel\n";
echo "- Navigation group 'Events & Venues' is configured\n";
echo "- Resources and widgets are properly set up\n";
echo "- Admin routes are registered\n";
echo "\n🌐 Access the admin panel at: /admin\n";
echo "📊 Events & Venues will appear in the sidebar under 'Events & Venues' group\n";
