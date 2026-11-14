<?php

namespace App\Console\Commands;

use App\Models\AuditLog;
use App\Models\Post;
use App\Models\User;
use App\Notifications\CommunityActivity;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Notification;

class CommunityPublish extends Command
{
    protected $signature = 'community:publish {slug : Post slug or id} {--no-notify : Do not alert members}';

    protected $description = 'Publish a draft post to the private community and alert members.';

    public function handle(): int
    {
        $key = $this->argument('slug');
        $post = Post::where('slug', $key)->orWhere('id', $key)->first();

        if (! $post) {
            $this->error('Post not found.');

            return self::FAILURE;
        }

        if ($post->status === 'published') {
            $this->warn('Already published.');

            return self::SUCCESS;
        }

        $post->update(['status' => 'published', 'published_at' => $post->published_at ?? now()]);
        AuditLog::record('post.published_cli', $post);

        if (! $this->option('no-notify')) {
            $members = User::where('status', 'approved')->where('id', '!=', $post->user_id)->get();
            Notification::send($members, new CommunityActivity("New in the community: {$post->title}", route('community.show', $post)));
            $this->line("Alerted {$members->count()} members.");
        }

        $this->info("Published: {$post->title}");

        return self::SUCCESS;
    }
}
