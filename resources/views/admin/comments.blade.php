@extends('admin.layout', ['title' => 'Comments'])

@section('content')
    <div class="card" style="padding:0">
        <table>
            <thead><tr><th>Comment</th><th>Author</th><th>On post</th><th>When</th><th></th></tr></thead>
            <tbody>
            @forelse($comments as $c)
                <tr>
                    <td style="max-width:420px;white-space:pre-line">{{ \Illuminate\Support\Str::limit($c->body, 220) }}</td>
                    <td>{{ $c->author->name }}</td>
                    <td>@if($c->post)<a href="{{ route('community.show', $c->post) }}#comment-{{ $c->id }}" target="_blank">{{ \Illuminate\Support\Str::limit($c->post->title, 50) }}</a>@endif</td>
                    <td>{{ $c->created_at->diffForHumans() }}</td>
                    <td><form method="POST" action="{{ route('admin.comments.destroy', $c) }}" data-confirm="Delete this comment?">@csrf @method('DELETE')<button class="btn btn--danger btn--sm">Delete</button></form></td>
                </tr>
            @empty
                <tr><td colspan="5" class="muted" style="text-align:center;padding:30px">No comments yet.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    {{ $comments->links('community.pager') }}
@endsection
