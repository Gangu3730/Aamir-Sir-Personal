@extends('admin.layout', ['title' => 'Dashboard'])

@section('content')
    <div class="stats">
        <div class="stat"><b>{{ $pending }}</b><span>Pending applications</span></div>
        <div class="stat"><b>{{ $members }}</b><span>Active members</span></div>
        <div class="stat"><b>{{ $posts }}</b><span>Published posts</span></div>
        <div class="stat"><b>{{ $awaitingPosts }}</b><span>Member posts awaiting review</span></div>
        <div class="stat"><b>{{ $comments }}</b><span>Comments</span></div>
        <div class="stat"><b>{{ $openReports }}</b><span>Open reports</span></div>
    </div>

    <div class="grid2">
        <div class="card">
            <h2>Newest applicants</h2>
            @forelse($latestApplicants as $u)
                <div class="row" style="justify-content:space-between;padding:8px 0;border-bottom:1px solid var(--line)">
                    <div><b>{{ $u->name }}</b><br><span class="muted">{{ $u->email }} &middot; {{ $u->created_at->diffForHumans() }}</span></div>
                    <a class="btn btn--sm" href="{{ route('admin.applications.show', $u) }}">Review</a>
                </div>
            @empty
                <p class="muted">No applications waiting.</p>
            @endforelse
        </div>
        <div class="card">
            <h2>Recent activity</h2>
            @forelse($activity as $a)
                <div style="padding:7px 0;border-bottom:1px solid var(--line)">
                    <b>{{ $a->action }}</b> <span class="muted">by {{ $a->user?->name ?? 'system' }} &middot; {{ $a->created_at?->diffForHumans() }}</span>
                </div>
            @empty
                <p class="muted">Nothing yet.</p>
            @endforelse
        </div>
    </div>
@endsection
