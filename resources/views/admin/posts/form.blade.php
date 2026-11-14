@extends('admin.layout', ['title' => $post->exists ? 'Edit post' : 'New post'])

@section('content')
    <form method="POST" enctype="multipart/form-data" class="card" action="{{ $post->exists ? route('admin.posts.update', $post) : route('admin.posts.store') }}">
        @csrf
        @if($post->exists) @method('PUT') @endif

        <label for="title">Title</label>
        <input id="title" type="text" name="title" value="{{ old('title', $post->title) }}" required maxlength="190">

        <div class="grid2">
            <div>
                <label for="type">Type</label>
                <select id="type" name="type">@foreach(['article' => 'Article / thought', 'discussion' => 'Discussion prompt', 'question' => 'Question'] as $k => $l)<option value="{{ $k }}" @selected(old('type', $post->type) === $k)>{{ $l }}</option>@endforeach</select>
            </div>
            <div>
                <label for="status">Status</label>
                <select id="status" name="status"><option value="draft" @selected(old('status', $post->status) !== 'published')>Draft</option><option value="published" @selected(old('status', $post->status) === 'published')>Published</option></select>
            </div>
        </div>

        <label for="excerpt">Short summary (optional)</label>
        <textarea id="excerpt" name="excerpt" style="min-height:60px">{{ old('excerpt', $post->excerpt) }}</textarea>

        <label for="body">Content (HTML allowed)</label>
        <p class="muted" style="margin:0 0 6px">Use the Visual tab to format, or switch to HTML to paste or edit raw markup.</p>
        <textarea id="body" name="body" class="code" data-html-widget required>{{ old('body', $post->body) }}</textarea>

        <div class="grid2">
            <div>
                <label for="featured_image">Featured image</label>
                <input id="featured_image" type="file" name="featured_image" accept="image/*">
                @if($post->featured_image)<p class="muted">Current: <a href="{{ $post->featured_image }}" target="_blank">{{ basename($post->featured_image) }}</a></p>@endif
            </div>
            <div>
                <label for="published_at">Publish date (optional)</label>
                <input id="published_at" type="datetime-local" name="published_at" value="{{ old('published_at', $post->published_at?->format('Y-m-d\TH:i')) }}">
            </div>
        </div>

        <label class="check"><input type="checkbox" name="is_pinned" value="1" @checked(old('is_pinned', $post->is_pinned))> Pin to the top of the community</label>
        <label class="check"><input type="checkbox" name="comments_open" value="1" @checked(old('comments_open', $post->comments_open ?? true))> Allow comments and replies</label>

        <div class="row" style="margin-top:16px">
            <button class="btn">Save</button>
            <a class="btn btn--ghost" href="{{ route('admin.posts.index') }}">Back</a>
            @if($post->exists && $post->status === 'published')<a class="btn btn--ghost" href="{{ route('community.show', $post) }}" target="_blank">View</a>@endif
        </div>
    </form>

    @if($post->exists)
        <form method="POST" action="{{ route('admin.posts.destroy', $post) }}" data-confirm="Delete this post and its comments?">@csrf @method('DELETE')<button class="btn btn--danger btn--sm">Delete post</button></form>
    @endif
@endsection

@push("scripts")
<script src="/site/js/html-widget.js?v={{ filemtime(public_path("site/js/html-widget.js")) }}"></script>
@endpush
