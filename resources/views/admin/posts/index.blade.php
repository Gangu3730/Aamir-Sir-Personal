@extends('admin.layout', ['title' => 'Posts & Discussions'])

@section('content')
    <div class="row" style="justify-content:space-between;margin-bottom:14px">
        <div class="tabs" style="margin:0">
            <a href="{{ route('admin.posts.index') }}" class="{{ ! $status ? 'on' : '' }}">All</a>
            @foreach(['published', 'draft', 'pending'] as $s)<a href="{{ route('admin.posts.index', ['status' => $s]) }}" class="{{ $status === $s ? 'on' : '' }}">{{ ucfirst($s) }}</a>@endforeach
        </div>
        <a class="btn" href="{{ route('admin.posts.create') }}">+ New post</a>
    </div>
    <div class="card" style="padding:0">
        <table>
            <thead><tr><th>Title</th><th>Type</th><th>Status</th><th>Comments</th><th>Updated</th><th></th></tr></thead>
            <tbody>
            @forelse($posts as $p)
                <tr>
                    <td><a href="{{ route('admin.posts.edit', $p) }}"><b>{{ $p->title }}</b></a>@if($p->is_pinned) <span class="badge badge--warn">Pinned</span>@endif<br><span class="muted">by {{ $p->author->name }}</span></td>
                    <td>{{ ucfirst($p->type) }}</td>
                    <td><span class="badge {{ $p->status === 'published' ? 'badge--ok' : 'badge--warn' }}">{{ ucfirst($p->status) }}</span>@unless($p->comments_open) <span class="badge">Closed</span>@endunless</td>
                    <td>{{ $p->comments_count }}</td>
                    <td>{{ $p->updated_at->diffForHumans() }}</td>
                    <td style="white-space:nowrap">
                        @php $t = fn ($a, $l) => '<form class="inline" method="POST" action="'.route('admin.posts.toggle', $p).'">'.csrf_field().'<input type="hidden" name="action" value="'.$a.'"><button class="btn btn--ghost btn--sm">'.$l.'</button></form>'; @endphp
                        @if($p->status !== 'published') {!! $t('publish', 'Publish') !!} @endif
                        {!! $p->is_pinned ? $t('unpin', 'Unpin') : $t('pin', 'Pin') !!}
                        {!! $p->comments_open ? $t('close', 'Close') : $t('reopen', 'Reopen') !!}
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="muted" style="text-align:center;padding:30px">No posts.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    {{ $posts->links('community.pager') }}
@endsection
