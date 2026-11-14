<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Post;
use App\Models\User;
use App\Notifications\CommunityActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;

class PostController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->query('status');
        $posts = Post::with('author')->withCount('comments')
            ->when($status, fn ($q) => $q->where('status', $status))
            ->latest('updated_at')->paginate(20)->withQueryString();

        return view('admin.posts.index', compact('posts', 'status'));
    }

    public function create()
    {
        return view('admin.posts.form', ['post' => new Post(['type' => 'article', 'status' => 'draft', 'comments_open' => true])]);
    }

    public function store(Request $request)
    {
        $post = new Post(['user_id' => $request->user()->id]);

        return $this->save($request, $post);
    }

    public function edit(Post $post)
    {
        return view('admin.posts.form', compact('post'));
    }

    public function update(Request $request, Post $post)
    {
        return $this->save($request, $post);
    }

    public function destroy(Post $post)
    {
        $post->delete();
        AuditLog::record('post.deleted', $post);

        return redirect()->route('admin.posts.index')->with('status', 'Post deleted.');
    }

    /** Quick actions: pin / unpin / close / reopen / publish (approve member post). */
    public function toggle(Request $request, Post $post)
    {
        $action = $request->validate(['action' => ['required', 'in:pin,unpin,close,reopen,publish,unpublish']])['action'];

        $wasPublished = $post->status === 'published';

        match ($action) {
            'pin' => $post->is_pinned = true,
            'unpin' => $post->is_pinned = false,
            'close' => $post->comments_open = false,
            'reopen' => $post->comments_open = true,
            'publish' => [$post->status, $post->published_at] = ['published', $post->published_at ?? now()],
            'unpublish' => $post->status = 'draft',
        };
        $post->save();

        if (! $wasPublished && $post->status === 'published') {
            $this->announce($post);
        }

        AuditLog::record('post.'.$action, $post);

        return back()->with('status', 'Done.');
    }

    private function save(Request $request, Post $post)
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:190'],
            'type' => ['required', 'in:article,discussion,question'],
            'excerpt' => ['nullable', 'string', 'max:500'],
            'body' => ['required', 'string'],
            'status' => ['required', 'in:draft,published'],
            'published_at' => ['nullable', 'date'],
            'is_pinned' => ['nullable', 'boolean'],
            'comments_open' => ['nullable', 'boolean'],
            'featured_image' => ['nullable', 'image', 'max:4096'],
        ]);

        $wasPublished = $post->exists && $post->status === 'published';

        $post->fill([
            'title' => $data['title'],
            'type' => $data['type'],
            'excerpt' => $data['excerpt'] ?? null,
            'body' => $data['body'],
            'status' => $data['status'],
            'is_pinned' => $request->boolean('is_pinned'),
            'comments_open' => $request->boolean('comments_open'),
        ]);

        if (! $post->exists || ! $post->slug) {
            $post->slug = Post::uniqueSlug($data['title']);
        }

        $post->published_at = $data['status'] === 'published'
            ? ($data['published_at'] ?? $post->published_at ?? now())
            : ($data['published_at'] ?? $post->published_at);

        if ($request->hasFile('featured_image')) {
            $post->featured_image = '/storage/'.$request->file('featured_image')->store('posts', 'public');
        }

        $post->save();
        AuditLog::record($post->wasRecentlyCreated ? 'post.created' : 'post.updated', $post);

        if (! $wasPublished && $post->status === 'published') {
            $this->announce($post);
        }

        return redirect()->route('admin.posts.edit', $post)->with('status', 'Saved.');
    }

    /** Alert members about a newly published post. */
    private function announce(Post $post): void
    {
        $members = User::where('status', 'approved')->where('id', '!=', $post->user_id)->get();
        Notification::send($members, new CommunityActivity("New in the community: {$post->title}", route('community.show', $post), email: $post->author?->isAdmin() ?? false));
    }
}
