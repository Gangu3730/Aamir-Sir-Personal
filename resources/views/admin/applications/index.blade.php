@extends('admin.layout', ['title' => 'Applications'])

@section('content')
    <div class="tabs">
        @foreach(['pending' => 'Pending', 'approved' => 'Approved', 'rejected' => 'Rejected'] as $key => $label)
            <a href="{{ route('admin.applications.index', ['status' => $key]) }}" class="{{ $status === $key ? 'on' : '' }}">{{ $label }} ({{ $counts[$key] ?? 0 }})</a>
        @endforeach
    </div>
    <div class="card" style="padding:0">
        <table>
            <thead><tr><th>Applicant</th><th>Phone</th><th>Profession</th><th>Applied</th><th></th></tr></thead>
            <tbody>
            @forelse($users as $u)
                <tr>
                    <td><b>{{ $u->name }}</b><br><span class="muted">{{ $u->email }}</span></td>
                    <td>{{ $u->phone }}</td>
                    <td>{{ $u->profession }}<br><span class="muted">{{ $u->company }}</span></td>
                    <td>{{ $u->created_at->format('M j, Y') }}</td>
                    <td style="white-space:nowrap">
                        <a class="btn btn--ghost btn--sm" href="{{ route('admin.applications.show', $u) }}">View</a>
                        @if($u->status === 'pending')
                            <form class="inline" method="POST" action="{{ route('admin.applications.approve', $u) }}">@csrf<button class="btn btn--sm">Approve</button></form>
                            <form class="inline" method="POST" action="{{ route('admin.applications.reject', $u) }}" data-confirm="Reject this application?">@csrf<button class="btn btn--danger btn--sm">Reject</button></form>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" class="muted" style="text-align:center;padding:30px">Nothing here.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    {{ $users->links('community.pager') }}
@endsection
