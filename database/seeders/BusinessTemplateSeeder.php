<?php

namespace Database\Seeders;

use App\Models\BusinessTemplate;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Seeds platform catalog packs for every marketplace vertical.
 * Aligned with frontend categoryTemplates.js + buy/download HTML in public/templates.
 */
class BusinessTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $catalog = $this->catalog();

        foreach ($catalog as $row) {
            $slug = Str::slug("{$row['vertical']}-{$row['category_slug']}-{$row['title']}");

            BusinessTemplate::updateOrCreate(
                ['slug' => $slug],
                array_merge($row, [
                    'slug' => $slug,
                    'user_id' => null,
                    'is_catalog' => true,
                    'status' => 'active',
                    'currency' => 'USD',
                ])
            );
        }

        $this->command?->info('Business templates seeded: '.count($catalog).' packs');
    }

    protected function catalog(): array
    {
        $rows = [];

        $add = function (
            string $vertical,
            string $category,
            string $headline,
            string $sectionDescription,
            array $items
        ) use (&$rows) {
            foreach ($items as $i => $item) {
                $rows[] = [
                    'vertical' => $vertical,
                    'category_slug' => $category,
                    'headline' => $headline,
                    'section_description' => $sectionDescription,
                    'title' => $item[0],
                    'blurb' => $item[1],
                    'price_label' => $item[2],
                    'price' => $this->parsePrice($item[2]),
                    'template_type' => $item[3] ?? 'business_doc',
                    'sort_order' => $i + 1,
                    'description' => $item[1],
                    'file_url' => $this->fileForTitle($item[0]),
                ];
            }
        };

        // —— Buy & Sell (legal + trader) ——
        $add('buy-sell', 'default', 'Buy & Sell templates for sale',
            'Sale agreements, bills of sale, listing packs and trader documents — buy from Worldwide Adverts.',
            [
                ['Private sale agreement', 'Buyer/seller details, item description, price, payment and handover terms', 'From $18', 'agreement'],
                ['Bill of sale', 'Legal transfer receipt for goods — serial numbers, warranty disclaimer, signatures', 'From $12', 'agreement'],
                ['Item listing description pack', 'Title formulas, feature bullets, condition grades and SEO listing copy', 'From $9', 'listing'],
                ['Purchase invoice / receipt', 'Professional invoice for marketplace sales with tax lines', 'From $8', 'invoice'],
                ['Escrow & handover checklist', 'Payment confirmation, inspection, delivery and dispute steps', 'From $10', 'checklist'],
                ['Reseller / trading plan', 'Sourcing, margins, channels and cash flow', 'From $24', 'business_plan'],
                ['Marketplace seller pitch', 'Niche, inventory model and growth ask', 'From $22', 'pitch_deck'],
                ['Micro-business grant pack', 'Need, equipment budget and impact', 'From $28', 'grant'],
            ]);

        foreach ([
            'electronics' => ['Electronics seller templates', 'Plans and pitches for gadget traders.',
                [['Electronics retail plan', 'SKU mix, warranty and turnover model', 'From $26', 'business_plan'],
                 ['Repair shop business plan', 'Jobs mix, parts and utilisation', 'From $28', 'business_plan'],
                 ['Bill of sale', 'Transfer receipt for devices with serial numbers', 'From $12', 'agreement']]],
            'vehicles' => ['Vehicle trader templates', 'Dealer and private-trader packs.',
                [['Used-car dealer plan', 'Stock turns, finance and overheads', 'From $32', 'business_plan'],
                 ['Private sale agreement', 'Vehicle private-party sale contract', 'From $18', 'agreement'],
                 ['Floorplan / stock loan pack', 'Inventory schedule and repayment', 'From $30', 'grant']]],
            'fashion' => ['Fashion seller templates', 'Boutique and brand launch packs.',
                [['Fashion brand business plan', 'Collection, channels and unit economics', 'From $30', 'business_plan'],
                 ['Boutique pitch deck', 'Positioning, margins and funding ask', 'From $26', 'pitch_deck'],
                 ['Item listing description pack', 'Product copy formulas for apparel listings', 'From $9', 'listing']]],
            'home-garden' => ['Home & garden seller templates', 'Furniture and DIY trading packs.',
                [['Home goods business plan', 'Categories, suppliers and margins', 'From $26', 'business_plan'],
                 ['Purchase invoice / receipt', 'Sale invoice for furniture / DIY', 'From $8', 'invoice'],
                 ['Escrow & handover checklist', 'Delivery and inspection checklist', 'From $10', 'checklist']]],
        ] as $slug => [$headline, $desc, $items]) {
            $add('buy-sell', $slug, $headline, $desc, $items);
        }

        // —— Business ——
        $add('business', 'default', 'Business templates for sale',
            'Pitch decks, grant applications, business plans and proposals — ready to customise.',
            [
                ['Investor pitch deck', 'Problem, solution, market, traction, team and funding ask', 'From $29', 'pitch_deck'],
                ['Grant application pack', 'Need statement, objectives, methods, budget and impact', 'From $35', 'grant'],
                ['Startup business plan', 'Market analysis, model, GTM, financials and team', 'From $39', 'business_plan'],
            ]);

        foreach ([
            'retail' => ['Retail business templates', 'Shops and multi-brand stores.', [
                ['Retail business plan', 'Location, assortment, margins and staffing', 'From $32', 'business_plan'],
                ['Franchise / expansion pitch', 'Unit economics and rollout slides', 'From $28', 'pitch_deck'],
                ['Supplier proposal pack', 'Terms, MOQs and partnership one-pager', 'From $18', 'proposal'],
            ]],
            'restaurants' => ['Restaurant & food templates', 'Cafés and food brands.', [
                ['Restaurant business plan', 'Concept, covers, food cost and break-even', 'From $34', 'business_plan'],
                ['Hospitality pitch deck', 'Concept, menu highlights and funding ask', 'From $27', 'pitch_deck'],
                ['Catering grant / loan pack', 'Equipment ask and cash flow', 'From $30', 'grant'],
            ]],
            'services' => ['Professional services templates', 'Agencies and practices.', [
                ['Client pitch deck', 'Problem, approach, case studies and pricing', 'From $26', 'pitch_deck'],
                ['Retainer proposal + SOW', 'Scope, deliverables, fees and SLAs', 'From $24', 'proposal'],
                ['Practice business plan', 'Services mix and 3-year forecast', 'From $36', 'business_plan'],
            ]],
            'healthcare' => ['Healthcare & wellness templates', 'Clinics and health providers.', [
                ['Clinic business plan', 'Services, compliance and projections', 'From $38', 'business_plan'],
                ['Health grant proposal', 'Community need, outcomes and budget', 'From $36', 'grant'],
                ['Investor / partner pitch', 'Model, catchment and growth roadmap', 'From $30', 'pitch_deck'],
            ]],
            'education' => ['Education & training templates', 'Course and school funding packs.', [
                ['Training programme plan', 'Curriculum, outcomes and pricing', 'From $28', 'business_plan'],
                ['Education grant application', 'Need, learners, methods and budget', 'From $34', 'grant'],
                ['Academy pitch deck', 'Market and enrolment forecast', 'From $26', 'pitch_deck'],
            ]],
            'technology' => ['Technology business templates', 'IT and tech startups.', [
                ['Tech startup pitch deck', 'Product, Moat, traction and ask', 'From $32', 'pitch_deck'],
                ['SaaS / IT business plan', 'Model, CAC/LTV and forecast', 'From $38', 'business_plan'],
                ['Innovation grant proposal', 'R&D scope and milestones', 'From $36', 'grant'],
            ]],
            'real-estate' => ['Real estate business templates', 'Agency and investment packs.', [
                ['Agency business plan', 'Areas, fee model and pipeline', 'From $34', 'business_plan'],
                ['Investment pitch deck', 'Deal thesis, comps and returns', 'From $32', 'pitch_deck'],
                ['Property management proposal', 'Services, fees and SLA', 'From $22', 'proposal'],
            ]],
            'non-profit' => ['Non-profit & charity templates', 'Mission and donor packs.', [
                ['Grant proposal pack', 'Need, methods, evaluation and budget', 'From $35', 'grant'],
                ['Donor pitch deck', 'Mission, programmes and ask', 'From $24', 'pitch_deck'],
                ['Charity strategic plan', 'Goals and 3-year funding map', 'From $32', 'business_plan'],
            ]],
        ] as $slug => [$headline, $desc, $items]) {
            $add('business', $slug, $headline, $desc, $items);
        }

        // —— Services ——
        $add('services', 'default', 'IT service business templates',
            'Pitch decks, proposals and grant packs for freelancers and agencies.',
            [
                ['Agency pitch deck', 'Capabilities, process, case studies and pricing', 'From $26', 'pitch_deck'],
                ['Client proposal + SOW', 'Scope, milestones, fees and acceptance criteria', 'From $22', 'proposal'],
                ['Freelance business plan', 'Offer mix, pipeline and 12-month forecast', 'From $28', 'business_plan'],
            ]);

        foreach ([
            'web-development' => ['Web development templates', 'Project pitches for web agencies.', [
                ['Website project proposal', 'Discovery, sitemap, build phases and quote', 'From $24', 'proposal'],
                ['Agency capability pitch', 'Stack, process and case-study slides', 'From $26', 'pitch_deck'],
                ['Retainer SOW pack', 'Hours, SLAs and billing', 'From $20', 'proposal'],
            ]],
            'app-software' => ['App & software templates', 'Product and SaaS packs.', [
                ['App / SaaS pitch deck', 'Problem, product, metrics and ask', 'From $32', 'pitch_deck'],
                ['Product requirements pack', 'PRD outline and MVP scope', 'From $28', 'proposal'],
                ['R&D / innovation grant', 'Technical approach and budget', 'From $36', 'grant'],
            ]],
            'digital-marketing' => ['Digital marketing templates', 'Campaign pitches and growth plans.', [
                ['Marketing pitch deck', 'Goals, channels and projected ROI', 'From $26', 'pitch_deck'],
                ['Campaign proposal pack', 'Audience, budget and KPIs', 'From $22', 'proposal'],
                ['Growth plan template', 'Funnel and monthly forecast', 'From $28', 'business_plan'],
            ]],
            'graphic-design' => ['Graphic design templates', 'Creative pitch packs.', [
                ['Brand project proposal', 'Discovery, deliverables and fees', 'From $22', 'proposal'],
                ['Studio pitch deck', 'Style, process and selected work', 'From $24', 'pitch_deck'],
                ['Creative brief template', 'Goals, audience and success metrics', 'From $12', 'proposal'],
            ]],
            'it-consultancy' => ['IT consultancy templates', 'Advisory and audit packs.', [
                ['Consulting pitch deck', 'Problems solved and case studies', 'From $28', 'pitch_deck'],
                ['IT audit + roadmap pack', 'Findings, priorities and SOW', 'From $32', 'proposal'],
                ['Digital transformation plan', 'Current state and investment ask', 'From $36', 'business_plan'],
            ]],
            'writing-content' => ['Writing & content templates', 'Editorial and content strategy.', [
                ['Content strategy proposal', 'Pillars, calendar, SEO and fees', 'From $20', 'proposal'],
                ['Editorial pitch deck', 'Audience, formats and sample work', 'From $18', 'pitch_deck'],
                ['Ghostwriting SOW pack', 'Outline, drafts, revisions and rights', 'From $16', 'proposal'],
            ]],
        ] as $slug => [$headline, $desc, $items]) {
            $add('services', $slug, $headline, $desc, $items);
        }

        // —— Property ——
        $add('property', 'default', 'Property templates for sale',
            'Agency plans, investment decks and landlord packs — ready to customise.',
            [
                ['Agency business plan', 'Areas, fee model, pipeline and team for estate agencies', 'From $34', 'business_plan'],
                ['Investment pitch deck', 'Deal thesis, comps, returns and risks', 'From $32', 'pitch_deck'],
                ['Property management proposal', 'Services, fees and SLA for landlords', 'From $22', 'proposal'],
            ]);

        foreach ([
            'residential' => ['Residential property templates', 'Home sale and letting packs.', [
                ['Residential listing prospectus', 'Property summary, photos plan and comps', 'From $28', 'business_plan'],
                ['Buyer presentation deck', 'Features, neighbourhood and next steps', 'From $24', 'pitch_deck'],
                ['Tenancy / lease proposal', 'Terms, deposit and inventory notes', 'From $18', 'proposal'],
            ]],
            'commercial' => ['Commercial property templates', 'Office and retail investment packs.', [
                ['Commercial investment pitch', 'Yield, tenants and capex plan', 'From $36', 'pitch_deck'],
                ['Lease proposal pack', 'Heads of terms and service charges', 'From $26', 'proposal'],
                ['Commercial business plan', 'Occupancy, income and forecast', 'From $34', 'business_plan'],
            ]],
            'land' => ['Land & development templates', 'Plot and planning packs.', [
                ['Land investment pitch', 'Zoning, comps and development thesis', 'From $32', 'pitch_deck'],
                ['Planning / grant pack', 'Need, design and budget', 'From $30', 'grant'],
                ['Development business plan', 'Phasing, costs and returns', 'From $38', 'business_plan'],
            ]],
            'investment' => ['Investment property templates', 'Portfolio and ROI packs.', [
                ['Portfolio pitch deck', 'Assets, yields and growth ask', 'From $34', 'pitch_deck'],
                ['Acquisition loan pack', 'Use of funds and repayment', 'From $36', 'grant'],
                ['Investment business plan', 'Strategy, cash flow and risks', 'From $38', 'business_plan'],
            ]],
            'rental' => ['Rental / landlord templates', 'Short and long-let ops packs.', [
                ['Landlord ops proposal', 'Services, fees and guest/tenant SLA', 'From $20', 'proposal'],
                ['Rental business plan', 'ADR, occupancy and opex', 'From $30', 'business_plan'],
                ['Hospitality pitch deck', 'Concept and growth ask', 'From $26', 'pitch_deck'],
            ]],
        ] as $slug => [$headline, $desc, $items]) {
            $add('property', $slug, $headline, $desc, $items);
        }

        // —— Vehicles ——
        $add('vehicles', 'default', 'Vehicle business templates',
            'Dealer, fleet and hire business plans and pitches.',
            [
                ['Dealership business plan', 'Stock, finance and overhead model', 'From $34', 'business_plan'],
                ['Fleet / hire pitch deck', 'Utilisation, contracts and growth ask', 'From $28', 'pitch_deck'],
                ['Transport grant pack', 'Vehicle schedule, jobs and budget', 'From $30', 'grant'],
            ]);

        foreach ([
            'car' => ['Car dealer templates', 'Used and new-car packs.', [
                ['Car dealer business plan', 'Turns, GP and showroom costs', 'From $32', 'business_plan'],
                ['Stock finance pitch', 'Floorplan ask and repayment', 'From $28', 'pitch_deck'],
                ['Private sale agreement', 'Private-party car sale contract', 'From $18', 'agreement'],
            ]],
            'motorbike' => ['Motorbike business templates', 'Bike retail and workshop packs.', [
                ['Bike shop business plan', 'Sales, service and accessory mix', 'From $28', 'business_plan'],
                ['Workshop pitch deck', 'Jobs, capacity and expansion ask', 'From $24', 'pitch_deck'],
                ['Bill of sale', 'Bike transfer receipt with VIN', 'From $12', 'agreement'],
            ]],
            'van' => ['Van & commercial templates', 'Courier and commercial packs.', [
                ['Courier / fleet plan', 'Routes, utilisation and opex', 'From $30', 'business_plan'],
                ['Van sales pitch deck', 'Stock mix and trade customers', 'From $24', 'pitch_deck'],
                ['Commercial vehicle grant', 'Fleet list and budget', 'From $28', 'grant'],
            ]],
            'truck' => ['Truck & haulage templates', 'Haulage funding packs.', [
                ['Haulage business plan', 'Contracts, fuel, drivers and margins', 'From $36', 'business_plan'],
                ['Fleet expansion pitch', 'Trucks, routes and financing ask', 'From $32', 'pitch_deck'],
                ['Logistics grant application', 'Jobs, efficiency and budget', 'From $34', 'grant'],
            ]],
        ] as $slug => [$headline, $desc, $items]) {
            $add('vehicles', $slug, $headline, $desc, $items);
        }

        // —— Books ——
        $add('books', 'default', 'Author & publishing templates',
            'Pitch decks, proposals and grant packs for writers and publishers.',
            [
                ['Book proposal pack', 'Synopsis, market, comps and sample chapters', 'From $22', 'proposal'],
                ['Author platform pitch', 'Audience, books and partnership ask', 'From $20', 'pitch_deck'],
                ['Publishing / arts grant', 'Project, audience reach and budget', 'From $28', 'grant'],
            ]);

        foreach ([
            'fiction' => ['Fiction author templates', 'Novel proposals and series pitches.', [
                ['Novel submission pack', 'Query, synopsis and comps list', 'From $18', 'proposal'],
                ['Series pitch deck', 'World, arcs and reader market', 'From $20', 'pitch_deck'],
                ['Literary grant application', 'Project plan and budget', 'From $26', 'grant'],
            ]],
            'non-fiction' => ['Non-fiction author templates', 'Proposal and platform packs.', [
                ['Non-fiction book proposal', 'Thesis, outline, audience and comps', 'From $22', 'proposal'],
                ['Thought-leadership pitch', 'Platform, topics and partnership ask', 'From $20', 'pitch_deck'],
                ['Research / arts grant pack', 'Methods and budget', 'From $28', 'grant'],
            ]],
            'business' => ['Business book templates', 'Author and publisher packs.', [
                ['Business book proposal', 'Hook, outline and market', 'From $24', 'proposal'],
                ['Author media kit pitch', 'Bio, books and speaking ask', 'From $18', 'pitch_deck'],
                ['Publishing grant pack', 'Project and budget', 'From $26', 'grant'],
            ]],
            'children' => ["Children's book templates", 'Picture book and YA packs.', [
                ['Children book proposal', 'Age range, synopsis and art notes', 'From $20', 'proposal'],
                ['Series pitch deck', 'Characters, arcs and market', 'From $18', 'pitch_deck'],
                ['Arts / literacy grant', 'Outreach and budget', 'From $24', 'grant'],
            ]],
        ] as $slug => [$headline, $desc, $items]) {
            $add('books', $slug, $headline, $desc, $items);
        }

        // —— Businesses for sale ——
        $add('businesses-for-sale', 'default', 'Business sale templates',
            'Prospectus packs and acquisition docs for buying or selling a business.',
            [
                ['Sale prospectus pack', 'Summary, financials, assets and reason for sale', 'From $45', 'business_plan'],
                ['Buyer / investor pitch deck', 'Opportunity, returns and handover plan', 'From $36', 'pitch_deck'],
                ['Acquisition loan / grant pack', 'Ask, use of funds and repayment', 'From $38', 'grant'],
            ]);

        foreach ([
            'restaurants' => ['Restaurant sale templates', 'Food business sale packs.', [
                ['Restaurant prospectus', 'Covers, lease, kitchen and P&L', 'From $42', 'business_plan'],
                ['Hospitality pitch deck', 'Concept, margins and growth', 'From $32', 'pitch_deck'],
                ['Fit-out / acquisition loan', 'Use of funds and cash flow', 'From $34', 'grant'],
            ]],
            'websites' => ['Website business sale templates', 'Site exit packs.', [
                ['Website teaser deck', 'Traffic, niche, monetisation and ask', 'From $32', 'pitch_deck'],
                ['Content site prospectus', 'Analytics, content assets and ops', 'From $36', 'business_plan'],
                ['Acquisition finance pack', 'Valuation and funds use', 'From $34', 'grant'],
            ]],
            'shops' => ['Shop / retail sale templates', 'Brick-and-mortar exit packs.', [
                ['Retail sale prospectus', 'Lease, stock, staff and P&L', 'From $40', 'business_plan'],
                ['Buyer pitch deck', 'Location, margins and growth', 'From $30', 'pitch_deck'],
                ['Acquisition loan pack', 'Ask and repayment', 'From $34', 'grant'],
            ]],
            'online-stores' => ['Online store sale templates', 'eCom exit packs.', [
                ['eCom teaser deck', 'Traffic, AOV, SKUs and ask', 'From $34', 'pitch_deck'],
                ['Store prospectus', 'Channels, margins and ops', 'From $38', 'business_plan'],
                ['Acquisition finance pack', 'Valuation and funds use', 'From $36', 'grant'],
            ]],
        ] as $slug => [$headline, $desc, $items]) {
            $add('businesses-for-sale', $slug, $headline, $desc, $items);
        }

        return $rows;
    }

    protected function parsePrice(string $label): float
    {
        if (preg_match('/(\d+(?:\.\d+)?)/', $label, $m)) {
            return (float) $m[1];
        }

        return 0;
    }

    protected function fileForTitle(string $title): string
    {
        return $this->templatePathForTitle($title);
    }

    protected function templatePathForTitle(string $title): string
    {
        $t = strtolower($title);

        if (str_contains($t, 'bill of sale')) {
            return '/templates/bill-of-sale.html';
        }
        if (str_contains($t, 'private sale') || str_contains($t, 'sale agreement')) {
            return '/templates/private-sale-agreement.html';
        }
        if (str_contains($t, 'listing description') || str_contains($t, 'item listing')) {
            return '/templates/item-listing-description.html';
        }
        if (str_contains($t, 'invoice') || str_contains($t, 'receipt')) {
            return '/templates/purchase-invoice-receipt.html';
        }
        if (str_contains($t, 'escrow') || str_contains($t, 'handover')) {
            return '/templates/escrow-handover-checklist.html';
        }
        if (str_contains($t, 'grant') || str_contains($t, 'loan')) {
            return '/templates/grant-application-pack.html';
        }
        if (str_contains($t, 'prospectus') || str_contains($t, 'teaser')) {
            return '/templates/sale-prospectus.html';
        }
        if (str_contains($t, 'saas') || str_contains($t, 'app /')) {
            return '/templates/saas-pitch-deck.html';
        }
        if (str_contains($t, 'audit') || str_contains($t, 'roadmap')) {
            return '/templates/it-audit-roadmap.html';
        }
        if (str_contains($t, 'book')) {
            return '/templates/book-proposal.html';
        }
        if (str_contains($t, 'marketing') || str_contains($t, 'campaign')) {
            return '/templates/marketing-campaign-proposal.html';
        }
        if (str_contains($t, 'website') || str_contains($t, 'web ')) {
            return '/templates/website-project-proposal.html';
        }
        if (str_contains($t, 'restaurant') || str_contains($t, 'catering') || str_contains($t, 'hospitality')) {
            return '/templates/restaurant-business-plan.html';
        }
        if (str_contains($t, 'agency') || str_contains($t, 'capability')) {
            return '/templates/agency-pitch-deck.html';
        }
        if (str_contains($t, 'proposal') || str_contains($t, 'sow') || str_contains($t, 'brief')) {
            return '/templates/client-proposal-sow.html';
        }
        if (str_contains($t, 'pitch') || str_contains($t, 'investor') || str_contains($t, 'donor')) {
            return '/templates/investor-pitch-deck.html';
        }

        return '/templates/startup-business-plan.html';
    }
}
