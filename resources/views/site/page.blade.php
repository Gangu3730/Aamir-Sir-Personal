@php
    use App\Support\Cms;
    // Home and Professional embed their own navigation; the others use the shared header/footer parts.
    $embedsHeader = in_array($page->slug, ['home', 'professional'], true);
    $set = in_array($page->slug, ['home', 'professional', 'about-me'], true) ? $page->slug : 'about-me';
@endphp
@extends('site.layout', [
    'set' => $set,
    'title' => $page->meta_title ?: $page->title,
    'description' => $page->meta_description,
    'bodyClass' => 'wp-singular page page-template-elementor_header_footer wp-embed-responsive wp-theme-hello-elementor wp-child-theme-hello-elementor-child hello-elementor-default elementor-default elementor-template-full-width elementor-kit-6 elementor-page '.($page->slug === 'home' ? 'home' : '').($page->slug === 'about-me' ? ' page-about' : '').($page->slug === 'professional' ? ' page-professional' : ''),
])

@section('body')
    @unless($embedsHeader)
        {!! Cms::part('header') !!}
    @endunless

    {!! Cms::render($page->content) !!}

    @if($page->slug !== 'home')
        {!! Cms::part('footer') !!}
    @endif
    @if($page->slug === 'professional')
        {!! Cms::part('popup') !!}
    @endif
@endsection
