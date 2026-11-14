@extends('community.layout', ['heading' => $showSaved ? 'Saved discussions' : 'Discussions', 'pageTitle' => 'Discussions'])

@section('content')
    <form method="GET" class="cm-search">
        @if($showSaved)<input type="hidden" name="saved" value="1">@endif
        <input type="search" name="q" value="{{ $q }}" placeholder="Search discussions...">
        <select name="sort" onchange="this.form.submit()">
            <option value="recent" @selected($sort === 'recent')>Most recent</option>
            <option value="discussed" @selected($sort === 'discussed')>Most discussed</option>
        </select>
        <button class="cm-btn" type="submit">Search</button>
        @if($canPost)<a class="cm-btn cm-btn--ghost" href="{{ route('community.create') }}">+ New post</a>@endif
    </form>

    @forelse($posts as $post)
        <article class="cm-card">
            @if($post->featured_image)<a href="{{ route('community.show', $post) }}"><img src="{{ $post->featured_image }}" alt="" loading="lazy"></a>@endif
            <div>
                <div class="cm-meta">
                    @if($post->is_pinned)<span class="cm-pin">&#9733; Pinned</span>@endif
                    <span class="cm-tag">{{ ucfirst($post->type) }}</span>
                    <span>{{ $post->author->name }}</span>
                    <span>{{ $post->published_at?->format('M j, Y') }}</span>
                    <span>{{ $post->comments_count }} {{ Str::plural('comment', $post->comments_count) }}</span>
                </div>
                <h2><a href="{{ route('community.show', $post) }}">{{ $post->title }}</a></h2>
                <p class="cm-excerpt">{{ $post->excerptText() }}</p>
                <div class="cm-actions" style="border:0;padding:0;margin-top:12px">
                    <a class="cm-btn cm-btn--sm" href="{{ route('community.show', $post) }}">Read &amp; discuss</a>
                    <form method="POST" action="{{ route('community.save', $post) }}">@csrf
                        <button class="cm-btn cm-btn--sm cm-btn--ghost" type="submit">{{ in_array($post->id, $savedIds) ? '★ Saved' : '☆ Save' }}</button>
                    </form>
                </div>
            </div>
        </article>
    @empty
        <div class="cm-empty">{{ $showSaved ? 'You have not saved anything yet.' : 'No discussions found.' }}</div>
    @endforelse

    {{ $posts->links('community.pager') }}
@endsection
