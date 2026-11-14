@extends('admin.layout', ['title' => 'Reported content'])

@section('content')
    <div class="tabs">@foreach(['open', 'resolved', 'dismissed'] as $s)<a href="{{ route('admin.reports', ['status' => $s]) }}" class="{{ $status === $s ? 'on' : '' }}">{{ ucfirst($s) }}</a>@endforeach</div>
    <div class="card" style="padding:0">
        <table>
            <thead><tr><th>Content</th><th>Reported by</th><th>Reason</th><th>When</th><th></th></tr></thead>
            <tbody>
            @forelse($reports as $r)
                @php $item = $r->reportable; $isPost = $item instanceof \App\Models\Post; @endphp
                <tr>
                    <td>
                        @if(! $item) <span class="muted">(removed)</span>
                        @elseif($isPost) <span class="badge">Post</span> <a href="{{ route('community.show', $item) }}" target="_blank">{{ $item->title }}</a>
                        @else <span class="badge">Comment</span> {{ \Illuminate\Support\Str::limit($item->body, 120) }} @endif
                    </td>
                    <td>{{ $r->reporter->name }}</td>
                    <td>{{ $r->reason ?: '-' }}</td>
                    <td>{{ $r->created_at->diffForHumans() }}</td>
                    <td style="white-space:nowrap">
                        @if($r->status === 'open')
                            @foreach(['remove' => ['Remove content', 'btn--danger'], 'resolve' => ['Mark resolved', ''], 'dismiss' => ['Dismiss', 'btn--ghost']] as $a => [$l, $c])
                                <form class="inline" method="POST" action="{{ route('admin.reports.resolve', $r) }}" @if($a === 'remove') data-confirm="Remove this content?" @endif>@csrf<input type="hidden" name="action" value="{{ $a }}"><button class="btn btn--sm {{ $c }}">{{ $l }}</button></form>
                            @endforeach
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" class="muted" style="text-align:center;padding:30px">No reports.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    {{ $reports->links('community.pager') }}
@endsection
