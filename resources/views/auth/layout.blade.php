@php use App\Support\Cms; @endphp
@extends('site.layout', ['set' => 'login', 'title' => ($heading ?? 'Log In').' - Amir Sheeraz', 'noindex' => true, 'motion' => true])

@section('body')
    {!! Cms::part('header') !!}

    <div class="elementor elementor-1015">
        <div class="elementor-element e-flex e-con-boxed e-con e-parent auth-wrap">
            <div class="e-con-inner">
                <div class="elementor-widget elementor-widget-heading">
                    <div class="elementor-widget-container">
                        <h1 class="elementor-heading-title elementor-size-default">{{ $heading ?? 'Log In' }}</h1>
                    </div>
                </div>

                @if(empty($hideIntro))
                    <div class="auth-intro">{!! Cms::part('auth_intro') !!}</div>
                @endif

                <div class="auth-card">
                    @if(session('status'))
                        <div class="auth-alert auth-alert--ok">{{ session('status') }}</div>
                    @endif
                    @if($errors->any() && empty($inlineErrors))
                        <div class="auth-alert auth-alert--err">
                            @foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach
                        </div>
                    @endif

                    @yield('form')
                </div>
            </div>
        </div>
    </div>

    {!! Cms::part('footer') !!}
@endsection
