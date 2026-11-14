<?php

namespace App\Console\Commands;

use App\Models\Comment;
use App\Models\Post;
use App\Models\Report;
use App\Models\User;
use Illuminate\Console\Command;

class CommunityDigest extends Command
{
    protected $signature = 'community:digest {--days=7}';

    protected $description = 'Print recent community activity (for Claude to summarise into themes).';

    public function handle(): int
    {
        $since = now()->subDays((int) $this->option('days'));

        $this->line("== Community digest: last {$this->option('days')} days ==");
        $this->line('New applications: '.User::where('created_at', '>=', $since)->count().' | pending now: '.User::pending()->count());
        $this->line('New posts: '.Post::where('published_at', '>=', $since)->count().' | New comments: '.Comment::where('created_at', '>=', $since)->count().' | Open reports: '.Report::where('status', 'open')->count());

        foreach (Post::published()->withCount(['comments' => fn ($q) => $q->where('created_at', '>=', $since)])
            ->orderByDesc('comments_count')->take(10)->get() as $post) {
            if ($post->comments_count === 0 && $post->published_at < $since) {
                continue;
            }
            $this->line("\n## {$post->title}  ({$post->comments_count} new comments)");
            foreach (Comment::with('author')->where('post_id', $post->id)->where('created_at', '>=', $since)->latest()->take(15)->get() as $c) {
                $this->line(' - '.$c->author->name.': '.str($c->body)->squish()->limit(240));
            }
        }

        return self::SUCCESS;
    }
}
