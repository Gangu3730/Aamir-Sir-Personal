<?php

namespace App\Console\Commands;

use App\Models\Post;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class CommunityPostDraft extends Command
{
    protected $signature = 'community:draft {title : Post title}
        {--body= : HTML/text body}
        {--file= : Read the body from a file instead}
        {--type=discussion : article|discussion|question}
        {--author= : Author email (defaults to the first admin)}';

    protected $description = 'Save a new community post as a DRAFT (never published automatically).';

    public function handle(): int
    {
        $body = $this->option('file') ? File::get($this->option('file')) : (string) $this->option('body');
        if (trim($body) === '') {
            $this->error('Provide --body or --file.');

            return self::FAILURE;
        }

        $author = $this->option('author')
            ? User::where('email', $this->option('author'))->firstOrFail()
            : User::where('role', User::ROLE_ADMIN)->orderBy('id')->firstOrFail();

        // Plain text gets paragraphs; HTML is kept as is.
        if ($body === strip_tags($body)) {
            $body = collect(preg_split("/\n{2,}/", trim($body)))->map(fn ($p) => '<p>'.nl2br(e(trim($p))).'</p>')->implode("\n");
        }

        $post = Post::create([
            'user_id' => $author->id,
            'type' => $this->option('type'),
            'title' => $this->argument('title'),
            'slug' => Post::uniqueSlug($this->argument('title')),
            'body' => $body,
            'status' => 'draft',
        ]);

        $this->info("Draft saved (id {$post->id}, slug {$post->slug}). Publish with: php artisan community:publish {$post->slug}");

        return self::SUCCESS;
    }
}
