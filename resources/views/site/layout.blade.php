@php
    use App\Support\Cms;
    $set = $set ?? 'about-me';
    $bodyClass = $bodyClass ?? 'wp-singular page wp-embed-responsive wp-theme-hello-elementor wp-child-theme-hello-elementor-child hello-elementor-default elementor-default elementor-template-full-width elementor-kit-6 elementor-page';
@endphp
<!doctype html>
<html lang="en-US">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Amir Sheeraz' }}</title>
    @isset($description)<meta name="description" content="{{ $description }}">@endisset
    @if(!empty($noindex))<meta name="robots" content="noindex, nofollow, noarchive">@else<meta name="robots" content="max-image-preview:large">@endif
    <link rel="icon" href="/site/wp-content/uploads/2025/04/Group-1000004294.webp" sizes="32x32">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Lato:wght@300;400;700&family=Playfair+Display:wght@400;500;600;700&family=Poppins:wght@300;400;500;600;700&family=Roboto:wght@300;400;500;600;700&family=Roboto+Slab:wght@400;500;700&display=swap">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    @foreach(Cms::css($set) as $href)
        <link rel="stylesheet" href="{{ $href }}">
    @endforeach
    @if(file_exists(public_path("site/css/inline-{$set}.css")))
        <link rel="stylesheet" href="/site/css/inline-{{ $set }}.css">
    @endif
    <link rel="stylesheet" href="/site/css/about.css?v={{ filemtime(public_path('site/css/about.css')) }}">
    @if(str_contains($bodyClass ?? '','page-professional'))
    <link rel="stylesheet" href="/site/css/professional.css?v={{ filemtime(public_path('site/css/professional.css')) }}">
    @endif
    <link rel="stylesheet" href="/site/css/polish.css?v={{ filemtime(public_path('site/css/polish.css')) }}">
    <link rel="stylesheet" href="/site/css/app-forms.css?v={{ filemtime(public_path('site/css/app-forms.css')) }}">
    @stack('head')
</head>
<body class="{{ $bodyClass }} {{ auth()->check() ? 'logged-in' : '' }}">

@yield('body')

<script src="/site/wp-includes/js/jquery/jquery.min.js"></script>
<script src="/site/wp-content/uploads/ac_assets/fullscreen_navigation_menu/menu.js"></script>
<script src="/site/js/site.js?v={{ filemtime(public_path('site/js/site.js')) }}"></script>
@if(!empty($motion))
<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/ScrollTrigger.min.js"></script>
<script src="/site/js/motion.js?v={{ filemtime(public_path('site/js/motion.js')) }}"></script>
@endif
@stack('scripts')
</body>
</html>
