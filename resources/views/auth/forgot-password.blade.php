@extends('auth.layout', ['heading' => 'Lost Password', 'hideIntro' => true])

@section('form')
    <p class="auth-lead">Enter your member email address and we will send you a link to set a new password.</p>
    <form method="POST" action="{{ route('password.email') }}" class="auth-form">
        @csrf
        <label for="email">Email</label>
        <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus>
        <button type="submit" class="auth-btn">Email me a link</button>
        <p class="auth-alt"><a href="{{ route('login') }}">&larr; Back to log in</a></p>
    </form>
@endsection
