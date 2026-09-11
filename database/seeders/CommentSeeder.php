<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\Comment;
use App\Models\User;
use Illuminate\Database\Seeder;

class CommentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $articles = Article::published()->take(3)->get();
        $admin = User::where('email', 'admin@securofi.tech')->first() 
            ?? User::role('Super Admin')->first() 
            ?? User::first();

        if ($articles->isEmpty()) {
            return;
        }

        foreach ($articles as $article) {
            // Root comment 1 (Approved)
            $c1 = Comment::create([
                'article_id' => $article->id,
                'name' => 'Dr. Elena Rostova',
                'email' => 'e.rostova@infosec-labs.io',
                'website' => 'https://infosec-labs.io',
                'content' => "A rigorous architectural assessment. One additional factor to consider when implementing zero-trust key isolation is hardware-enforced rate limiting across HSM partition boundaries to preempt side-channel leaks.",
                'status' => 'approved',
                'created_at' => now()->subHours(8),
            ]);

            // Staff reply to root comment 1
            if ($admin) {
                Comment::create([
                    'article_id' => $article->id,
                    'parent_id' => $c1->id,
                    'user_id' => $admin->id,
                    'name' => 'SecuroFi Editorial Staff',
                    'email' => $admin->email,
                    'content' => "Excellent point, Dr. Rostova. We covered HSM partition isolation thresholds in our Q3 Cryptographic Standards review. We will link that supplementary whitepaper in our next revision.",
                    'status' => 'approved',
                    'is_admin_reply' => true,
                    'created_at' => now()->subHours(5),
                ]);
            }

            // Root comment 2 (Pending moderation)
            Comment::create([
                'article_id' => $article->id,
                'name' => 'Marcus Chen',
                'email' => 'mchen@cybersec-ops.org',
                'website' => 'https://cybersec-ops.org',
                'content' => "How do you recommend handling ephemeral identity federation when migrating legacy active directory forests into hybrid cloud architectures?",
                'status' => 'pending',
                'created_at' => now()->subMinutes(42),
            ]);
        }
    }
}
