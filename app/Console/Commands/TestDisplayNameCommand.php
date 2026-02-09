<?php

namespace App\Console\Commands;

use App\Models\Listing;
use App\Models\Customer;
use App\Models\CustomerBusiness;
use App\Models\CustomerStore;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class TestDisplayNameCommand extends Command
{
    protected $signature = 'test:display-name';
    protected $description = 'Test the display name functionality for listings';

    public function handle()
    {
        $this->info('Testing display name functionality...');
        
        // Test 1: Check if display_name field exists in database
        $this->info("\nTest 1: Check display_name field exists in database");
        $schema = DB::select('DESCRIBE listing');
        $fieldExists = false;
        foreach ($schema as $field) {
            if ($field->Field === 'display_name') {
                $fieldExists = true;
                break;
            }
        }
        
        if ($fieldExists) {
            $this->info('✓ display_name field exists in database');
        } else {
            $this->error('✗ display_name field missing in database');
        }
        
        // Test 2: Check if display_name is fillable in Listing model
        $this->info("\nTest 2: Check display_name is fillable in Listing model");
        $listing = new Listing();
        $fillable = $listing->getFillable();
        if (in_array('display_name', $fillable)) {
            $this->info('✓ display_name field is fillable');
        } else {
            $this->error('✗ display_name field is not fillable');
        }
        
        // Test 3: Check relationships exist
        $this->info("\nTest 3: Check relationships exist");
        if (method_exists($listing, 'business')) {
            $this->info('✓ business relationship exists');
        } else {
            $this->error('✗ business relationship missing');
        }
        
        if (method_exists($listing, 'store')) {
            $this->info('✓ store relationship exists');
        } else {
            $this->error('✗ store relationship missing');
        }
        
        // Test 4: Check methods exist
        $this->info("\nTest 4: Check methods exist");
        if (method_exists($listing, 'getDisplayName')) {
            $this->info('✓ getDisplayName method exists');
        } else {
            $this->error('✗ getDisplayName method missing');
        }
        
        if (method_exists($listing, 'setDisplayName')) {
            $this->info('✓ setDisplayName method exists');
        } else {
            $this->error('✗ setDisplayName method missing');
        }
        
        // Test 5: Test display name logic with mock data
        $this->info("\nTest 5: Test display name logic");
        
        // Test regular customer post
        $testListing = new Listing([
            'is_business' => false,
            'is_store' => false,
            'is_admin_post' => false
        ]);
        $testListing->setDisplayName();
        $displayName = $testListing->getDisplayName();
        $this->info('Regular post display name: ' . ($displayName ?: 'null'));
        
        // Test business post
        $testListing = new Listing([
            'is_business' => true,
            'is_store' => false,
            'is_admin_post' => false
        ]);
        $mockBusiness = new CustomerBusiness(['business_name' => 'Test Business']);
        $testListing->setRelation('business', $mockBusiness);
        $testListing->setDisplayName();
        $displayName = $testListing->getDisplayName();
        $this->info('Business post display name: ' . $displayName);
        
        // Test store post
        $testListing = new Listing([
            'is_business' => false,
            'is_store' => true,
            'is_admin_post' => false
        ]);
        $mockStore = new CustomerStore(['store_name' => 'Test Store']);
        $testListing->setRelation('store', $mockStore);
        $testListing->setDisplayName();
        $displayName = $testListing->getDisplayName();
        $this->info('Store post display name: ' . $displayName);
        
        // Test admin post
        $testListing = new Listing([
            'is_business' => false,
            'is_store' => false,
            'is_admin_post' => true
        ]);
        $mockAdmin = new User(['first_name' => 'Admin', 'last_name' => 'User']);
        $testListing->setRelation('approvedBy', $mockAdmin);
        $testListing->setDisplayName();
        $displayName = $testListing->getDisplayName();
        $this->info('Admin post display name: ' . $displayName);
        
        $this->info("\nDisplay name functionality test completed!");
        
        return 0;
    }
}
