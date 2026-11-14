@php use App\Support\Cms; @endphp
@extends('site.layout', ['set' => 'login', 'title' => ($pageTitle ?? 'Community').' - Amir Sheeraz', 'noindex' => true, 'motion' => true])

@section('body')
    {!! Cms::part('header') !!}

    <main class="cm">
        <div class="cm-top">
            <h1 class="cm-title">{{ $heading ?? 'Community' }}</h1>
            <nav class="cm-nav">
                <a href="{{ route('community.index') }}" class="{{ request()->routeIs('community.index') && ! request('saved') ? 'on' : '' }}">Discussions</a>
                <a href="{{ route('community.index', ['saved' => 1]) }}" class="{{ request('saved') ? 'on' : '' }}">Saved</a>
                <a href="{{ route('community.notifications') }}" class="{{ request()->routeIs('community.notifications') ? 'on' : '' }}">Alerts @if(($n = auth()->user()->unreadNotifications()->count()) > 0)<span class="cm-badge">{{ $n }}</span>@endif</a>
                <a href="{{ route('community.profile') }}" class="{{ request()->routeIs('community.profile') ? 'on' : '' }}">Profile</a>
                @if(auth()->user()->isModerator())<a href="{{ route('admin.dashboard') }}">Admin</a>@endif
                <form method="POST" action="{{ route('logout') }}" style="display:inline">@csrf<button type="submit">Log out</button></form>
            </nav>
        </div>

        @if(session('status'))<div class="cm-flash">{{ session('status') }}</div>@endif
        @if($errors->any())<div class="cm-errors">@foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach</div>@endif

        @yield('content')
    </main>

    {!! Cms::part('footer') !!}
@endsection
