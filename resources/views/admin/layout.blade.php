<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>{{ $title ?? 'Admin' }} - {{ config('app.name') }}</title>
    <link rel="icon" href="/site/wp-content/uploads/2025/04/Group-1000004294.webp">
    <link rel="stylesheet" href="/css/admin.css?v={{ filemtime(public_path('css/admin.css')) }}">
</head>
<body>
@php
    $me = auth()->user();
    $pendingCount = \App\Models\User::pending()->count();
    $reportCount = \App\Models\Report::where('status', 'open')->count();
    $nav = fn ($route, $label, $count = 0, $admin = false) => ($admin && ! $me->isAdmin()) ? '' :
        '<a href="'.route($route).'" class="'.(request()->routeIs($route.'*') ? 'on' : '').'">'.e($label).($count ? '<span class="ad-pill">'.$count.'</span>' : '').'</a>';
@endphp
<div class="ad">
    <aside class="ad-side">
        <div class="ad-brand">{{ \App\Models\Setting::get('site_name', 'Aamir Sheeraz') }}<small>{{ $me->isAdmin() ? 'Super Admin' : 'Moderator' }}</small></div>
        {!! $nav('admin.dashboard', 'Dashboard') !!}
        {!! $nav('admin.applications.index', 'Applications', $pendingCount) !!}
        {!! $nav('admin.members.index', 'Members') !!}
        <div class="sep"></div>
        {!! $nav('admin.posts.index', 'Posts & Discussions') !!}
        {!! $nav('admin.comments', 'Comments') !!}
        {!! $nav('admin.reports', 'Reported content', $reportCount) !!}
        <div class="sep"></div>
        {!! $nav('admin.pages.index', 'Website pages (CMS)', 0, true) !!}
        {!! $nav('admin.settings', 'Settings', 0, true) !!}
        <div class="sep"></div>
        <a href="{{ route('community.index') }}">Open community &rarr;</a>
        <a href="{{ url('/') }}" target="_blank">View website &rarr;</a>
        <form method="POST" action="{{ route('logout') }}">@csrf<a href="#" onclick="this.closest('form').submit();return false">Log out</a></form>
    </aside>
    <main class="ad-main">
        <h1>{{ $title ?? 'Admin' }}</h1>
        @if(session('status'))<div class="flash">{{ session('status') }}</div>@endif
        @if($errors->any())<div class="errors">@foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach</div>@endif
        @yield('content')
    </main>
</div>
<script>
document.querySelectorAll('[data-confirm]').forEach(function(f){f.addEventListener('submit',function(e){if(!confirm(f.getAttribute('data-confirm')))e.preventDefault()})});
</script>
@stack("scripts")
<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/ScrollTrigger.min.js"></script>
<script src="/site/js/motion.js?v={{ filemtime(public_path('site/js/motion.js')) }}"></script>
</body>
</html>
