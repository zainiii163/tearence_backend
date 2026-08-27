<?php

namespace Tests\Feature;

use App\Support\JobSchema;
use App\Support\StripUnknownModelColumns;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Tests\Support\FilamentHarness;
use Tests\TestCase;

class FilamentResourceCompileAndSaveTest extends TestCase
{
    public function createApplication()
    {
        putenv('DB_CONNECTION=sqlite');
        putenv('DB_DATABASE=:memory:');
        $_ENV['DB_CONNECTION'] = 'sqlite';
        $_ENV['DB_DATABASE'] = ':memory:';
        $_SERVER['DB_CONNECTION'] = 'sqlite';
        $_SERVER['DB_DATABASE'] = ':memory:';

        $app = require __DIR__.'/../../bootstrap/app.php';
        $app->make(Kernel::class)->bootstrap();

        $app['config']->set('database.default', 'sqlite');
        $app['config']->set('database.connections.sqlite.database', ':memory:');
        $app['config']->set('database.connections.sqlite.prefix', '');
        $app['config']->set('database.connections.sqlite.foreign_key_constraints', false);

        return $app;
    }

    protected function setUp(): void
    {
        parent::setUp();
        StripUnknownModelColumns::resetCache();
        JobSchema::reset();
        Schema::dropIfExists('dummy_filament_harness');
        Schema::create('dummy_filament_harness', function (Blueprint $table) {
            $table->id();
        });
    }

    public function test_every_filament_resource_form_and_table_can_be_built(): void
    {
        $failures = [];
        $ok = 0;
        $warnings = 0;
        $harness = new FilamentHarness;

        foreach (File::allFiles(app_path('Filament')) as $file) {
            $class = $this->classFromPath($file->getPathname());
            if (! $class || ! class_exists($class) || ! is_subclass_of($class, Resource::class)) {
                continue;
            }
            if ((new \ReflectionClass($class))->isAbstract()) {
                continue;
            }

            try {
                $class::form(Form::make($harness)->model($class::getModel()));
                $class::table(Table::make($harness)->query($class::getModel()::query()));
                $ok++;
            } catch (\Illuminate\Database\QueryException $e) {
                $warnings++;
            } catch (\Throwable $e) {
                $failures[] = $class . ': ' . $e::class . ' — ' . $e->getMessage();
            }
        }

        $this->assertSame(
            [],
            $failures,
            "Filament resources failed to compile form/table:\n" . implode("\n", $failures)
        );
        $this->assertGreaterThan(40, $ok + $warnings, 'Expected to discover Filament resources');
    }

    public function test_job_admin_create_payload_saves_against_legacy_jobs_schema(): void
    {
        Schema::create('jobs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('job_category_id');
            $table->string('title');
            $table->string('slug')->nullable();
            $table->text('description')->nullable();
            $table->string('company_name')->nullable();
            $table->string('country')->nullable();
            $table->string('city')->nullable();
            $table->string('work_type');
            $table->string('experience_level')->nullable();
            $table->string('education_level')->nullable();
            $table->string('application_method')->nullable();
            $table->boolean('is_active')->default(true);
            $table->string('status')->default('active');
            $table->timestamp('posted_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });

        JobSchema::reset();

        $payload = JobSchema::normalizeAdminPayload([
            'user_id' => 1,
            'category_id' => 9,
            'title' => 'Warehouse picker',
            'description' => 'Pick orders',
            'company_name' => 'Acme',
            'country' => 'United Kingdom',
            'city' => 'London',
            'work_type' => 'freelance',
            'experience_level' => 'entry',
            'education_level' => 'doctorate',
            'application_method' => 'website',
            'is_active' => true,
            'status' => 'active',
            'posted_at' => now(),
            'not_a_real_column' => 'drop-me',
        ]);

        $this->assertArrayHasKey('job_category_id', $payload);
        $this->assertSame(9, $payload['job_category_id']);
        $this->assertArrayNotHasKey('category_id', $payload);
        $this->assertSame('contract', $payload['work_type']);
        $this->assertSame('phd', $payload['education_level']);
        $this->assertSame('link', $payload['application_method']);
        $this->assertArrayNotHasKey('not_a_real_column', $payload);

        $job = \App\Models\Job::create($payload);
        $this->assertNotNull($job->id);
        $this->assertSame('Warehouse picker', $job->title);
        $this->assertSame(9, $job->job_category_id);
    }

    public function test_saving_strips_attributes_that_are_not_database_columns(): void
    {
        Schema::create('funding_projects', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('customer_id')->nullable();
            $table->string('title');
            $table->text('problem_solved')->nullable();
            $table->string('region')->nullable();
            $table->timestamps();
        });

        StripUnknownModelColumns::resetCache();

        $normalized = \App\Support\FundingProjectSchema::normalizeAdminPayload([
            'title' => 'Solar roof',
            'customer_id' => 3,
            'problem_solving' => 'High energy bills',
            'city' => 'Manchester',
            'amount_raised' => 50,
            'not_a_column' => 'drop-me',
        ]);

        $this->assertSame('High energy bills', $normalized['problem_solved']);
        $this->assertSame('Manchester', $normalized['region']);
        $this->assertArrayNotHasKey('problem_solving', $normalized);
        $this->assertArrayNotHasKey('not_a_column', $normalized);
        $this->assertArrayNotHasKey('amount_raised', $normalized);

        $project = \App\Models\FundingProject::create($normalized);
        $this->assertDatabaseHas('funding_projects', [
            'title' => 'Solar roof',
            'customer_id' => 3,
            'problem_solved' => 'High energy bills',
            'region' => 'Manchester',
        ]);
        $this->assertNotNull($project->id);
    }

    public function test_zone_job_alert_venue_service_listing_and_advertisement_can_save(): void
    {
        Schema::create('zone', function (Blueprint $table) {
            $table->id('zone_id');
            $table->unsignedBigInteger('country_id');
            $table->string('name');
            $table->string('code')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('job_alerts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('customer_id')->nullable();
            $table->string('name');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('venue_services', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->nullable();
            $table->string('category')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('listing', function (Blueprint $table) {
            $table->id('listing_id');
            $table->unsignedBigInteger('customer_id')->nullable();
            $table->string('title');
            $table->string('status')->nullable();
            $table->string('approval_status')->nullable();
            $table->timestamps();
        });

        Schema::create('advertisement', function (Blueprint $table) {
            $table->id('advertisement_id');
            $table->string('title');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('job_applications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('job_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('status')->nullable();
            $table->timestamp('applied_at')->nullable();
            $table->timestamps();
        });

        StripUnknownModelColumns::resetCache();

        $zone = \App\Models\Zone::create([
            'country_id' => 1,
            'name' => 'London',
            'code' => 'LDN',
            'is_active' => true,
            'sort_order' => 1,
            'unknown_field' => 'nope',
        ]);
        $this->assertNotNull($zone->zone_id);

        $alert = \App\Models\JobAlert::create([
            'customer_id' => 1,
            'name' => 'Warehouse jobs',
            'is_active' => true,
            'deleted_at' => now(),
        ]);
        $this->assertNotNull($alert->getKey());

        $service = \App\Models\VenueService::create([
            'name' => 'Catering',
            'slug' => 'catering',
            'category' => 'food',
            'is_active' => true,
        ]);
        $this->assertNotNull($service->id);

        $listing = \App\Models\Listing::create([
            'title' => 'Used sofa',
            'status' => 'active',
            'approval_status' => 'approved',
        ]);
        $this->assertNotNull($listing->listing_id);

        $ad = \App\Models\Advertisement::create([
            'title' => 'Homepage banner',
            'is_active' => true,
            'pricing_plan_id' => 99,
        ]);
        $this->assertNotNull($ad->advertisement_id);

        $application = \App\Models\JobApplication::create([
            'job_id' => 1,
            'user_id' => 1,
            'status' => 'pending',
            'applied_at' => now(),
            'job_listing_id' => 1,
        ]);
        $this->assertNotNull($application->id);
    }

    public function test_zone_resource_default_sort_is_a_string(): void
    {
        $harness = new FilamentHarness;
        $table = \App\Filament\Resources\ZoneResource::table(Table::make($harness));
        $this->assertSame('name', $table->getDefaultSortColumn());
    }

    private function classFromPath(string $path): ?string
    {
        $relative = str_replace(app_path() . DIRECTORY_SEPARATOR, '', $path);
        $relative = str_replace(['/', '\\'], '\\', $relative);
        $relative = preg_replace('/\.php$/', '', $relative);

        return $relative ? 'App\\' . $relative : null;
    }
}
