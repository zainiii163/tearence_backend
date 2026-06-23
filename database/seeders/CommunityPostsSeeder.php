<?php

namespace Database\Seeders;

use App\Models\CommunityPost;
use App\Models\Community;
use App\Models\User;
use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CommunityPostsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::take(5)->get();
        $communities = Community::take(5)->get();
        $categories = Category::take(5)->get();

        if ($users->isEmpty() || $communities->isEmpty()) {
            $this->command->warn('No users or communities found. Please seed users and communities first.');
            return;
        }

        $samplePosts = [
            [
                'post_type' => 'discussion_thread',
                'title' => 'Best practices for first-time home buyers in the UK',
                'content' => 'I\'m looking to buy my first property in the UK. What are the key things I should know about the process? Any tips on mortgages, surveys, or negotiating?',
                'discussion_type' => 'question',
                'tags' => ['first-time-buyer', 'mortgage', 'uk-property', 'home-buying'],
            ],
            [
                'post_type' => 'discussion_thread',
                'title' => 'Startup funding trends for 2024',
                'content' => 'What are the current trends in startup funding? Are VCs more cautious now? What sectors are getting the most attention?',
                'discussion_type' => 'general',
                'tags' => ['startup', 'funding', 'vc', 'investment', 'trends'],
            ],
            [
                'post_type' => 'discussion_thread',
                'title' => 'Effective charity fundraising strategies',
                'content' => 'Share your experiences with successful fundraising campaigns. What worked best for your organization?',
                'discussion_type' => 'advice',
                'tags' => ['fundraising', 'charity', 'nonprofit', 'donations'],
            ],
            [
                'post_type' => 'discussion_thread',
                'title' => 'Upcoming music festivals in London this summer',
                'content' => 'What music festivals are happening in London this summer? Looking for recommendations across different genres.',
                'discussion_type' => 'question',
                'tags' => ['music', 'festivals', 'london', 'summer', 'events'],
            ],
            [
                'post_type' => 'discussion_thread',
                'title' => 'Electric vehicle adoption in Europe',
                'content' => 'How is the EV market developing across Europe? What are the biggest challenges and opportunities?',
                'discussion_type' => 'general',
                'tags' => ['electric-vehicles', 'ev', 'europe', 'automotive', 'sustainability'],
            ],
            [
                'post_type' => 'discussion_thread',
                'title' => 'Remote work productivity tips',
                'content' => 'What tools and strategies do you use to stay productive while working remotely? Share your best practices.',
                'discussion_type' => 'advice',
                'tags' => ['remote-work', 'productivity', 'work-from-home', 'tips'],
            ],
            [
                'post_type' => 'discussion_thread',
                'title' => 'Book recommendation: Must-read business books',
                'content' => 'Looking for recommendations on business books that have had a real impact on your career or business.',
                'discussion_type' => 'question',
                'tags' => ['books', 'business', 'recommendations', 'reading'],
            ],
            [
                'post_type' => 'discussion_thread',
                'title' => 'Hidden gems for travel in Southeast Asia',
                'content' => 'Share your favorite lesser-known destinations in Southeast Asia. Places that aren\'t typical tourist spots but are amazing to visit.',
                'discussion_type' => 'general',
                'tags' => ['travel', 'southeast-asia', 'hidden-gems', 'adventure'],
            ],
            [
                'post_type' => 'discussion_thread',
                'title' => 'Selling second-hand electronics safely',
                'content' => 'What are the best practices for selling used electronics online? How do you ensure safe transactions and accurate descriptions?',
                'discussion_type' => 'advice',
                'tags' => ['electronics', 'selling', 'safety', 'marketplace'],
            ],
            [
                'post_type' => 'discussion_thread',
                'title' => 'Job market outlook for tech professionals in 2024',
                'content' => 'How is the job market looking for tech professionals this year? Which skills are most in demand?',
                'discussion_type' => 'question',
                'tags' => ['jobs', 'tech', 'career', 'job-market', 'skills'],
            ],
        ];

        foreach ($samplePosts as $index => $postData) {
            $user = $users->random();
            $community = $communities->random();
            $category = $categories->random();

            $post = CommunityPost::create([
                'post_id' => Str::uuid(),
                'user_id' => $user->user_id,
                'post_type' => $postData['post_type'],
                'title' => $postData['title'],
                'content' => $postData['content'],
                'discussion_type' => $postData['discussion_type'],
                'tags' => $postData['tags'],
                'category_id' => $category->category_id,
                'location' => $community->city ?? $community->region ?? 'Global',
                'country' => $community->region === 'UK' ? 'United Kingdom' : ($community->region === 'EU' ? 'European Union' : null),
                'city' => $community->city,
                'views_count' => rand(10, 500),
                'comments_count' => rand(0, 50),
                'reactions_count' => rand(0, 100),
                'saves_count' => rand(0, 30),
                'shares_count' => rand(0, 20),
                'is_pinned' => $index < 2,
                'is_featured' => $index < 3,
            ]);

            // Attach to community
            $post->communities()->attach($community->community_id, [
                'is_primary' => true
            ]);

            // Increment community posts count
            $community->incrementPostsCount();
        }

        $this->command->info('Community posts seeded successfully.');
    }
}
