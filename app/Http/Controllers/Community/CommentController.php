<?php

namespace App\Http\Controllers\Community;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use App\Notifications\CommunityActivity;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    public function store(Request $request, Post $post)
    {
        abort_unless($post->status === 'published' && $post->comments_open, 403, 'Comments are closed.');

        $data = $request->validate([
            'body' => ['required', 'string', 'max:5000'],
            'parent_id' => ['nullable', 'integer', 'exists:comments,id'],
        ]);

        $parent = null;
        if (! empty($data['parent_id'])) {
            $parent = Comment::where('post_id', $post->id)->findOrFail($data['parent_id']);
        }

        $user = $request->user();
        $comment = $post->comments()->create([
            'user_id' => $user->id,
            'parent_id' => $parent?->id,
            'body' => $data['body'],
        ]);

        $link = route('community.show', $post).'#comment-'.$comment->id;
        $notified = [$user->id];

        // Reply -> notify the parent author; comment -> notify the post author.
        $targets = collect([$parent?->author ?? $post->author]);

        // @mentions: match against exact member names.
        if (preg_match_all('/@([\p{L}\.\-\' ]{2,60})/u', $data['body'], $m)) {
            foreach ($m[1] as $raw) {
                $mentioned = User::where('status', 'approved')->get()->first(fn ($u) => str_starts_with(mb_strtolower($raw), mb_strtolower($u->name)));
                if ($mentioned) {
                    $mentioned->notify(new CommunityActivity("{$user->name} mentioned you in \"{$post->title}\"", $link));
                    $notified[] = $mentioned->id;
                }
            }
        }

        foreach ($targets as $t) {
            if ($t && ! in_array($t->id, $notified, true)) {
                $t->notify(new CommunityActivity("{$user->name} ".($parent ? 'replied to your comment' : 'commented')." on \"{$post->title}\"", $link));
            }
        }

        return redirect($link)->with('status', 'Comment posted.');
    }

    public function update(Request $request, Comment $comment)
    {
        abort_unless($comment->user_id === $request->user()->id, 403);

        $data = $request->validate(['body' => ['required', 'string', 'max:5000']]);

        if ($data['body'] !== $comment->body) {
            $comment->forceFill(['body' => $data['body'], 'edited_at' => now()])->save();
        }

        return redirect(route('community.show', $comment->post).'#comment-'.$comment->id)->with('status', 'Comment updated.');
    }

    public function destroy(Request $request, Comment $comment)
    {
        $user = $request->user();
        abort_unless($user->isModerator() || $comment->user_id === $user->id, 403);

        $comment->delete();

        return back()->with('status', 'Comment removed.');
    }
}
