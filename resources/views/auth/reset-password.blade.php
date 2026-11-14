@extends('auth.layout', ['heading' => $setup ? 'Set Your Password' : 'Reset Password', 'hideIntro' => true])

@section('form')
    <p class="auth-lead">
        {{ $setup ? 'Welcome! Choose a password to activate your account.' : 'Choose a new password for your account.' }}
        Use at least 10 characters, including letters and numbers.
    </p>
    <form method="POST" action="{{ route('password.update') }}" class="auth-form">
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">
        <label for="email">Email</label>
        <input id="email" type="email" name="email" value="{{ old('email', $email) }}" required readonly>
        <label for="password">New password</label>
        <input id="password" type="password" name="password" required minlength="10" autocomplete="new-password" autofocus>
        <label for="password_confirmation">Confirm password</label>
        <input id="password_confirmation" type="password" name="password_confirmation" required minlength="10" autocomplete="new-password">
        <button type="submit" class="auth-btn">{{ $setup ? 'Activate account' : 'Save password' }}</button>
    </form>
@endsection
