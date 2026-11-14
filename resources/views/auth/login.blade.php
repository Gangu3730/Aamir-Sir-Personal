@extends('auth.layout', ['heading' => 'Log In'])

@section('form')
    <form method="POST" action="{{ url('/login') }}" class="auth-form">
        @csrf
        <label for="email">Email</label>
        <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username">

        <label for="password">Password</label>
        <input id="password" type="password" name="password" required autocomplete="current-password">

        <div class="auth-row">
            <label class="auth-check"><input type="checkbox" name="remember" value="1"> Remember me</label>
            <a href="{{ route('password.request') }}">Lost your password?</a>
        </div>

        <button type="submit" class="auth-btn">Log In</button>

        <p class="auth-alt">Not a member yet? <a href="{{ route('apply') }}">Apply for access</a></p>
    </form>
@endsection
