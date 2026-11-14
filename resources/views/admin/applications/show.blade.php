@extends('admin.layout', ['title' => 'Application: '.$user->name])

@section('content')
    <div class="card">
        <dl class="kv">
            <dt>Status</dt><dd><span class="badge {{ ['approved' => 'badge--ok', 'pending' => 'badge--warn', 'rejected' => 'badge--bad', 'suspended' => 'badge--bad'][$user->status] ?? '' }}">{{ ucfirst($user->status) }}</span></dd>
            <dt>Full name</dt><dd>{{ $user->name }}</dd>
            <dt>Email</dt><dd>{{ $user->email }}</dd>
            <dt>Phone</dt><dd>{{ $user->phone }}</dd>
            <dt>Profession</dt><dd>{{ $user->profession ?: '-' }}</dd>
            <dt>Company</dt><dd>{{ $user->company ?: '-' }}</dd>
            <dt>Country</dt><dd>{{ $user->country ?: '-' }}</dd>
            <dt>Location</dt><dd>{{ $user->location ?: '-' }}</dd>
            <dt>LinkedIn</dt><dd>@if($user->linkedin_url)<a href="{{ $user->linkedin_url }}" target="_blank" rel="noopener noreferrer">{{ $user->linkedin_url }}</a>@else - @endif</dd>
            <dt>Why join?</dt><dd style="white-space:pre-line">{{ $user->reason ?: '-' }}</dd>
            <dt>Applied</dt><dd>{{ $user->created_at->format('M j, Y g:i A') }}</dd>
            @if($user->reviewed_at)<dt>Reviewed</dt><dd>{{ $user->reviewed_at->format('M j, Y g:i A') }}</dd>@endif
            @if($user->review_note)<dt>Note</dt><dd>{{ $user->review_note }}</dd>@endif
        </dl>
    </div>

    @if($user->status === 'pending' || $user->status === 'rejected')
        <div class="grid2">
            <form class="card" method="POST" action="{{ route('admin.applications.approve', $user) }}">
                @csrf
                <h2>Approve</h2>
                <p class="muted">Activates the account and emails a secure password-setup link.</p>
                <label for="role">Role</label>
                <select id="role" name="role"><option value="member">Member</option>@if(auth()->user()->isAdmin())<option value="moderator">Moderator</option>@endif</select>
                <button class="btn" style="margin-top:14px">Approve &amp; send invitation</button>
            </form>
            @if($user->status === 'pending')
                <form class="card" method="POST" action="{{ route('admin.applications.reject', $user) }}" data-confirm="Reject this application?">
                    @csrf
                    <h2>Reject</h2>
                    <p class="muted">No community access is granted. A rejection email is sent only if enabled in Settings.</p>
                    <label for="review_note">Internal note (optional)</label>
                    <textarea id="review_note" name="review_note" style="min-height:70px"></textarea>
                    <button class="btn btn--danger" style="margin-top:14px">Reject application</button>
                </form>
            @endif
        </div>
    @endif
    <a href="{{ route('admin.applications.index') }}">&larr; Back to applications</a>
@endsection
