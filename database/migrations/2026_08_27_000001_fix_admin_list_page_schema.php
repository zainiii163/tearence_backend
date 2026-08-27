<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Production Filament admin list pages 500 when expected columns are missing
 * or when older table shapes (OpenCart-style zone/country, advertisements vs
 * advertisement) do not match the models.
 */
return new class extends Migration
{
    public function up(): void
    {
        $this->ensureListingModerationColumns();
        $this->ensureAdvertisementColumns();
        $this->ensureZoneCountryColumns();
        $this->ensureJobAlertsColumns();
        $this->ensureVenueServicesColumns();
        $this->ensureFundingProjectColumns();
    }

    public function down(): void
    {
        // Keep columns — safer than dropping production data columns.
    }

    private function ensureListingModerationColumns(): void
    {
        if (! Schema::hasTable('listing')) {
            return;
        }

        Schema::table('listing', function (Blueprint $table) {
            if (! Schema::hasColumn('listing', 'approval_status')) {
                $table->string('approval_status', 50)->default('approved');
            }
            if (! Schema::hasColumn('listing', 'approved_by')) {
                $table->unsignedBigInteger('approved_by')->nullable();
            }
            if (! Schema::hasColumn('listing', 'approved_at')) {
                $table->timestamp('approved_at')->nullable();
            }
            if (! Schema::hasColumn('listing', 'rejection_reason')) {
                $table->text('rejection_reason')->nullable();
            }
            if (! Schema::hasColumn('listing', 'post_type')) {
                $table->string('post_type', 50)->default('regular');
            }
            if (! Schema::hasColumn('listing', 'is_admin_post')) {
                $table->boolean('is_admin_post')->default(false);
            }
            if (! Schema::hasColumn('listing', 'is_harmful')) {
                $table->boolean('is_harmful')->default(false);
            }
            if (! Schema::hasColumn('listing', 'moderation_notes')) {
                $table->text('moderation_notes')->nullable();
            }
            if (! Schema::hasColumn('listing', 'last_reposted_at')) {
                $table->timestamp('last_reposted_at')->nullable();
            }
        });
    }

    private function ensureAdvertisementColumns(): void
    {
        if (! Schema::hasTable('advertisement')) {
            return;
        }

        Schema::table('advertisement', function (Blueprint $table) {
            if (! Schema::hasColumn('advertisement', 'type')) {
                $table->string('type', 50)->nullable();
            }
            if (! Schema::hasColumn('advertisement', 'pricing_plan_id')) {
                $table->unsignedBigInteger('pricing_plan_id')->nullable();
            }
            if (! Schema::hasColumn('advertisement', 'price')) {
                $table->decimal('price', 10, 2)->nullable();
            }
            if (! Schema::hasColumn('advertisement', 'payment_status')) {
                $table->string('payment_status', 32)->default('pending');
            }
            if (! Schema::hasColumn('advertisement', 'payment_transaction_id')) {
                $table->string('payment_transaction_id')->nullable();
            }
            if (! Schema::hasColumn('advertisement', 'created_by')) {
                $table->unsignedInteger('created_by')->nullable();
            }
        });
    }

    private function ensureZoneCountryColumns(): void
    {
        if (Schema::hasTable('zone')) {
            Schema::table('zone', function (Blueprint $table) {
                if (! Schema::hasColumn('zone', 'is_active')) {
                    $table->boolean('is_active')->default(true);
                }
                if (! Schema::hasColumn('zone', 'sort_order')) {
                    $table->integer('sort_order')->default(0);
                }
            });

            if (Schema::hasColumn('zone', 'status') && Schema::hasColumn('zone', 'is_active')) {
                DB::table('zone')->update([
                    'is_active' => DB::raw('CASE WHEN status = 1 THEN 1 ELSE 0 END'),
                ]);
            }
        }

        if (Schema::hasTable('country')) {
            Schema::table('country', function (Blueprint $table) {
                if (! Schema::hasColumn('country', 'is_active')) {
                    $table->boolean('is_active')->default(true);
                }
                if (! Schema::hasColumn('country', 'sort_order')) {
                    $table->integer('sort_order')->default(0);
                }
            });

            if (Schema::hasColumn('country', 'status') && Schema::hasColumn('country', 'is_active')) {
                DB::table('country')->update([
                    'is_active' => DB::raw('CASE WHEN status = 1 THEN 1 ELSE 0 END'),
                ]);
            }
        }
    }

    private function ensureJobAlertsColumns(): void
    {
        if (! Schema::hasTable('job_alerts')) {
            Schema::create('job_alerts', function (Blueprint $table) {
                $table->id('job_alert_id');
                $table->unsignedInteger('customer_id');
                $table->string('name', 255);
                $table->json('keywords')->nullable();
                $table->unsignedInteger('location_id')->nullable();
                $table->unsignedInteger('category_id')->nullable();
                $table->json('job_type')->nullable();
                $table->decimal('salary_min', 10, 2)->nullable();
                $table->decimal('salary_max', 10, 2)->nullable();
                $table->string('frequency', 32)->default('daily');
                $table->boolean('is_active')->default(true);
                $table->string('notification_email', 255)->nullable();
                $table->dateTime('last_notified_at')->nullable();
                $table->integer('last_matched_count')->default(0);
                $table->timestamps();
                $table->softDeletes();
            });

            return;
        }

        Schema::table('job_alerts', function (Blueprint $table) {
            if (! Schema::hasColumn('job_alerts', 'is_active')) {
                $table->boolean('is_active')->default(true);
            }
            if (! Schema::hasColumn('job_alerts', 'deleted_at')) {
                $table->softDeletes();
            }
            if (! Schema::hasColumn('job_alerts', 'name') && ! Schema::hasColumn('job_alerts', 'title')) {
                $table->string('name', 255)->nullable();
            }
            if (! Schema::hasColumn('job_alerts', 'customer_id')) {
                $table->unsignedInteger('customer_id')->nullable();
            }
            if (! Schema::hasColumn('job_alerts', 'location_id')) {
                $table->unsignedInteger('location_id')->nullable();
            }
            if (! Schema::hasColumn('job_alerts', 'category_id')) {
                $table->unsignedInteger('category_id')->nullable();
            }
            if (! Schema::hasColumn('job_alerts', 'job_type')) {
                $table->json('job_type')->nullable();
            }
            if (! Schema::hasColumn('job_alerts', 'last_notified_at')) {
                $table->dateTime('last_notified_at')->nullable();
            }
            if (! Schema::hasColumn('job_alerts', 'last_matched_count')) {
                $table->integer('last_matched_count')->default(0);
            }
            if (! Schema::hasColumn('job_alerts', 'notification_email')) {
                $table->string('notification_email', 255)->nullable();
            }
        });

        if (Schema::hasColumn('job_alerts', 'active') && Schema::hasColumn('job_alerts', 'is_active')) {
            DB::table('job_alerts')->update([
                'is_active' => DB::raw('CASE WHEN active = 1 THEN 1 ELSE 0 END'),
            ]);
        }

        if (Schema::hasColumn('job_alerts', 'title') && Schema::hasColumn('job_alerts', 'name')) {
            DB::table('job_alerts')->whereNull('name')->update([
                'name' => DB::raw('title'),
            ]);
        }
    }

    private function ensureVenueServicesColumns(): void
    {
        if (! Schema::hasTable('venue_services')) {
            return;
        }

        Schema::table('venue_services', function (Blueprint $table) {
            if (! Schema::hasColumn('venue_services', 'deleted_at')) {
                $table->softDeletes();
            }
            if (! Schema::hasColumn('venue_services', 'category') && Schema::hasColumn('venue_services', 'service_category')) {
                $table->string('category', 64)->nullable();
            }
        });

        if (Schema::hasColumn('venue_services', 'service_category') && Schema::hasColumn('venue_services', 'category')) {
            DB::table('venue_services')->whereNull('category')->update([
                'category' => DB::raw('service_category'),
            ]);
        }
    }

    private function ensureFundingProjectColumns(): void
    {
        if (! Schema::hasTable('funding_projects')) {
            return;
        }

        Schema::table('funding_projects', function (Blueprint $table) {
            if (! Schema::hasColumn('funding_projects', 'customer_id') && Schema::hasColumn('funding_projects', 'user_id')) {
                $table->unsignedBigInteger('customer_id')->nullable();
            }
            if (! Schema::hasColumn('funding_projects', 'amount_raised')) {
                $table->decimal('amount_raised', 15, 2)->default(0);
            }
            if (! Schema::hasColumn('funding_projects', 'backer_count')) {
                $table->integer('backer_count')->default(0);
            }
            if (! Schema::hasColumn('funding_projects', 'is_active')) {
                $table->boolean('is_active')->default(true);
            }
        });

        if (Schema::hasColumn('funding_projects', 'user_id') && Schema::hasColumn('funding_projects', 'customer_id')) {
            DB::table('funding_projects')->whereNull('customer_id')->update([
                'customer_id' => DB::raw('user_id'),
            ]);
        }

        if (Schema::hasColumn('funding_projects', 'current_funded') && Schema::hasColumn('funding_projects', 'amount_raised')) {
            DB::table('funding_projects')->where('amount_raised', 0)->update([
                'amount_raised' => DB::raw('current_funded'),
            ]);
        }

        if (Schema::hasColumn('funding_projects', 'backers_count') && Schema::hasColumn('funding_projects', 'backer_count')) {
            DB::table('funding_projects')->where('backer_count', 0)->update([
                'backer_count' => DB::raw('backers_count'),
            ]);
        }
    }
};
