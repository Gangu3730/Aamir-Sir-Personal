@extends('admin.layout', ['title' => 'Website pages (CMS)'])

@section('content')
    <p class="muted">Edit the public pages. The design is preserved &mdash; change text, links and images inside the page HTML.</p>
    <div class="card" style="padding:0">
        <table>
            <thead><tr><th>Page</th><th>URL</th><th>Status</th><th>Updated</th><th></th></tr></thead>
            <tbody>
            @foreach($pages as $p)
                <tr>
                    <td><b>{{ $p->title }}</b></td>
                    <td><a href="{{ $p->slug === 'home' ? url('/') : url($p->slug) }}" target="_blank">/{{ $p->slug === 'home' ? '' : $p->slug }}</a></td>
                    <td><span class="badge {{ $p->is_published ? 'badge--ok' : 'badge--warn' }}">{{ $p->is_published ? 'Published' : 'Hidden' }}</span></td>
                    <td>{{ $p->updated_at->diffForHumans() }}</td>
                    <td><a class="btn btn--sm" href="{{ route('admin.pages.edit', $p) }}">Edit</a></td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
@endsection
