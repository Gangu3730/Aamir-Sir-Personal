@extends('community.layout', ['heading' => 'Alerts', 'pageTitle' => 'Alerts'])

@section('content')
    <div class="cm-panel">
        @forelse($items as $n)
            <div class="cm-notif {{ $n->read_at ? '' : 'unread' }}">
                <a href="{{ $n->data['url'] ?? '#' }}">{{ $n->data['title'] ?? 'Notification' }}</a>
                <div class="cm-meta">{{ $n->created_at->diffForHumans() }}</div>
            </div>
        @empty
            <div class="cm-empty">No alerts yet.</div>
        @endforelse
    </div>
    {{ $items->links('community.pager') }}
@endsection
