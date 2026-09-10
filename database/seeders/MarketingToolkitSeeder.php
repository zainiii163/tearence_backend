<?php

namespace Database\Seeders;

use App\Models\MarketingToolkit;
use Illuminate\Database\Seeder;

class MarketingToolkitSeeder extends Seeder
{
    public function run(): void
    {
        $toolkits = $this->getToolkits();

        foreach ($toolkits as $toolkit) {
            MarketingToolkit::updateOrCreate(
                ['name' => $toolkit['name'], 'category_slug' => $toolkit['category_slug']],
                $toolkit
            );
        }
    }

    private function getToolkits(): array
    {
        return array_merge(
            $this->getPropertyToolkits(),
            $this->getCommercialPropertyToolkits(),
            $this->getVehiclesToolkits(),
            $this->getJobsToolkits(),
            $this->getServicesToolkits(),
            $this->getEventsToolkits(),
            $this->getResortsToolkits(),
            $this->getBooksToolkits(),
            $this->getBuySellToolkits(),
            $this->getBusinessToolkits(),
            $this->getFundingToolkits(),
            $this->getDonationsToolkits(),
            $this->getAdvertsToolkits(),
        );
    }

    private function getPropertyToolkits(): array
    {
        return [
            [
                'category_slug' => 'property',
                'tool_type' => 'guide',
                'name' => 'Property Listing Description Generator',
                'description' => 'AI-powered template for writing compelling property descriptions that attract buyers.',
                'content' => null,
                'items' => [
                    'formula' => '[Adjective] [property type] in [neighborhood] featuring [key feature 1], [key feature 2], and [key feature 3]. Perfect for [buyer persona].',
                    'structure' => [
                        'Lead with lifestyle (how it feels to live there)',
                        'List key features (bedrooms, bathrooms, sqft)',
                        'Highlight location benefits (schools, transport, shops)',
                        'Close with urgency (available from, viewing recommended)',
                    ],
                    'adjectives' => ['Stunning', 'Elegant', 'Spacious', 'Modern', 'Charming', 'Contemporary', 'Beautifully renovated', 'Bright and airy'],
                    'buyer_personas' => ['first-time buyers', 'growing families', 'young professionals', 'investors', 'downsizers'],
                ],
                'sort_order' => 1,
            ],
            [
                'category_slug' => 'property',
                'tool_type' => 'checklist',
                'name' => 'Photo Quality Checklist',
                'description' => 'Ensure your property photos meet professional standards to maximize buyer interest.',
                'content' => null,
                'items' => [
                    'minimum_photos' => 12,
                    'required_shots' => [
                        'Exterior front (curb appeal)',
                        'Kitchen (most viewed room)',
                        'Master bedroom',
                        'Master bathroom',
                        'Living room',
                        'Dining area',
                        'Garden/outdoor space',
                        'Additional bedrooms',
                        'Hallway/entrance',
                        'Parking/garage',
                        'Local area highlights',
                        'Floor plan (if available)',
                    ],
                    'technical_requirements' => [
                        'Resolution: minimum 2048px wide',
                        'Lighting: natural light preferred, all lights on',
                        'Staging: declutter, minimal personal items',
                        'Composition: wide-angle for rooms, straight-on for features',
                        'Time of day: golden hour for exteriors',
                    ],
                    'common_mistakes' => [
                        'Dark or blurry photos',
                        'Cluttered rooms',
                        'Toilet visible in bathroom shots',
                        'Personal items (family photos, mail)',
                        'Closed curtains/blinds',
                    ],
                ],
                'sort_order' => 2,
            ],
            [
                'category_slug' => 'property',
                'tool_type' => 'calculator',
                'name' => 'Mortgage Affordability Calculator',
                'description' => 'Help buyers understand their budget with this interactive mortgage calculator.',
                'content' => null,
                'items' => [
                    'inputs' => ['property_price', 'deposit_amount', 'interest_rate', 'term_years'],
                    'outputs' => ['monthly_payment', 'total_interest', 'total_cost', 'loan_to_value'],
                    'default_rate' => 5.5,
                    'typical_terms' => [15, 20, 25, 30],
                    'formula' => 'M = P * [r(1+r)^n] / [(1+r)^n - 1]',
                    'explanation' => 'Where M = monthly payment, P = principal (price - deposit), r = monthly interest rate, n = total number of payments',
                ],
                'sort_order' => 3,
            ],
            [
                'category_slug' => 'property',
                'tool_type' => 'calculator',
                'name' => 'Rental Yield Calculator',
                'description' => 'Calculate rental yield for investment properties to evaluate return on investment.',
                'content' => null,
                'items' => [
                    'gross_yield_formula' => '(Annual Rent / Property Price) x 100',
                    'net_yield_formula' => '((Annual Rent - Annual Costs) / Property Price) x 100',
                    'annual_costs' => ['Management fees (10-15%)', 'Maintenance', 'Insurance', 'Void periods', 'Mortgage interest'],
                    'good_yield_benchmarks' => [
                        'UK average: 4-5%',
                        'London: 2-3%',
                        'North West: 5-7%',
                        'Scotland: 5-6%',
                    ],
                ],
                'sort_order' => 4,
            ],
            [
                'category_slug' => 'property',
                'tool_type' => 'template',
                'name' => 'Social Media Caption Generator',
                'description' => 'Ready-to-use social media captions for property listings across all platforms.',
                'content' => null,
                'items' => [
                    'new_listing' => "🏠 New to Market! \n\n[Property Type] in [Location]\n🛏 [X] Bedrooms | 🛁 [X] Bathrooms\n💰 [Price]\n\n[Key Feature 1]\n[Key Feature 2]\n[Key Feature 3]\n\n📩 DM to arrange a viewing\n🔗 [Link]\n\n#[Location]Property #ForSale #RealEstate #NewListing",
                    'price_reduction' => "📉 PRICE REDUCED!\n\nNow asking [New Price] (was [Old Price])\n\n[Property Type] in [Location]\n[X] Bed | [X] Bath | [Sqft] sqft\n\nDon't miss this opportunity!\n\n#[Location]Property #PriceDrop #PropertyDeal",
                    'open_house' => "🏡 OPEN HOUSE THIS WEEKEND!\n\n[Property Type] in [Location]\n📅 [Date] | ⏰ [Time]\n\n✨ [Key Feature]\n🛏 [X] Bedrooms | 🛁 [X] Bathrooms\n💰 [Price]\n\nCome see your future home!\n\n#[Location]Property #OpenHouse #Viewing",
                    'sold' => "🎉 SOLD!\n\nCongratulations to our happy buyers!\n\n[Property Type] in [Location]\n\nThank you for trusting us with your property journey.\n\nLooking to sell? Contact us today.\n\n#[Location]Property #Sold #HappyClients",
                ],
                'sort_order' => 5,
            ],
            [
                'category_slug' => 'property',
                'tool_type' => 'template',
                'name' => 'Email Drip Campaign Templates',
                'description' => '5-email sequence to nurture property leads from inquiry to viewing.',
                'content' => null,
                'items' => [
                    'email_1' => [
                        'subject' => 'Thank you for your interest in [Property Address]',
                        'timing' => 'Immediately',
                        'content' => 'Welcome email with property highlights, similar listings, and viewing scheduling link',
                    ],
                    'email_2' => [
                        'subject' => 'Discover [Neighborhood] — Your new neighborhood',
                        'timing' => 'Day 3',
                        'content' => 'Neighborhood spotlight: schools, transport, amenities, demographics',
                    ],
                    'email_3' => [
                        'subject' => 'Can you afford this property? Let us help',
                        'timing' => 'Day 5',
                        'content' => 'Mortgage affordability check, local lender recommendations, deposit calculator',
                    ],
                    'email_4' => [
                        'subject' => 'What similar properties have sold for',
                        'timing' => 'Day 7',
                        'content' => 'Recent sales data, market trends, price-per-sqft analysis',
                    ],
                    'email_5' => [
                        'subject' => 'Ready to view? Let us know',
                        'timing' => 'Day 10',
                        'content' => 'Final CTA for viewing, urgency about market conditions, contact options',
                    ],
                ],
                'sort_order' => 6,
            ],
            [
                'category_slug' => 'property',
                'tool_type' => 'guide',
                'name' => 'Neighborhood Guide Builder',
                'description' => 'Template for creating comprehensive neighborhood profiles that attract buyers.',
                'content' => null,
                'items' => [
                    'sections' => [
                        'Schools — names, ratings, distances, Ofsted/inspection results',
                        'Transport — nearest stations, bus routes, commute times to key destinations',
                        'Amenities — supermarkets, restaurants, parks, gyms, healthcare',
                        'Safety — crime rates, police presence, neighborhood watch',
                        'Demographics — age profile, family mix, income levels',
                        'Development — upcoming infrastructure, regeneration projects',
                    ],
                    'data_sources' => [
                        'Ofsted (schools)',
                        'Transport for London / National Rail',
                        'Police.uk (crime data)',
                        'ONS (demographics)',
                        'Rightmove/Zoopla (area guides)',
                    ],
                ],
                'sort_order' => 7,
            ],
            [
                'category_slug' => 'property',
                'tool_type' => 'checklist',
                'name' => 'Tenant Screening Checklist',
                'description' => 'Comprehensive reference template for landlords to screen potential tenants.',
                'content' => null,
                'items' => [
                    'required_documents' => [
                        'Proof of identity (passport/driving licence)',
                        'Proof of address (utility bill/bank statement)',
                        'Employment reference (last 3 months payslips or employment letter)',
                        'Bank statements (last 3 months)',
                        'Previous landlord reference',
                        'Credit check consent',
                    ],
                    'financial_checks' => [
                        'Income should be 2.5x the rent (or 3x for guarantor)',
                        'Credit score assessment',
                        'County Court Judgments (CCJ) check',
                        'Bankruptcy check',
                    ],
                    'reference_checks' => [
                        'Employment verification (confirm role, salary, start date)',
                        'Previous landlord (payment history, property care, notice given)',
                        'Personal reference (character, reliability)',
                    ],
                ],
                'sort_order' => 8,
            ],
            [
                'category_slug' => 'property',
                'tool_type' => 'guide',
                'name' => 'Property Staging ROI Guide',
                'description' => 'Data-driven guide to staging investments that yield the highest returns.',
                'content' => null,
                'items' => [
                    'staging_investments' => [
                        ['action' => 'Kitchen declutter & clean', 'cost' => '$200-500', 'roi' => '+5% sale price'],
                        ['action' => 'Fresh neutral paint', 'cost' => '$300-800', 'roi' => '+3-4% sale price'],
                        ['action' => 'Professional staging', 'cost' => '$2,000-5,000', 'roi' => '+6-10% sale price'],
                        ['action' => 'Garden landscaping', 'cost' => '$500-2,000', 'roi' => '+5-7% curb appeal'],
                        ['action' => 'Professional photography', 'cost' => '$200-500', 'roi' => '+40% more views'],
                        ['action' => 'Floor plan creation', 'cost' => '$100-300', 'roi' => '+30% more inquiries'],
                    ],
                    'key_findings' => [
                        'Staged homes sell 73% faster (NAR data)',
                        'Staged homes sell for 6-10% more than non-staged',
                        '95% of buyers start their search online — photos are critical',
                        'Kitchen and bathroom updates yield highest ROI',
                    ],
                ],
                'sort_order' => 9,
            ],
        ];
    }

    private function getCommercialPropertyToolkits(): array
    {
        return [
            [
                'category_slug' => 'property',
                'tool_type' => 'guide',
                'name' => 'Commercial Property Marketing Guide',
                'description' => 'Specialized marketing strategies for commercial real estate including offices, retail, and industrial spaces.',
                'content' => null,
                'items' => [
                    'target_audiences' => ['Business owners', 'Investors', 'Tenant representatives', 'Corporate relocators'],
                    'key_metrics' => ['Price per sqft', 'Rental yield', 'Cap rate', 'Net operating income', 'Service charge costs'],
                    'listing_essentials' => [
                        'Floor area (sqm/sqft)', 'Tenant mix (if multi-let)', 'Lease terms and expiry',
                        'Service charge details', 'Business rates', 'Parking ratio',
                        'EPC rating', 'Accessibility features', 'Loading/delivery access',
                    ],
                    'marketing_channels' => [
                        'Commercial property portals (CoStar, EG Radius)',
                        'LinkedIn outreach to business owners',
                        'Direct mail to target tenant sectors',
                        'Commercial agent networks',
                        'Business network events',
                    ],
                    'legal_considerations' => [
                        'EPC certificate (mandatory for marketing)',
                        'Asbestos survey (buildings pre-2000)',
                        'Fire safety compliance',
                        'Planning use class confirmation',
                    ],
                ],
                'sort_order' => 10,
            ],
            [
                'category_slug' => 'property',
                'tool_type' => 'template',
                'name' => 'Commercial Lease Terms Glossary',
                'description' => 'Plain-English explanations of commercial lease terminology for sellers and landlords.',
                'content' => null,
                'items' => [
                    'FRI Lease' => 'Full Repairing and Insuring — tenant responsible for all repairs and insurance. Most common for retail and industrial.',
                    'Internal Repairing Lease' => 'Landlord responsible for external structure; tenant responsible for internal repairs.',
                    'Gross Lease' => 'Single rent payment covering all costs. Landlord pays rates, service charge, insurance.',
                    'NNN Lease' => 'Triple Net — tenant pays rent plus all three: rates, service charge, and insurance.',
                    'Break Clause' => 'Right to terminate lease early, usually at 3 or 5 year mark. Must be exercised with notice.',
                    'Upward Only Rent Review' => 'Rent can only increase at review, never decrease. Standard in UK commercial leases.',
                    'Turnover Rent' => 'Rent calculated as percentage of tenant turnover. Common in shopping centres.',
                    'Key Money' => 'Upfront payment from tenant to landlord, common in restaurant/bar leases.',
                ],
                'sort_order' => 11,
            ],
            [
                'category_slug' => 'property',
                'tool_type' => 'calculator',
                'name' => 'Commercial Property Investment Calculator',
                'description' => 'Evaluate commercial property investments with cap rate, yield, and cash flow analysis.',
                'content' => null,
                'items' => [
                    'cap_rate_formula' => '(Net Operating Income / Purchase Price) x 100',
                    'noi_formula' => 'Gross Rental Income - Operating Expenses',
                    'cash_on_cash_formula' => '(Annual Cash Flow / Total Cash Invested) x 100',
                    'operating_expenses' => [
                        'Property management (5-10%)', 'Maintenance and repairs', 'Insurance',
                        'Business rates', 'Void allowance (5-10%)', 'Legal and professional fees',
                    ],
                    'investment_benchmarks' => [
                        'Office: 4-6% cap rate', 'Retail: 5-7% cap rate', 'Industrial: 6-8% cap rate',
                        'Good cash-on-cash: 8-12%',
                    ],
                ],
                'sort_order' => 12,
            ],
        ];
    }

    private function getVehiclesToolkits(): array
    {
        return [
            [
                'category_slug' => 'vehicles',
                'tool_type' => 'template',
                'name' => 'Vehicle Listing Description Template',
                'description' => 'Structured template for writing vehicle listings that attract serious buyers.',
                'content' => null,
                'items' => [
                    'format' => '[Year] [Make] [Model] [Trim] — [Mileage]km, [Condition] condition, [Key Feature]. [Urgency line].',
                    'required_info' => [
                        'Year, Make, Model, Trim/Variant',
                        'Mileage (km/miles)',
                        'Condition (excellent/good/fair)',
                        'Service history (full/partial/none)',
                        ' MOT expiry date',
                        'Number of previous owners',
                        'Colour (exterior and interior)',
                        'Engine size and fuel type',
                        'Transmission (manual/automatic)',
                        'Insurance group',
                    ],
                    'headline_formulas' => [
                        '[Year] [Make] [Model] — Only [X]km, Full History',
                        'LOW MILEAGE: [Description]',
                        'PRICE REDUCED — [Vehicle] Must Go',
                        'One Owner, Full Service History — [Vehicle]',
                    ],
                    'urgency_lines' => [
                        'Priced to sell — won\'t last',
                        'Part exchange welcome',
                        'Viewing by appointment',
                        'HPI clear, ready to go',
                    ],
                ],
                'sort_order' => 20,
            ],
            [
                'category_slug' => 'vehicles',
                'tool_type' => 'checklist',
                'name' => 'Vehicle Photo Angle Guide',
                'description' => 'Mandatory shot list and photography tips for vehicle listings.',
                'content' => null,
                'items' => [
                    'mandatory_shots' => [
                        '3/4 front view (driver side)',
                        '3/4 front view (passenger side)',
                        '3/4 rear view',
                        'Interior dashboard (engine on)',
                        'Interior rear seats',
                        'Engine bay (clean)',
                        'Wheel close-up (alloy condition)',
                        'Odometer reading',
                        'Any damage or scratches (honesty builds trust)',
                        'Registration plate',
                    ],
                    'photography_tips' => [
                        'Shoot in natural daylight — overcast is ideal',
                        'Clean the vehicle thoroughly before photos',
                        'Park against a clean, neutral background',
                        'Avoid wide-angle distortion on interiors',
                        'Capture the vehicle from slightly above eye level',
                    ],
                    'minimum_photos' => 8,
                ],
                'sort_order' => 21,
            ],
            [
                'category_slug' => 'vehicles',
                'tool_type' => 'calculator',
                'name' => 'Vehicle Finance Calculator',
                'description' => 'Help buyers understand monthly payments for vehicle purchases.',
                'content' => null,
                'items' => [
                    'inputs' => ['vehicle_price', 'deposit', 'term_months', 'interest_rate_apr', 'balloon_payment'],
                    'formula' => 'Monthly Payment = (P - B) * [r(1+r)^n] / [(1+r)^n - 1] + B * r',
                    'typical_terms' => [12, 24, 36, 48, 60],
                    'typical_apr' => ['New: 3-7%', 'Used: 6-15%', 'Bad credit: 15-30%'],
                    'balloon_payment_info' => 'A balloon payment reduces monthly costs but you owe a lump sum at the end. Common on PCP deals.',
                ],
                'sort_order' => 22,
            ],
            [
                'category_slug' => 'vehicles',
                'tool_type' => 'guide',
                'name' => 'Vehicle History Check Guide',
                'description' => 'What to verify before buying and how to build buyer confidence.',
                'content' => null,
                'items' => [
                    'what_to_check' => [
                        'MOT status and history',
                        'Outstanding finance',
                        'Insurance write-off history (Cat A/B/C/D/N)',
                        'Number of previous owners',
                        'Plate changes',
                        'Mileage discrepancies',
                        'Stolen vehicle check',
                        'VIN verification',
                    ],
                    'build_buyer_confidence' => [
                        'Provide HPI check report proactively',
                        'Show original purchase invoice',
                        'Include service receipts',
                        'Offer independent mechanical inspection',
                        'Allow test drive with valid licence and insurance',
                    ],
                    'commercial_vehicle_checks' => [
                        'Payload capacity',
                        'ULEZ/Clean Air Zone compliance',
                        'Road tax band',
                        'Insurance group',
                        'Operator licence (if applicable)',
                    ],
                ],
                'sort_order' => 23,
            ],
            [
                'category_slug' => 'vehicles',
                'tool_type' => 'template',
                'name' => 'Video Walkaround Script',
                'description' => '60-90 second video script for vehicle sellers to create compelling walkaround videos.',
                'content' => null,
                'items' => [
                    'script' => [
                        'INTRO (5s) — "Hi, I\'m [name] and this is my [Year Make Model]"',
                        'EXTERIOR WALK (20s) — Walk around the vehicle, highlighting condition and features',
                        'INTERIOR TOUR (20s) — Show dashboard, seats, boot, any technology features',
                        'ENGINE START (10s) — Start the engine, show idle, any warning lights (or lack thereof)',
                        'ODOMETER (5s) — Show the mileage reading',
                        'TEST DRIVE POV (15s) — Quick drive showing acceleration, braking, steering',
                        'PRICE & CTA (10s) — "Asking [Price]. Contact me on [platform] to arrange a viewing"',
                    ],
                    'production_tips' => [
                        'Use a phone stabiliser/gimbal',
                        'Film in landscape mode',
                        'Shoot in daylight, engine bay clean',
                        'Narrate clearly and confidently',
                        'Keep under 90 seconds',
                    ],
                ],
                'sort_order' => 24,
            ],
        ];
    }

    private function getJobsToolkits(): array
    {
        return [
            [
                'category_slug' => 'jobs',
                'tool_type' => 'template',
                'name' => 'Job Description Builder',
                'description' => 'Structured template for writing job postings that attract quality candidates.',
                'content' => null,
                'items' => [
                    'structure' => [
                        'Role Summary (2-3 sentences)',
                        'Key Responsibilities (5-7 bullet points)',
                        'Required Skills (must-have)',
                        'Nice-to-Have Skills',
                        'Benefits & Perks',
                        'About the Company',
                        'How to Apply',
                    ],
                    'headline_formulas' => [
                        'We\'re Hiring: [Role] at [Company]',
                        '[Role] — Join [Company] to [Impact]',
                        'Looking for a [Role]? We\'re looking for you',
                        '[Role] (Remote/Hybrid/On-site) — [Company]',
                    ],
                    'best_practices' => [
                        'Include salary range (increases applications by 30%)',
                        'State remote/hybrid/on-site in first line',
                        'List 5 must-have skills maximum',
                        'Include a "day in the life" section',
                        'Use gender-neutral language',
                    ],
                ],
                'sort_order' => 30,
            ],
            [
                'category_slug' => 'jobs',
                'tool_type' => 'template',
                'name' => 'Social Media Job Announcement Kit',
                'description' => 'Pre-formatted posts for LinkedIn, Twitter/X, and Facebook to promote job openings.',
                'content' => null,
                'items' => [
                    'linkedin' => "📢 We're hiring!\n\n[Role] — [Location/Remote]\n\nWhat you'll do:\n• [Responsibility 1]\n• [Responsibility 2]\n• [Responsibility 3]\n\nWhat we're looking for:\n• [Skill 1]\n• [Skill 2]\n• [Skill 3]\n\nWhat we offer:\n• [Benefit 1]\n• [Benefit 2]\n\nApply now: [Link]\n\n#Hiring #Jobs #[Industry]Jobs",
                    'twitter' => "🚀 We're growing! Looking for a [Role] to join our team.\n\n📍 [Location/Remote]\n💰 [Salary Range]\n\nInterested? Apply here: [Link]\n\n#NowHiring #[Industry]",
                    'facebook' => "💼 JOB OPPORTUNITY\n\nWe're looking for a [Role] to join [Company]!\n\n📍 Location: [Location]\n⏰ Type: [Full-time/Part-time/Contract]\n💰 Salary: [Range]\n\nAbout the role:\n[2-3 sentence summary]\n\nTo apply, send your CV to [email] or click [link].\n\nPlease share with anyone who might be interested! 🙏",
                ],
                'sort_order' => 31,
            ],
            [
                'category_slug' => 'jobs',
                'tool_type' => 'guide',
                'name' => 'Salary Benchmarking Guide',
                'description' => 'How to research and set competitive salary ranges for job postings.',
                'content' => null,
                'items' => [
                    'data_sources' => [
                        'Glassdoor salary data',
                        'Payscale',
                        'Indeed salary explorer',
                        'LinkedIn Salary Insights',
                        'Industry-specific salary surveys',
                        'ONS Annual Survey of Hours and Earnings',
                    ],
                    'salary_structure' => [
                        'Base salary',
                        'Performance bonus',
                        'Benefits value (health, pension, etc.)',
                        'Equity/stock options (if applicable)',
                        'Total compensation package',
                    ],
                    'transparency_benefits' => [
                        'Job postings with salary ranges get 30% more applications',
                        'Reduces time-to-fill by 20%',
                        'Builds trust with candidates',
                        'Filters unqualified applicants early',
                    ],
                ],
                'sort_order' => 32,
            ],
            [
                'category_slug' => 'jobs',
                'tool_type' => 'resource',
                'name' => 'Interview Question Bank',
                'description' => 'Role-specific interview questions organized by category for hiring managers.',
                'content' => null,
                'items' => [
                    'technical' => [
                        'Walk me through your process for [task]',
                        'Describe a challenging project you completed',
                        'What tools/technologies do you use daily?',
                    ],
                    'behavioral' => [
                        'Tell me about a time you handled a difficult situation',
                        'Describe your most successful project',
                        'How do you prioritize competing deadlines?',
                    ],
                    'culture_fit' => [
                        'What type of work environment do you thrive in?',
                        'How do you prefer to receive feedback?',
                        'What motivates you in your work?',
                    ],
                    'scenario_based' => [
                        'If you joined our team, what would your first 30 days look like?',
                        'How would you approach [specific challenge]?',
                        'What questions do you have for us?',
                    ],
                ],
                'sort_order' => 33,
            ],
        ];
    }

    private function getServicesToolkits(): array
    {
        return [
            [
                'category_slug' => 'services',
                'tool_type' => 'template',
                'name' => 'Service Package Builder',
                'description' => 'Create tiered pricing packages that convert visitors into clients.',
                'content' => null,
                'items' => [
                    'tier_structure' => [
                        'Basic' => ['price_indicator' => '$', 'features' => ['Core deliverable', '1 revision', 'Email support']],
                        'Standard' => ['price_indicator' => '$$', 'features' => ['Core deliverable', '3 revisions', 'Priority support', 'Bonus feature']],
                        'Premium' => ['price_indicator' => '$$$', 'features' => ['Full deliverable', 'Unlimited revisions', 'Dedicated support', 'All bonuses', 'Extended warranty']],
                    ],
                    'pricing_tips' => [
                        'Anchor with the premium tier first',
                        'Make Standard the "Most Popular" choice',
                        'Show savings vs. hourly pricing',
                        'Include money-back guarantee if possible',
                    ],
                ],
                'sort_order' => 40,
            ],
            [
                'category_slug' => 'services',
                'tool_type' => 'template',
                'name' => 'Portfolio Case Study Template',
                'description' => 'Structured format for showcasing your best work to attract new clients.',
                'content' => null,
                'items' => [
                    'structure' => [
                        'Client Challenge — What problem did they have?',
                        'Our Approach — What strategy did we use?',
                        'Results — What outcomes did we achieve? (metrics)',
                        'Client Testimonial — What did they say?',
                    ],
                    'metrics_to_include' => [
                        'Revenue generated or cost saved',
                        'Time to completion',
                        'Performance improvement %',
                        'Client satisfaction score',
                        'ROI achieved',
                    ],
                    'presentation_tips' => [
                        'Use before/after visuals',
                        'Include specific numbers, not vague claims',
                        'Get client permission before publishing',
                        'Keep to one page per case study',
                    ],
                ],
                'sort_order' => 41,
            ],
            [
                'category_slug' => 'services',
                'tool_type' => 'guide',
                'name' => 'Client Onboarding Pack',
                'description' => 'Professional onboarding templates for new client relationships.',
                'content' => null,
                'items' => [
                    'welcome_email' => [
                        'subject' => 'Welcome to [Business Name] — Let\'s get started',
                        'content' => 'Thank you message, next steps, timeline, what we need from you',
                    ],
                    'project_brief_questionnaire' => [
                        'Business goals and objectives',
                        'Target audience',
                        'Brand guidelines and assets',
                        'Competitors and inspiration',
                        'Budget and timeline expectations',
                        'Key stakeholders and approval process',
                    ],
                    'communication_preferences' => [
                        'Preferred contact method',
                        'Response time expectations',
                        'Meeting cadence',
                        'Project management tool',
                    ],
                ],
                'sort_order' => 42,
            ],
            [
                'category_slug' => 'services',
                'tool_type' => 'strategy',
                'name' => 'LinkedIn Profile Optimizer',
                'description' => 'Optimize your LinkedIn profile to attract inbound service inquiries.',
                'content' => null,
                'items' => [
                    'headline_formula' => 'I help [audience] achieve [outcome] through [method]',
                    'about_section' => 'Hook (opening line) → What you do → Who you help → How you do it → Results → CTA',
                    'featured_section' => 'Pin your best case studies, testimonials, and lead magnets',
                    'experience_formula' => 'Role at Company — [Impact-focused description with metrics]',
                    'content_strategy' => [
                        'Post 3-5 times per week',
                        'Share insights, not sales pitches',
                        'Engage with industry content',
                        'Comment thoughtfully on prospect posts',
                    ],
                ],
                'sort_order' => 43,
            ],
            [
                'category_slug' => 'services',
                'tool_type' => 'guide',
                'name' => 'Pricing Strategy Guide',
                'description' => 'How to price your services for maximum profitability and client acquisition.',
                'content' => null,
                'items' => [
                    'pricing_models' => [
                        'Hourly — Good for ongoing/unclear scope',
                        'Fixed project — Good for defined deliverables',
                        'Value-based — Best for experienced providers',
                        'Retainer — Best for recurring revenue',
                    ],
                    'pricing_by_service' => [
                        'Web Design: $1,500-10,000 project / $75-200/hr',
                        'SEO: $500-2,000/month retainer',
                        'Social Media Management: $300-1,500/month',
                        'Graphic Design: $50-150/hr / $200-1,000 project',
                        'Consulting: $150-500/hr depending on expertise',
                    ],
                    'value_pricing_tips' => [
                        'Price based on the value delivered, not hours worked',
                        'Always include a discovery call before quoting',
                        'Present 3 options (good, better, best)',
                        'Never negotiate price — adjust scope instead',
                    ],
                ],
                'sort_order' => 44,
            ],
        ];
    }

    private function getEventsToolkits(): array
    {
        return [
            [
                'category_slug' => 'events',
                'tool_type' => 'strategy',
                'name' => 'Event Promotion Timeline',
                'description' => '8-week countdown strategy for maximizing event attendance and ticket sales.',
                'content' => null,
                'items' => [
                    'week_8' => ['action' => 'Teaser announcement', 'channels' => ['Social media', 'Email list'], 'goal' => 'Build anticipation'],
                    'week_6' => ['action' => 'Speaker/performer reveals', 'channels' => ['Social media', 'Press release', 'Partner cross-promotion'], 'goal' => 'Credibility and reach'],
                    'week_4' => ['action' => 'Early bird deadline reminder', 'channels' => ['Email', 'Social ads', 'Retargeting'], 'goal' => 'Urgency and first sales wave'],
                    'week_2' => ['action' => 'Agenda drop + urgency push', 'channels' => ['All channels'], 'goal' => 'Inform and convert fence-sitters'],
                    'week_1' => ['action' => 'Last chance messaging', 'channels' => ['Email', 'SMS', 'Social'], 'goal' => 'Final ticket push'],
                    'day_of' => ['action' => 'Live coverage', 'channels' => ['Social media stories', 'Live stream', 'Photography'], 'goal' => 'Engagement and future promotion content'],
                    'post_event' => ['action' => 'Thank you + survey', 'channels' => ['Email', 'Social'], 'goal' => 'Feedback and retention'],
                ],
                'sort_order' => 50,
            ],
            [
                'category_slug' => 'events',
                'tool_type' => 'template',
                'name' => 'Email Campaign Sequence',
                'description' => '6-email series for event promotion from save-the-date to post-event follow-up.',
                'content' => null,
                'items' => [
                    'email_1_save_the_date' => 'Subject: Save the Date — [Event Name] [Date]',
                    'email_2_speaker_reveal' => 'Subject: Introducing our incredible lineup for [Event Name]',
                    'email_3_early_bird' => 'Subject: Early bird ends Friday — Don\'t miss out',
                    'email_4_agenda' => 'Subject: Full agenda released — See what\'s in store',
                    'email_5_last_chance' => 'Subject: Last chance — [Event Name] is [X] days away',
                    'email_6_post_event' => 'Subject: Thank you for attending [Event Name]!',
                ],
                'sort_order' => 51,
            ],
            [
                'category_slug' => 'events',
                'tool_type' => 'template',
                'name' => 'Ticket Pricing Calculator',
                'description' => 'Dynamic pricing model for event tickets with revenue projections.',
                'content' => null,
                'items' => [
                    'pricing_tiers' => [
                        'Early Bird — 20-30% discount (first 20% of tickets)',
                        'Standard — Full price',
                        'VIP — Premium experience (limited quantity)',
                        'Last Minute — Optional discount for unsold tickets',
                    ],
                    'revenue_formula' => 'Total Revenue = (Early Bird Qty × Price) + (Standard Qty × Price) + (VIP Qty × Price)',
                    'cost_considerations' => [
                        'Venue hire', 'Catering', 'AV/Technical', 'Marketing', 'Insurance',
                        'Staffing', 'Speaker fees/travel', 'Printed materials',
                    ],
                    'profit_margin_target' => 'Aim for 30-50% gross margin after direct costs',
                ],
                'sort_order' => 52,
            ],
            [
                'category_slug' => 'events',
                'tool_type' => 'guide',
                'name' => 'Venue Specifications Guide',
                'description' => 'What to include in venue listings to attract event organizers.',
                'content' => null,
                'items' => [
                    'essential_info' => [
                        'Capacity (seated and standing)',
                        'Floor dimensions (meters)',
                        'Ceiling height',
                        'AV equipment available',
                        'Catering options (in-house or external)',
                        'Parking capacity',
                        'Accessibility (wheelchair, hearing loop)',
                        'Setup and teardown times',
                        'Noise restrictions / curfew',
                        'Wi-Fi capacity',
                    ],
                    'venue_photos_needed' => [
                        'Main hall empty', 'Main hall set up for event type',
                        'Stage/platform area', 'AV and technical setup',
                        'Entrance and foyer', 'Catering area',
                        'Parking and exterior', 'Restrooms',
                    ],
                ],
                'sort_order' => 53,
            ],
        ];
    }

    private function getResortsToolkits(): array
    {
        return [
            [
                'category_slug' => 'resorts',
                'tool_type' => 'template',
                'name' => 'Property Listing Optimizer',
                'description' => 'Structured template for hotel, resort, and B&B listings that drive direct bookings.',
                'content' => null,
                'items' => [
                    'structure' => [
                        'Property Name & Location Tagline',
                        'At a Glance — key stats (stars, rooms, distance to beach/city)',
                        'The Space — detailed room descriptions',
                        'The Experience — what makes it unique',
                        'Guest Favorites — most loved features',
                        'Practical Info — check-in/out, parking, WiFi',
                        'Photo Gallery — minimum 20 professional photos',
                    ],
                    'photo_requirements' => [
                        'Exterior at golden hour', 'Lobby/entrance',
                        'Room interiors (bed, bathroom, view)', 'Dining area',
                        'Pool/spa', 'Local attractions', 'Breakfast spread',
                    ],
                    'description_formula' => 'Set the scene (location/vibe) → Describe the experience → List practical details → Create urgency',
                ],
                'sort_order' => 60,
            ],
            [
                'category_slug' => 'resorts',
                'tool_type' => 'guide',
                'name' => 'Seasonal Pricing Calendar',
                'description' => 'Dynamic pricing strategy for accommodations based on seasonality and demand.',
                'content' => null,
                'items' => [
                    'pricing_periods' => [
                        'Peak Season (summer/holidays) — Full rate',
                        'Shoulder Season (spring/autumn) — 10-20% discount',
                        'Off Season (winter) — 20-40% discount',
                        'Weekend Premium — 10-15% increase (city properties)',
                        'Holiday Premium — 20-50% increase',
                    ],
                    'pricing_factors' => [
                        'Local events and festivals',
                        'School holiday schedules',
                        'Weather patterns',
                        'Competitor pricing',
                        'Historical demand data',
                    ],
                    'direct_booking_incentives' => [
                        'Free room upgrade',
                        'Late checkout',
                        'Complimentary breakfast',
                        'Airport transfer',
                        '10-15% discount vs. OTA prices',
                    ],
                ],
                'sort_order' => 61,
            ],
            [
                'category_slug' => 'resorts',
                'tool_type' => 'template',
                'name' => 'Guest Review Response Templates',
                'description' => 'Professional response templates for all types of guest reviews.',
                'content' => null,
                'items' => [
                    'positive_review' => 'Thank you for staying with us, [Name]! We\'re thrilled you enjoyed [specific detail]. It was a pleasure hosting you, and we look forward to welcoming you back. — [Manager Name]',
                    'neutral_review' => 'Thank you for your feedback, [Name]. We appreciate you sharing your experience. We\'re glad you enjoyed [positive], and we\'ve noted your comments about [area for improvement] to help us improve. We hope to have the opportunity to host you again.',
                    'negative_review' => 'Dear [Name], thank you for your honest feedback. We sincerely apologize for [specific issue]. This falls below our standards, and we\'ve already [action taken]. We\'d love the opportunity to make this right — please contact us at [email/phone].',
                    'response_tips' => [
                        'Respond within 24 hours',
                        'Personalize with specific details from their review',
                        'Never argue or make excuses',
                        'Take the conversation offline for complaints',
                        'Show concrete actions taken',
                    ],
                ],
                'sort_order' => 62,
            ],
            [
                'category_slug' => 'resorts',
                'tool_type' => 'strategy',
                'name' => 'Travel Blogger Outreach Kit',
                'description' => 'Templates and strategies for partnering with travel influencers.',
                'content' => null,
                'items' => [
                    'pitch_email' => 'Subject: Complimentary Stay at [Property] — Collaboration Opportunity\n\nHi [Name],\n\nI love your content about [specific post/destination]. We\'d love to invite you for a complimentary stay at [Property] in [Location].\n\nWhat we offer:\n- [X] nights accommodation\n- [Meals/experiences included]\n\nWhat we\'d love in return:\n- [X] Instagram stories\n- [X] feed posts\n- Honest review\n\nWould you be interested? Happy to discuss details.',
                    'content_requirements' => [
                        'Minimum 3 Instagram stories during stay',
                        '1 feed post with property tag',
                        'Honest review (not scripted)',
                        'Photo/video content rights for our use',
                        'Hashtag requirements',
                    ],
                    'measurement' => [
                        'Track engagement on influencer posts',
                        'Monitor booking source "influencer" referrals',
                        'Calculate cost per acquisition vs. paid ads',
                    ],
                ],
                'sort_order' => 63,
            ],
        ];
    }

    private function getBooksToolkits(): array
    {
        return [
            [
                'category_slug' => 'books',
                'tool_type' => 'template',
                'name' => 'Book Listing Template',
                'description' => 'Professional book listing format that attracts readers and boosts sales.',
                'content' => null,
                'items' => [
                    'required_fields' => [
                        'Title', 'Author', 'Genre', 'Format (paperback/ebook/audiobook)',
                        'Page Count', 'Publication Date', 'ISBN',
                        'Synopsis (150 words)', 'Sample Chapter Preview', 'Author Bio',
                    ],
                    'synopsis_formula' => 'Hook (emotional opening) → Setup (main character + world) → Conflict (what\'s at stake) → Stakes (what happens if they fail) → Promise (emotional journey)',
                    'headline_formulas' => [
                        'A [Genre] That Will Keep You [Emotion]',
                        'From the Author of [Previous Book]',
                        'If You Loved [Popular Book], You\'ll Love This',
                    ],
                ],
                'sort_order' => 70,
            ],
            [
                'category_slug' => 'books',
                'tool_type' => 'strategy',
                'name' => 'Book Launch Social Media Kit',
                'description' => '14-day launch countdown strategy for maximum visibility and sales.',
                'content' => null,
                'items' => [
                    'day_14' => 'Cover reveal post',
                    'day_12' => 'Excerpt teaser (1-2 paragraphs)',
                    'day_10' => 'Character introduction post',
                    'day_8' => 'Behind-the-scenes of writing process',
                    'day_6' => 'Early review quotes',
                    'day_4' => 'Pre-order reminder with bonus',
                    'day_2' => 'Final countdown — "2 days to go"',
                    'day_1' => 'Eve of launch — "Tomorrow!"',
                    'launch_day' => '"Available now!" with purchase links',
                    'day_2_post' => 'First reader reactions',
                    'day_5_post' => 'Share a favourite quote',
                    'day_7_post' => 'One week anniversary — thank readers',
                ],
                'sort_order' => 71,
            ],
            [
                'category_slug' => 'books',
                'tool_type' => 'guide',
                'name' => 'Book Pricing Strategy',
                'description' => 'Pricing guidelines for different book formats and genres.',
                'content' => null,
                'items' => [
                    'pricing_ranges' => [
                        'Paperback: $9.99-16.99',
                        'Ebook: $2.99-9.99',
                        'Audiobook: $14.99-24.99',
                        'Hardcover: $19.99-29.99',
                    ],
                    'kdp_select_considerations' => [
                        'Exclusive to Amazon for 90 days',
                        'Access to Kindle Unlimited page reads',
                        'Higher royalty on pages read (approx. $0.004/page)',
                        'Promotional tools: Free Book Promotion, Kindle Countdown',
                    ],
                    'launch_pricing_tips' => [
                        'Launch ebook at $0.99 for first week to drive reviews',
                        'Raise to $2.99-4.99 after 50+ reviews',
                        'Keep paperback at full price from launch',
                        'Bundle offers: ebook + audiobook discount',
                    ],
                ],
                'sort_order' => 72,
            ],
            [
                'category_slug' => 'books',
                'tool_type' => 'resource',
                'name' => 'Author Bio Template',
                'description' => 'Professional author bio template for book listings and marketing materials.',
                'content' => null,
                'items' => [
                    'template' => '[Name] is a [genre] author who [unique selling point]. [He/They] [background/credentials]. [Previous works or achievements]. [Personal touch: location, hobbies, fun fact]. [CTA: newsletter signup or social media].',
                    'tips' => [
                        'Write in third person',
                        'Keep to 100-150 words',
                        'Lead with credentials',
                        'End with personality',
                        'Include a professional headshot',
                    ],
                ],
                'sort_order' => 73,
            ],
        ];
    }

    private function getBuySellToolkits(): array
    {
        return [
            [
                'category_slug' => 'buy-sell',
                'tool_type' => 'template',
                'name' => 'Item Listing Optimizer',
                'description' => 'Structured template for listing items that sell faster on the marketplace.',
                'content' => null,
                'items' => [
                    'structure' => [
                        'Item Name — Brand/Model',
                        'Condition (New/Like New/Good/Fair)',
                        'Original Price',
                        'Asking Price',
                        'What\'s Included',
                        'Reason for Selling',
                        'Pickup/Shipping Details',
                    ],
                    'headline_formulas' => [
                        '[Brand] [Item] — Like New, [X]% Off RRP',
                        'Must Sell: [Item] — [Condition]',
                        'Bargain: [Item] for [Price] — Worth [RRP]',
                    ],
                    'description_formula' => 'What it is → Condition details → Why selling → What\'s included → Price justification → Urgency',
                ],
                'sort_order' => 80,
            ],
            [
                'category_slug' => 'buy-sell',
                'tool_type' => 'checklist',
                'name' => 'Photo Checklist by Category',
                'description' => 'Category-specific photography guides for maximum buyer confidence.',
                'content' => null,
                'items' => [
                    'electronics' => ['Front view', 'Back view', 'Screen on', 'Ports', 'Accessories included', 'Any damage close-up'],
                    'fashion' => ['Front', 'Back', 'Label/tag', 'Size details', 'Wear details', 'Styled/on hanger'],
                    'furniture' => ['All angles', 'Dimensions written', 'Any flaws close-up', 'In-room context', 'Delivery considerations'],
                    'general' => ['Natural light', 'Plain background', 'Multiple angles', 'Scale reference', 'Condition honesty'],
                ],
                'sort_order' => 81,
            ],
            [
                'category_slug' => 'buy-sell',
                'tool_type' => 'guide',
                'name' => 'Condition Grading Guide',
                'description' => 'Standardized condition grades to build buyer trust and reduce returns.',
                'content' => null,
                'items' => [
                    'new' => 'Sealed, unopened, with original tags/packaging',
                    'like_new' => 'Opened but unused, no signs of wear, all accessories',
                    'good' => 'Minor signs of use, fully functional, clean',
                    'fair' => 'Visible wear but fully functional, may have minor cosmetic issues',
                    'acceptsable' => 'Significant wear, may have functional issues clearly described',
                ],
                'sort_order' => 82,
            ],
            [
                'category_slug' => 'buy-sell',
                'tool_type' => 'calculator',
                'name' => 'Price Comparison Tool',
                'description' => 'Help sellers price competitively against similar items on the market.',
                'content' => null,
                'items' => [
                    'pricing_rules' => [
                        'New items: 70-85% of RRP',
                        'Like new: 50-70% of RRP',
                        'Good condition: 30-50% of RRP',
                        'Fair condition: 15-30% of RRP',
                    ],
                    'competitive_factors' => [
                        'Current retail price (new)',
                        'Number of similar listings',
                        'Age of item',
                        'Completeness (all accessories, original box)',
                        'Seasonal demand',
                    ],
                    'bundle_discount_recommendation' => 'Offer 10-15% off for multiple item purchases',
                ],
                'sort_order' => 83,
            ],
        ];
    }

    private function getBusinessToolkits(): array
    {
        return [
            [
                'category_slug' => 'business',
                'tool_type' => 'template',
                'name' => 'Business Profile Builder',
                'description' => 'Complete business profile template for maximum visibility in the directory.',
                'content' => null,
                'items' => [
                    'sections' => [
                        'Business Name & Logo',
                        'Category & Sub-category',
                        'Description (what you do, who you serve)',
                        'Full Address with map',
                        'Opening Hours (including holidays)',
                        'Phone, Email, Website',
                        'Photos (logo, storefront, interior, team, products/services)',
                        'Menu or Service List',
                        'Special Offers',
                    ],
                    'photo_requirements' => [
                        'Logo (square, minimum 500x500px)',
                        'Storefront exterior',
                        'Interior (3-5 photos)',
                        'Team photo',
                        'Products or services (5-10 photos)',
                    ],
                ],
                'sort_order' => 90,
            ],
            [
                'category_slug' => 'business',
                'tool_type' => 'strategy',
                'name' => 'Local SEO Optimizer',
                'description' => 'Step-by-step guide to dominating local search results.',
                'content' => null,
                'items' => [
                    'google_business_profile' => [
                        'Complete 100% of profile',
                        'Choose primary and secondary categories',
                        'Add all services/products',
                        'Upload 100+ photos',
                        'Post weekly updates',
                        'Respond to all reviews within 24 hours',
                        'Enable messaging',
                        'Add booking link',
                    ],
                    'nap_consistency' => 'Name, Address, Phone must be identical across all platforms',
                    'review_strategy' => [
                        'Ask every happy customer for a review',
                        'Make it easy — direct link to Google review page',
                        'Respond to every review, positive and negative',
                        'Target: minimum 50 reviews with 4.5+ average',
                    ],
                    'local_keywords' => [
                        'Use "[service] in [location]" in descriptions',
                        'Add location-specific content regularly',
                        'List on local directories',
                    ],
                ],
                'sort_order' => 91,
            ],
            [
                'category_slug' => 'business',
                'tool_type' => 'template',
                'name' => 'Social Media Starter Pack',
                'description' => '30 pre-written social media posts for new businesses.',
                'content' => null,
                'items' => [
                    'post_types' => [
                        'Welcome post (introduce the business)',
                        'Behind-the-scenes (show the team/workspace)',
                        'Team introduction (individual spotlights)',
                        'Product/service spotlight',
                        'Customer testimonial',
                        'Local community engagement',
                        'Special offer/promotion',
                        'FAQ post',
                        'Industry tip/advice',
                        'Milestone celebration',
                    ],
                    'posting_schedule' => [
                        'Monday: Motivation or tip post',
                        'Tuesday: Product/service spotlight',
                        'Wednesday: Behind-the-scenes',
                        'Thursday: Customer testimonial',
                        'Friday: Fun or community post',
                        'Weekend: Special offer or event',
                    ],
                ],
                'sort_order' => 92,
            ],
            [
                'category_slug' => 'business',
                'tool_type' => 'resource',
                'name' => 'Restaurant Marketing Pack',
                'description' => 'Specialized marketing tools for restaurants, cafes, and food businesses.',
                'content' => null,
                'items' => [
                    'food_photography_tips' => [
                        'Shoot from 45-degree angle for plates',
                        'Natural light near window',
                        'Use props (cutlery, napkins, flowers)',
                        'Show texture and steam',
                        'Capture the full table spread',
                    ],
                    'menu_design_tips' => [
                        'Highlight high-margin items',
                        'Use descriptive adjectives',
                        'Limit menu items (less is more)',
                        'Update seasonally',
                        'Include dietary labels',
                    ],
                    'delivery_strategy' => [
                        'Optimize for delivery app search',
                        'Package food for travel (soggy chips = bad reviews)',
                        'Include a branded touch (sticker, card, sauce packet)',
                        'Encourage direct orders for higher margins',
                    ],
                ],
                'sort_order' => 93,
            ],
            [
                'category_slug' => 'business',
                'tool_type' => 'guide',
                'name' => 'Customer Review Generator',
                'description' => 'Automated system for generating and managing customer reviews.',
                'content' => null,
                'items' => [
                    'review_request_template' => 'Hi [Name], thank you for choosing [Business]. We\'d love to hear about your experience! Would you mind leaving us a quick review? [Direct link]',
                    'timing' => 'Send within 24 hours of service/purchase',
                    'platform_priority' => ['Google Business Profile', 'WWA listing', 'Facebook', 'Trustpilot'],
                    'handling_negative_reviews' => [
                        'Respond within 24 hours',
                        'Apologize sincerely',
                        'Explain what you\'ve done to fix it',
                        'Invite them back with a gesture of goodwill',
                        'Never delete negative reviews (unless abusive)',
                    ],
                ],
                'sort_order' => 94,
            ],
        ];
    }

    private function getFundingToolkits(): array
    {
        return [
            [
                'category_slug' => 'funding',
                'tool_type' => 'template',
                'name' => 'Campaign Page Builder',
                'description' => 'Structured template for compelling crowdfunding campaign pages.',
                'content' => null,
                'items' => [
                    'sections' => [
                        'Story — Why this matters (emotional hook)',
                        'Team — Credibility and trust',
                        'Funding Goal — How much and why',
                        'Budget Breakdown — Where every dollar goes',
                        'Timeline — Milestones and delivery dates',
                        'Tiered Rewards — Incentives for backers',
                        'FAQ — Common questions answered',
                        'Progress Updates — Regular communication',
                    ],
                    'reward_tiers' => [
                        '$5 — Thank you + digital reward',
                        '$25 — Early bird product',
                        '$50 — Product + extras',
                        '$100 — Premium bundle',
                        '$250 — Limited edition',
                        '$500+ — VIP / backer wall',
                    ],
                ],
                'sort_order' => 100,
            ],
            [
                'category_slug' => 'funding',
                'tool_type' => 'template',
                'name' => 'Video Pitch Script',
                'description' => '2-3 minute video pitch template for crowdfunding campaigns.',
                'content' => null,
                'items' => [
                    'script' => [
                        'HOOK (10s) — Attention-grabbing opening statement',
                        'PROBLEM (15s) — What pain point exists?',
                        'SOLUTION (20s) — How does your product solve it?',
                        'DEMO (30s) — Show the product in action',
                        'TEAM (15s) — Who are you and why are you qualified?',
                        'TRACTION (15s) — What progress have you made?',
                        'ASK (10s) — What do you need and why?',
                        'CTA (5s) — "Back us now and be part of this journey"',
                    ],
                    'production_tips' => [
                        'Keep under 3 minutes',
                        'Show the product, don\'t just talk about it',
                        'Include real people using the product',
                        'Professional audio is more important than video quality',
                        'End with clear call to action',
                    ],
                ],
                'sort_order' => 101,
            ],
            [
                'category_slug' => 'funding',
                'tool_type' => 'guide',
                'name' => 'Investor Pitch Deck Template',
                'description' => '10-slide pitch deck format for business investment seekers.',
                'content' => null,
                'items' => [
                    'slides' => [
                        '1. Problem — What pain point exists?',
                        '2. Solution — How do you solve it?',
                        '3. Market Size — How big is the opportunity?',
                        '4. Business Model — How do you make money?',
                        '5. Traction — What progress have you made?',
                        '6. Competition — Why you win?',
                        '7. Team — Why you can execute?',
                        '8. Financials — Projections and unit economics',
                        '9. The Ask — How much and what for?',
                        '10. Contact — How to reach you',
                    ],
                    'design_tips' => [
                        'One idea per slide',
                        'Use visuals over text',
                        'Include key metrics prominently',
                        'Tell a story, don\'t dump data',
                        'Practice the 10-minute pitch',
                    ],
                ],
                'sort_order' => 102,
            ],
        ];
    }

    private function getDonationsToolkits(): array
    {
        return [
            [
                'category_slug' => 'donations',
                'tool_type' => 'template',
                'name' => 'Donation Page Optimizer',
                'description' => 'High-converting donation page template for charities and causes.',
                'content' => null,
                'items' => [
                    'sections' => [
                        'Cause Headline — Clear, emotional, specific',
                        'Impact Statement — What $X achieves',
                        'Story/Need — Personal narrative',
                        'Photo/Video — Real beneficiaries (with consent)',
                        'Donation Tiers — Pre-set amounts with impact labels',
                        'Recurring Option — Monthly giving prominently placed',
                        'Social Proof — Donor count, progress bar',
                        'Transparency — How funds are used',
                    ],
                    'impact_calculator' => [
                        '$25 — Provides school supplies for one child for a term',
                        '$50 — Feeds a family for one month',
                        '$100 — Covers medical treatment for one patient',
                        '$250 — Sponsors a student for one year',
                        '$500 — Provides emergency shelter for 10 families',
                    ],
                    'pre_set_amounts' => ['$25', '$50', '$100', '$250', 'Custom'],
                ],
                'sort_order' => 110,
            ],
            [
                'category_slug' => 'donations',
                'tool_type' => 'template',
                'name' => 'Storytelling Template',
                'description' => 'Emotional storytelling framework for charity campaigns.',
                'content' => null,
                'items' => [
                    'structure' => [
                        'Meet [person] — Introduce a real beneficiary',
                        'Their challenge — What difficulty do they face?',
                        'How your organization helps — What do you do?',
                        'The difference made — What changed?',
                        'How to help — What can the donor do?',
                        'Donate now — Clear call to action',
                    ],
                    'writing_tips' => [
                        'Lead with a person, not statistics',
                        'Show vulnerability and authenticity',
                        'Use specific, tangible outcomes',
                        'Include a photo or video',
                        'End with hope, not despair',
                    ],
                ],
                'sort_order' => 111,
            ],
            [
                'category_slug' => 'donations',
                'tool_type' => 'strategy',
                'name' => 'Donor Retention Email Sequence',
                'description' => 'Email templates to keep donors engaged and encourage repeat giving.',
                'content' => null,
                'items' => [
                    'welcome_series' => [
                        'Email 1: Thank you + what your donation achieves',
                        'Email 2: Story of a beneficiary',
                        'Email 3: Impact report with statistics',
                        'Email 4: Volunteer/partner opportunity',
                    ],
                    'ongoing_communication' => [
                        'Monthly impact update',
                        'Annual report summary',
                        'Birthday/anniversary email',
                        'Year-end appeal',
                        'Major gift cultivation',
                    ],
                    'thank_you_template' => 'Dear [Name], your generous donation of [Amount] is making a real difference. Here\'s exactly what your gift will achieve: [Specific impact]. Thank you for being part of our mission.',
                ],
                'sort_order' => 112,
            ],
        ];
    }

    private function getAdvertsToolkits(): array
    {
        return [
            [
                'category_slug' => 'adverts',
                'tool_type' => 'guide',
                'name' => 'Promotion Level Comparison',
                'description' => 'Visual comparison of all advertising tiers to help sellers choose the right promotion.',
                'content' => null,
                'items' => [
                    'tiers' => [
                        'Free' => ['price' => '$0', 'features' => ['Standard listing', 'Basic visibility', '3-day duration']],
                        'Paid' => ['price' => '$10', 'features' => ['Priority placement', '7-day duration', 'Highlighted in search']],
                        'Promoted' => ['price' => '$20', 'features' => ['Highlighted card', '7-day duration', 'Category boost', '+150% views']],
                        'Featured' => ['price' => '$30', 'features' => ['Top of category', '7-day duration', 'Homepage carousel', '+200% views']],
                        'Sponsored' => ['price' => '$40', 'features' => ['Homepage placement', '7-day duration', 'Email newsletter', '+300% views']],
                    ],
                    'roi_data' => [
                        'Promoted listings get 150% more views on average',
                        'Featured listings sell 25% faster',
                        'Sponsored listings receive 3x more inquiries',
                    ],
                ],
                'sort_order' => 120,
            ],
            [
                'category_slug' => 'adverts',
                'tool_type' => 'calculator',
                'name' => 'Promotion ROI Calculator',
                'description' => 'Calculate the return on investment for promoting your listing.',
                'content' => null,
                'items' => [
                    'inputs' => ['listing_value', 'category', 'promotion_cost', 'expected_conversion_rate'],
                    'formula' => 'ROI = ((Additional Inquiries × Conversion Rate × Listing Value) - Promotion Cost) / Promotion Cost × 100',
                    'category_benchmarks' => [
                        'Property: +180% views when promoted',
                        'Vehicles: +150% views when promoted',
                        'Jobs: +200% views when promoted',
                        'Services: +120% views when promoted',
                        'Buy & Sell: +100% views when promoted',
                    ],
                ],
                'sort_order' => 121,
            ],
            [
                'category_slug' => 'adverts',
                'tool_type' => 'strategy',
                'name' => 'Banner Ad Design Guide',
                'description' => 'Best practices for creating effective banner advertisements.',
                'content' => null,
                'items' => [
                    'standard_sizes' => [
                        'Leaderboard: 728×90',
                        'Medium Rectangle: 300×250',
                        'Skyscraper: 160×600',
                        'Mobile Banner: 320×50',
                    ],
                    'design_rules' => [
                        'Text-to-image ratio: 80/20',
                        'Keep file size under 150KB',
                        'Use high-contrast CTA buttons',
                        'Include brand logo',
                        'Design mobile-first',
                        'Refresh creative every 2-3 weeks',
                    ],
                    'headline_formulas' => [
                        'Save [X]% on [Category]',
                        'Limited Time: [Offer]',
                        'Join [X] Happy Customers',
                        'Get a Free Quote Today',
                    ],
                ],
                'sort_order' => 122,
            ],
        ];
    }
}
