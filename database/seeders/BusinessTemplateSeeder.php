<?php

namespace Database\Seeders;

use App\Models\BusinessTemplate;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Seeds platform catalog packs (pitch decks, grants, business plans)
 * aligned with frontend categoryTemplates.js.
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

        // —— Business ——
        $add('business', 'default', 'Business templates for sale',
            'Pitch decks, grant applications, business plans and proposals — ready to customise.',
            [
                ['Investor pitch deck', 'Problem, solution, market, traction, team and funding ask (10–15 slides)', 'From $29', 'pitch_deck'],
                ['Grant application pack', 'Need statement, objectives, methods, budget and impact sections', 'From $35', 'grant'],
                ['Startup business plan', 'Market analysis, model, GTM, financials and team for loans or partners', 'From $39', 'business_plan'],
            ]);

        $add('business', 'retail', 'Retail business templates',
            'Plans and pitch packs for shops, boutiques and multi-brand stores.',
            [
                ['Retail business plan', 'Location, assortment, margins and staffing model', 'From $32', 'business_plan'],
                ['Franchise / expansion pitch', 'Unit economics, territory and rollout slides', 'From $28', 'pitch_deck'],
                ['Supplier proposal pack', 'Terms, MOQs and brand partnership one-pager', 'From $18', 'proposal'],
            ]);

        $add('business', 'restaurants', 'Restaurant & food templates',
            'Funding and ops packs for cafés, restaurants and food brands.',
            [
                ['Restaurant business plan', 'Concept, covers, food cost and break-even', 'From $34', 'business_plan'],
                ['Hospitality pitch deck', 'Concept, menu highlights and funding ask', 'From $27', 'pitch_deck'],
                ['Catering grant / loan pack', 'Equipment ask, kitchen layout and cash flow', 'From $30', 'grant'],
            ]);

        $add('business', 'services', 'Professional services templates',
            'Client-winning decks and funding docs for agencies and practices.',
            [
                ['Client pitch deck', 'Problem, approach, case studies and pricing', 'From $26', 'pitch_deck'],
                ['Retainer proposal + SOW', 'Scope, deliverables, fees and SLAs', 'From $24', 'proposal'],
                ['Practice business plan', 'Services mix, pipeline and 3-year forecast', 'From $36', 'business_plan'],
            ]);

        $add('business', 'healthcare', 'Healthcare & wellness templates',
            'Clinic plans, funding asks and grant packs for health providers.',
            [
                ['Clinic business plan', 'Services, compliance, staffing and projections', 'From $38', 'business_plan'],
                ['Health grant proposal', 'Community need, outcomes, budget and evaluation', 'From $36', 'grant'],
                ['Investor / partner pitch', 'Model, catchment and growth roadmap', 'From $30', 'pitch_deck'],
            ]);

        $add('business', 'education', 'Education & training templates',
            'Course, school and training funding packs.',
            [
                ['Training programme plan', 'Curriculum, outcomes, pricing and delivery', 'From $28', 'business_plan'],
                ['Education grant application', 'Need, learners, methods, budget and impact', 'From $34', 'grant'],
                ['Academy pitch deck', 'Market, differentiation and enrolment forecast', 'From $26', 'pitch_deck'],
            ]);

        $add('business', 'non-profit', 'Non-profit & charity templates',
            'Mission plans, grants and donor pitch packs.',
            [
                ['Grant proposal pack', 'Need, methods, evaluation, budget and impact', 'From $35', 'grant'],
                ['Donor pitch deck', 'Mission, programmes, outcomes and ask', 'From $24', 'pitch_deck'],
                ['Charity strategic plan', 'Goals, programmes and 3-year funding map', 'From $32', 'business_plan'],
            ]);

        $add('business', 'technology', 'Technology business templates',
            'IT, electronics and tech startup funding packs.',
            [
                ['Tech startup pitch deck', 'Problem, product, Moat, traction and ask', 'From $32', 'pitch_deck'],
                ['SaaS / IT business plan', 'Model, CAC/LTV, roadmap and forecast', 'From $38', 'business_plan'],
                ['Innovation grant proposal', 'R&D scope, milestones and budget', 'From $36', 'grant'],
            ]);

        $add('business', 'real-estate', 'Real estate business templates',
            'Agency and property investment pitch packs.',
            [
                ['Agency business plan', 'Areas, fee model, pipeline and team', 'From $34', 'business_plan'],
                ['Investment pitch deck', 'Deal thesis, comps, returns and risks', 'From $32', 'pitch_deck'],
                ['Property management proposal', 'Services, fees and SLA for landlords', 'From $22', 'proposal'],
            ]);

        // —— Services (IT) ——
        $add('services', 'default', 'IT service business templates',
            'Pitch decks, proposals and grant packs for freelancers and agencies.',
            [
                ['Agency pitch deck', 'Capabilities, process, case studies and pricing', 'From $26', 'pitch_deck'],
                ['Client proposal + SOW', 'Scope, milestones, fees and acceptance criteria', 'From $22', 'proposal'],
                ['Freelance business plan', 'Offer mix, pipeline and 12-month forecast', 'From $28', 'business_plan'],
            ]);

        $add('services', 'web-development', 'Web development templates',
            'Project pitches and proposals for web agencies.',
            [
                ['Website project proposal', 'Discovery, sitemap, build phases and quote', 'From $24', 'proposal'],
                ['Agency capability pitch', 'Stack, process and case-study slides', 'From $26', 'pitch_deck'],
                ['Retainer SOW pack', 'Hours, SLAs, change control and billing', 'From $20', 'proposal'],
            ]);

        $add('services', 'app-software', 'App & software templates',
            'Product and SaaS funding packs.',
            [
                ['App / SaaS pitch deck', 'Problem, product, Moat, metrics and ask', 'From $32', 'pitch_deck'],
                ['Product requirements pack', 'PRD outline, roadmap and MVP scope', 'From $28', 'proposal'],
                ['R&D / innovation grant', 'Technical approach, milestones and budget', 'From $36', 'grant'],
            ]);

        $add('services', 'digital-marketing', 'Digital marketing templates',
            'Campaign pitches and growth plans for marketers.',
            [
                ['Marketing pitch deck', 'Goals, channels, plan and projected ROI', 'From $26', 'pitch_deck'],
                ['Campaign proposal pack', 'Audience, budget, creatives and KPIs', 'From $22', 'proposal'],
                ['Growth plan template', 'Funnel, experiments and monthly forecast', 'From $28', 'business_plan'],
            ]);

        $add('services', 'it-consultancy', 'IT consultancy templates',
            'Advisory pitches, audits and funding packs.',
            [
                ['Consulting pitch deck', 'Problems solved, method and case studies', 'From $28', 'pitch_deck'],
                ['IT audit + roadmap pack', 'Findings template, priorities and SOW', 'From $32', 'proposal'],
                ['Digital transformation plan', 'Current state, target and investment ask', 'From $36', 'business_plan'],
            ]);

        // —— Buy & Sell ——
        $add('buy-sell', 'default', 'Seller & trader business templates',
            'Pitch, plan and grant packs for sellers building a trading business.',
            [
                ['Reseller / trading plan', 'Sourcing, margins, channels and cash flow', 'From $24', 'business_plan'],
                ['Marketplace seller pitch', 'Niche, inventory model and growth ask', 'From $22', 'pitch_deck'],
                ['Micro-business grant pack', 'Need, equipment budget and impact', 'From $28', 'grant'],
            ]);

        $add('buy-sell', 'electronics', 'Electronics seller templates',
            'Plans and pitches for gadget and device traders.',
            [
                ['Electronics retail plan', 'SKU mix, warranty and turnover model', 'From $26', 'business_plan'],
                ['Repair shop business plan', 'Jobs mix, parts and utilisation', 'From $28', 'business_plan'],
                ['Equipment finance pitch', 'Bench tools ROI and loan request', 'From $22', 'pitch_deck'],
            ]);

        $add('buy-sell', 'vehicles', 'Vehicle trader templates',
            'Dealer and private-trader funding packs.',
            [
                ['Used-car dealer plan', 'Stock turns, finance and overheads', 'From $32', 'business_plan'],
                ['Dealer pitch deck', 'Sourcing, margins and expansion ask', 'From $28', 'pitch_deck'],
                ['Floorplan / stock loan pack', 'Inventory schedule and repayment', 'From $30', 'grant'],
            ]);

        // —— Vehicles ——
        $add('vehicles', 'default', 'Vehicle business templates',
            'Dealer, fleet and hire business plans and pitches.',
            [
                ['Dealership business plan', 'Stock, finance and overhead model', 'From $34', 'business_plan'],
                ['Fleet / hire pitch deck', 'Utilisation, contracts and growth ask', 'From $28', 'pitch_deck'],
                ['Transport grant pack', 'Vehicle schedule, jobs and budget', 'From $30', 'grant'],
            ]);

        // —— Books ——
        $add('books', 'default', 'Author & publishing templates',
            'Pitch decks, proposals and grant packs for writers and publishers.',
            [
                ['Book proposal pack', 'Synopsis, market, comps and sample chapters', 'From $22', 'proposal'],
                ['Author platform pitch', 'Audience, books and partnership ask', 'From $20', 'pitch_deck'],
                ['Publishing / arts grant', 'Project, audience reach and budget', 'From $28', 'grant'],
            ]);

        // —— Businesses for sale ——
        $add('businesses-for-sale', 'default', 'Business sale templates',
            'Pitch decks, prospectus packs and grant/loan docs for buying or selling a business.',
            [
                ['Sale prospectus pack', 'Summary, financials, assets and reason for sale', 'From $45', 'business_plan'],
                ['Buyer / investor pitch deck', 'Opportunity, returns and handover plan', 'From $36', 'pitch_deck'],
                ['Acquisition loan / grant pack', 'Ask, use of funds and repayment', 'From $38', 'grant'],
            ]);

        $add('businesses-for-sale', 'restaurants', 'Restaurant sale templates',
            'Food business sale and funding packs.',
            [
                ['Restaurant prospectus', 'Covers, lease, kitchen and P&L', 'From $42', 'business_plan'],
                ['Hospitality pitch deck', 'Concept, margins and growth', 'From $32', 'pitch_deck'],
                ['Fit-out / acquisition loan', 'Use of funds and cash flow', 'From $34', 'grant'],
            ]);

        $add('businesses-for-sale', 'websites', 'Website business sale templates',
            'Due-diligence and teaser packs for site exits.',
            [
                ['Website teaser deck', 'Traffic, niche, monetisation and ask', 'From $32', 'pitch_deck'],
                ['Content site prospectus', 'Analytics, content assets and ops', 'From $36', 'business_plan'],
                ['Acquisition finance pack', 'Valuation, funds use and plan', 'From $34', 'grant'],
            ]);

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
        $t = strtolower($title);
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
        if (str_contains($t, 'restaurant') || str_contains($t, 'catering')) {
            return '/templates/restaurant-business-plan.html';
        }
        if (str_contains($t, 'agency') || str_contains($t, 'capability')) {
            return '/templates/agency-pitch-deck.html';
        }
        if (str_contains($t, 'proposal') || str_contains($t, 'sow')) {
            return '/templates/client-proposal-sow.html';
        }
        if (str_contains($t, 'pitch') || str_contains($t, 'investor')) {
            return '/templates/investor-pitch-deck.html';
        }

        return '/templates/startup-business-plan.html';
    }
}
