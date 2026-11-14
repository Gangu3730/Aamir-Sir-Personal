@extends('community.layout', ['heading' => 'New post', 'pageTitle' => 'New post'])

@section('content')
    @if(\App\Models\Setting::bool('member_posts_moderated', true) && ! auth()->user()->isModerator())
        <div class="cm-flash" style="background:#fff8e1;border-color:#f3deb0;color:#856404">Posts are reviewed by a moderator before they appear.</div>
    @endif
    <form method="POST" action="{{ route('community.store') }}" class="cm-panel cm-form">
        @csrf
        <label for="title">Title</label>
        <input id="title" type="text" name="title" value="{{ old('title') }}" required maxlength="190">
        <label for="body">Your post</label>
        <textarea id="body" name="body" rows="10" required>{{ old('body') }}</textarea>
        <button class="cm-btn" type="submit" style="margin-top:14px">Submit</button>
    </form>
@endsection
