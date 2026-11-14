@extends('admin.layout', ['title' => 'Members'])

@section('content')
    <form method="GET" class="row" style="margin-bottom:16px"><input type="search" name="q" value="{{ $q }}" placeholder="Search name or email..." style="max-width:340px"><button class="btn">Search</button></form>
    <div class="card" style="padding:0">
        <table>
            <thead><tr><th>Member</th><th>Role</th><th>Status</th><th>Last login</th><th>Actions</th></tr></thead>
            <tbody>
            @foreach($users as $u)
                <tr>
                    <td><b>{{ $u->name }}</b><br><span class="muted">{{ $u->email }}</span></td>
                    <td><span class="badge">{{ ucfirst($u->role) }}</span></td>
                    <td><span class="badge {{ ['approved' => 'badge--ok', 'rejected' => 'badge--bad', 'suspended' => 'badge--bad'][$u->status] ?? 'badge--warn' }}">{{ ucfirst($u->status) }}</span></td>
                    <td>{{ $u->last_login_at?->diffForHumans() ?? 'never' }}</td>
                    <td>
                        @if($u->id !== auth()->id() && (auth()->user()->isAdmin() || ! $u->isAdmin()))
                            @php $act = fn ($a, $label, $cls = '', $confirm = null) => '<form class="inline" method="POST" action="'.route('admin.members.update', $u).'"'.($confirm ? ' data-confirm="'.e($confirm).'"' : '').'>'.csrf_field().'<input type="hidden" name="action" value="'.$a.'"><button class="btn btn--ghost btn--sm '.$cls.'">'.$label.'</button></form>'; @endphp
                            @if($u->status === 'suspended' || $u->status === 'rejected')
                                {!! $act('reactivate', 'Reactivate') !!}
                            @else
                                {!! $act('suspend', 'Suspend', '', 'Suspend '.$u->name.'?') !!}
                            @endif
                            {!! $act('resend_invite', 'Send password link') !!}
                            @if(auth()->user()->isAdmin())
                                {!! $u->role === 'moderator' ? $act('make_member', 'Make member') : $act('make_moderator', 'Make moderator') !!}
                            @endif
                        @endif
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
    {{ $users->links('community.pager') }}
@endsection
