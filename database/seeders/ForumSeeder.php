<?php

namespace Database\Seeders;

use App\Models\ForumTopic;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ForumSeeder extends Seeder
{
    public function run(): void
    {
        $author = User::where('is_admin', true)->first() ?? User::first();

        if (!$author) {
            $this->command?->warn('ForumSeeder: no users found, skipping.');
            return;
        }

        $topics = [
            [
                'title'     => 'Karibu! Forum rules & how XP works',
                'category'  => 'general',
                'is_pinned' => true,
                'body'      => "Karibu to the Pesa Forums — the social heart of Pesa City! 🎉\n\n"
                    . "A few simple rules:\n"
                    . "1. Be kind. We are all here to learn about money together.\n"
                    . "2. No sharing personal details — phone numbers, emails or real M-Pesa numbers.\n"
                    . "3. Keep it honest. No get-rich-quick schemes or fake \"investment\" pitches.\n"
                    . "4. Help beginners. Everyone started somewhere.\n\n"
                    . "How XP works here:\n"
                    . "• Start a new discussion: +40 XP\n"
                    . "• Reply to a discussion: +25 XP\n"
                    . "• XP is earned on your first 5 posts each day — after that, post for the love of the community, not the points!\n\n"
                    . "Now say hi and tell us what you're saving for. 👋",
            ],
            [
                'title'    => "What's your biggest money lesson this year?",
                'category' => 'money-talk',
                'body'     => "Real talk — what has this year taught you about money?\n\n"
                    . "Mine: small consistent savings beat big irregular ones. I used to wait until I had \"enough\" to save, then I'd spend it. Putting away a little every week changed everything.\n\n"
                    . "Your turn. What lesson hit you hardest — a win, a loss, or a close call?",
            ],
            [
                'title'    => 'Side hustle ideas that actually work for students',
                'category' => 'side-hustles',
                'body'     => "Let's build a list of side hustles that genuinely work when you're still in school.\n\n"
                    . "Starter ideas:\n"
                    . "• Selling snacks or airtime to classmates\n"
                    . "• Tutoring younger students\n"
                    . "• Digital work — typing, design, social media for small businesses\n"
                    . "• Buying and reselling second-hand items\n\n"
                    . "What has worked for you? What flopped? Share the real numbers if you can — it helps everyone plan.",
            ],
            [
                'title'    => 'How do you balance school and saving money?',
                'category' => 'school',
                'body'     => "School Corner question: pocket money is small and school demands are many. 📚\n\n"
                    . "How do you manage to save anything at all? Do you budget your pocket money? Skip some spending? Have a target you're working towards?\n\n"
                    . "Drop your tricks below — even saving Ksh 20 a week counts. Consistency is the real flex. 💪",
            ],
        ];

        foreach ($topics as $i => $t) {
            $slugBase = Str::slug($t['title']);

            if (ForumTopic::where('slug', 'like', $slugBase . '-%')->exists()) {
                continue; // already seeded
            }

            ForumTopic::create([
                'user_id'          => $author->id,
                'title'            => $t['title'],
                'slug'             => $slugBase . '-' . Str::lower(Str::random(4)),
                'body'             => $t['body'],
                'category'         => $t['category'],
                'is_pinned'        => $t['is_pinned'] ?? false,
                'last_activity_at' => now()->subMinutes(count($topics) - $i),
            ]);
        }
    }
}
