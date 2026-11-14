@extends('community.layout', ['heading' => 'Community', 'pageTitle' => $post->title])

@section('content')
    <p><a href="{{ route('community.index') }}" class="cm-tag" style="text-decoration:none">&larr; All discussions</a></p>

    <article class="cm-article">
        <div class="cm-meta">
            @if($post->is_pinned)<span class="cm-pin">&#9733; Pinned</span>@endif
            <span class="cm-tag">{{ ucfirst($post->type) }}</span>
            <span>{{ $post->author->name }}</span>
            <span>{{ ($post->published_at ?? $post->created_at)->format('F j, Y') }}</span>
            @if($post->status !== 'published')<span class="cm-tag" style="background:#fff3cd;color:#856404">{{ ucfirst($post->status) }}</span>@endif
            @unless($post->comments_open)<span class="cm-tag" style="background:#eee;color:#555">Discussion closed</span>@endunless
        </div>
        <h1>{{ $post->title }}</h1>
        @if($post->featured_image)<img class="cm-hero" src="{{ $post->featured_image }}" alt="">@endif
        <div class="cm-body">{!! $post->body !!}</div>

        <div class="cm-actions">
            <form method="POST" action="{{ route('community.save', $post) }}">@csrf
                <button class="cm-btn cm-btn--ghost cm-btn--sm" type="submit">{{ $saved ? '★ Saved' : '☆ Save' }}</button>
            </form>
            <form method="POST" action="{{ route('community.report', $post) }}" data-confirm="Report this post to the moderators?">@csrf
                <button class="cm-btn cm-btn--ghost cm-btn--sm" type="submit">Report</button>
            </form>
            @if(auth()->user()->isModerator())<a class="cm-btn cm-btn--ghost cm-btn--sm" href="{{ route('admin.posts.edit', $post) }}">Edit in admin</a>@endif
        </div>
    </article>

    <section class="cm-comments">
        <h3>{{ $comments->count() }} {{ Str::plural('discussion thread', $comments->count()) }}</h3>

        @if($post->comments_open && $post->status === 'published')
            <form method="POST" action="{{ route('community.comments.store', $post) }}" class="cm-form" style="margin-top:14px">
                @csrf
                <textarea name="body" required maxlength="5000" placeholder="Share your thoughts... (use @Name to mention a member)">{{ old('body') }}</textarea>
                <button class="cm-btn" type="submit" style="margin-top:8px">Post comment</button>
            </form>
        @else
            <p class="cm-meta" style="margin-top:12px">This discussion is closed to new comments.</p>
        @endif

        @foreach($comments as $comment)
            @include('community.comment', ['comment' => $comment, 'depth' => 0])
        @endforeach
    </section>
@endsection
