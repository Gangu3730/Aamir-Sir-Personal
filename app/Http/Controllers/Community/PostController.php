<?php

namespace App\Http\Controllers\Community;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Comment;
use App\Models\Post;
use App\Models\Report;
use App\Models\Setting;
use App\Models\User;
use App\Notifications\CommunityActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;

class PostController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $sort = $request->query('sort', 'recent');
        $q = trim((string) $request->query('q', ''));

        $query = Post::published()->with('author')->withCount(['comments' => fn ($c) => $c->where('status', 'visible')]);

        if ($request->boolean('saved')) {
            $query->whereIn('id', $user->savedPosts()->select('posts.id'));
        }

        if ($q !== '') {
            $query->where(fn ($w) => $w->where('title', 'like', "%{$q}%")->orWhere('body', 'like', "%{$q}%"));
        }

        $query->orderByDesc('is_pinned');
        $sort === 'discussed' ? $query->orderByDesc('comments_count') : $query->orderByDesc('published_at');

        return view('community.index', [
            'posts' => $query->paginate(10)->withQueryString(),
            'savedIds' => $user->savedPosts()->pluck('posts.id')->all(),
            'q' => $q,
            'sort' => $sort,
            'showSaved' => $request->boolean('saved'),
            'canPost' => $this->canCreate($user),
        ]);
    }

    public function show(Request $request, Post $post)
    {
        abort_unless($post->status === 'published' || $request->user()->isModerator() || $post->user_id === $request->user()->id, 404);

        $post->increment('views');
        $post->load('author');

        $comments = $post->comments()->where('status', 'visible')->whereNull('parent_id')
            ->with(['author', 'replies.author', 'replies.replies.author'])->oldest()->get();

        return view('community.show', [
            'post' => $post,
            'comments' => $comments,
            'saved' => $request->user()->savedPosts()->where('posts.id', $post->id)->exists(),
        ]);
    }

    public function create(Request $request)
    {
        abort_unless($this->canCreate($request->user()), 403);

        return view('community.create');
    }

    public function store(Request $request)
    {
        $user = $request->user();
        abort_unless($this->canCreate($user), 403);

        $data = $request->validate([
            'title' => ['required', 'string', 'max:190'],
            'body' => ['required', 'string', 'max:20000'],
        ]);

        // Member posts are held for moderation unless the switch is off.
        $needsReview = ! $user->isModerator() && Setting::bool('member_posts_moderated', true);

        $post = Post::create([
            'user_id' => $user->id,
            'type' => 'discussion',
            'title' => $data['title'],
            'slug' => Post::uniqueSlug($data['title']),
            'body' => nl2br(e($data['body'])),
            'status' => $needsReview ? 'pending' : 'published',
            'published_at' => $needsReview ? null : now(),
        ]);

        if ($needsReview) {
            Notification::send(User::where('status', 'approved')->whereIn('role', ['admin', 'moderator'])->get(),
                new CommunityActivity("New member post awaiting review: {$post->title}", route('admin.posts.edit', $post)));

            return redirect()->route('community.index')->with('status', 'Thanks! Your post was submitted and will appear once a moderator approves it.');
        }

        return redirect()->route('community.show', $post)->with('status', 'Your post is live.');
    }

    public function toggleSave(Request $request, Post $post)
    {
        $request->user()->savedPosts()->toggle($post->id);

        return back();
    }

    public function report(Request $request, Post $post)
    {
        $this->storeReport($request, $post);

        return back()->with('status', 'Thank you. A moderator will review this post.');
    }

    public function reportComment(Request $request, Comment $comment)
    {
        $this->storeReport($request, $comment);

        return back()->with('status', 'Thank you. A moderator will review this comment.');
    }

    private function storeReport(Request $request, $model): void
    {
        $request->validate(['reason' => ['nullable', 'string', 'max:500']]);

        Report::firstOrCreate(
            ['user_id' => $request->user()->id, 'reportable_type' => $model->getMorphClass(), 'reportable_id' => $model->id, 'status' => 'open'],
            ['reason' => $request->input('reason')]
        );
    }

    private function canCreate(User $user): bool
    {
        return $user->isModerator() || Setting::bool('member_posts_enabled', false);
    }
}
