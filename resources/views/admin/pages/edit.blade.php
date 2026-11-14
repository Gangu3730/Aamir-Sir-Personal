@extends('admin.layout', ['title' => 'Edit page: '.$page->title])

@section('content')
    <form method="POST" action="{{ route('admin.pages.update', $page) }}" class="card">
        @csrf @method('PUT')
        <div class="grid2">
            <div><label for="title">Page title</label><input id="title" type="text" name="title" value="{{ old('title', $page->title) }}" required></div>
            <div><label for="meta_title">Browser / SEO title</label><input id="meta_title" type="text" name="meta_title" value="{{ old('meta_title', $page->meta_title) }}"></div>
        </div>
        <label for="meta_description">SEO description</label>
        <textarea id="meta_description" name="meta_description" style="min-height:60px">{{ old('meta_description', $page->meta_description) }}</textarea>

        <label for="content">Page HTML</label>
        <textarea id="content" name="content" class="code" style="min-height:560px">{{ old('content', $page->content) }}</textarea>
        <p class="muted">Tip: <code>[[menu]]</code> inside a navigation list inserts the live menu from Settings.</p>

        <label class="check"><input type="checkbox" name="is_published" value="1" @checked(old('is_published', $page->is_published))> Published</label>
        <div class="row" style="margin-top:14px">
            <button class="btn">Save page</button>
            <a class="btn btn--ghost" href="{{ $page->slug === 'home' ? url('/') : url($page->slug) }}" target="_blank">View page</a>
            <a class="btn btn--ghost" href="{{ route('admin.pages.index') }}">Back</a>
        </div>
    </form>
@endsection
