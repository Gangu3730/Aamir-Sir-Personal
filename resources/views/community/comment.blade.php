@php $me = auth()->user(); @endphp
<div class="cm-comment" id="comment-{{ $comment->id }}">
    <div class="cm-avatar">{{ $comment->author->initials() }}</div>
    <div class="cm-cbody">
        <div class="cm-cname">{{ $comment->author->name }}@if($comment->author->isAdmin()) <span class="cm-tag">Author</span>@endif<small>{{ $comment->created_at->diffForHumans() }}@if($comment->edited_at) &middot; edited @endif</small></div>
        <div class="cm-ctext">{{ $comment->body }}</div>
        <div class="cm-clinks">
            @if($post->comments_open && $depth < 3)<button type="button" data-reply-toggle="reply-{{ $comment->id }}">Reply</button>@endif
            @if($comment->user_id === $me->id)<button type="button" data-reply-toggle="edit-{{ $comment->id }}">Edit</button>@endif
            @if($comment->user_id !== $me->id)
                <form method="POST" action="{{ route('community.comments.report', $comment) }}" data-confirm="Report this comment to the moderators?">@csrf<button type="submit">Report</button></form>
            @endif
            @if($me->isModerator() || $comment->user_id === $me->id)
                <form method="POST" action="{{ route('community.comments.destroy', $comment) }}" data-confirm="Delete this comment?">@csrf @method('DELETE')<button type="submit">Delete</button></form>
            @endif
        </div>

        @if($comment->user_id === $me->id)
            <form method="POST" action="{{ route('community.comments.update', $comment) }}" class="cm-form cm-reply" id="edit-{{ $comment->id }}">
                @csrf @method('PUT')
                <textarea name="body" required maxlength="5000">{{ $comment->body }}</textarea>
                <button class="cm-btn cm-btn--sm" type="submit" style="margin-top:8px">Save changes</button>
            </form>
        @endif

        @if($post->comments_open && $depth < 3)
            <form method="POST" action="{{ route('community.comments.store', $post) }}" class="cm-form cm-reply" id="reply-{{ $comment->id }}">
                @csrf
                <input type="hidden" name="parent_id" value="{{ $comment->id }}">
                <textarea name="body" required maxlength="5000" placeholder="Write a reply..."></textarea>
                <button class="cm-btn cm-btn--sm" type="submit" style="margin-top:8px">Post reply</button>
            </form>
        @endif

        @if($comment->replies->isNotEmpty())
            <div class="cm-replies">
                @foreach($comment->replies as $reply)
                    @include('community.comment', ['comment' => $reply, 'depth' => $depth + 1])
                @endforeach
            </div>
        @endif
    </div>
</div>
