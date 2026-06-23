<?php

namespace Database\Seeders;

use App\Models\Comment;
use App\Models\CommunityPost;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CommentsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $posts = CommunityPost::take(10)->get();
        $users = User::take(5)->get();

        if ($posts->isEmpty() || $users->isEmpty()) {
            $this->command->warn('No posts or users found. Please seed posts and users first.');
            return;
        }

        $sampleComments = [
            [
                'content' => 'Great question! I\'d recommend starting with getting your finances in order and getting pre-approved for a mortgage before you start looking at properties.',
                'comment_type' => 'tip',
            ],
            [
                'content' => 'The market is definitely more cautious right now. VCs are looking for proven business models rather than just ideas.',
                'comment_type' => 'tip',
            ],
            [
                'content' => 'We had great success with peer-to-peer fundraising campaigns. Personal stories really resonate with donors.',
                'comment_type' => 'report_experience',
            ],
            [
                'content' => 'Check out Glastonbury and Wireless Festival. Both are amazing experiences!',
                'comment_type' => 'tip',
            ],
            [
                'content' => 'The infrastructure is still catching up in some areas. Charging stations are the main bottleneck.',
                'comment_type' => 'review',
            ],
            [
                'content' => 'Slack for communication, Notion for organization, and time-blocking techniques work really well for me.',
                'comment_type' => 'tip',
            ],
            [
                'content' => 'I highly recommend "The Lean Startup" by Eric Ries. It changed how I think about building businesses.',
                'comment_type' => 'review',
            ],
            [
                'content' => 'Luang Prabang in Laos is incredible. Very peaceful and beautiful, not overrun by tourists yet.',
                'comment_type' => 'tip',
            ],
            [
                'content' => 'Always meet in a safe public place for in-person transactions. For shipping, use tracked services.',
                'comment_type' => 'tip',
            ],
            [
                'content' => 'AI/ML skills are still very much in demand. Cloud computing expertise is also valuable.',
                'comment_type' => 'tip',
            ],
            [
                'content' => 'Is this still available? I\'m interested in learning more.',
                'comment_type' => 'question',
            ],
            [
                'content' => 'Thanks for sharing this! Very helpful information.',
                'comment_type' => 'general',
            ],
            [
                'content' => 'I disagree with some points here. In my experience, the market is actually quite active.',
                'comment_type' => 'general',
            ],
            [
                'content' => 'Can you elaborate on that point? I\'d like to understand better.',
                'comment_type' => 'question',
            ],
            [
                'content' => 'This worked really well for my organization too. We saw a 40% increase in donations.',
                'comment_type' => 'report_experience',
            ],
        ];

        foreach ($posts as $post) {
            $commentCount = rand(2, 5);
            
            for ($i = 0; $i < $commentCount; $i++) {
                $user = $users->random();
                $commentData = $sampleComments[array_rand($sampleComments)];

                $comment = Comment::create([
                    'comment_id' => Str::uuid(),
                    'post_id' => $post->post_id,
                    'user_id' => $user->user_id,
                    'content' => $commentData['content'],
                    'comment_type' => $commentData['comment_type'],
                    'reactions_count' => rand(0, 20),
                    'replies_count' => rand(0, 3),
                ]);

                // Add some replies
                if (rand(0, 1) && $i < $commentCount - 1) {
                    $replyUser = $users->where('user_id', '!=', $user->user_id)->random();
                    Comment::create([
                        'comment_id' => Str::uuid(),
                        'post_id' => $post->post_id,
                        'user_id' => $replyUser->user_id,
                        'parent_id' => $comment->comment_id,
                        'content' => 'Thanks for the helpful response!',
                        'comment_type' => 'general',
                        'reactions_count' => rand(0, 5),
                        'replies_count' => 0,
                    ]);

                    $comment->incrementReplies();
                }

                $post->incrementComments();
            }
        }

        $this->command->info('Comments seeded successfully.');
    }
}
