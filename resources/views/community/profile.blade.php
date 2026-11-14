@extends('community.layout', ['heading' => 'Your profile', 'pageTitle' => 'Profile'])

@section('content')
    <form method="POST" action="{{ route('community.profile.update') }}" class="cm-panel cm-form auth-form auth-grid">
        @csrf @method('PUT')
        <div class="auth-field--full"><h2>Profile</h2><p class="cm-meta">Email: {{ $user->email }} (contact the administrator to change it)</p></div>
        @foreach(['name' => 'Full name', 'phone' => 'Phone', 'profession' => 'Profession / designation', 'company' => 'Company / organisation', 'country' => 'Country', 'location' => 'Location (City / Region)', 'linkedin_url' => 'LinkedIn URL'] as $k => $label)
            <div class="auth-field">
                <label for="{{ $k }}">{{ $label }}</label>
                @if($k === 'country')
                    <select id="country" name="country" required>
                        <option value="">Select your country</option>
                        @foreach(\App\Support\Countries::all() as $c)<option value="{{ $c }}" @selected(old('country', $user->country) === $c)>{{ $c }}</option>@endforeach
                    </select>
                @else
                    <input id="{{ $k }}" type="text" name="{{ $k }}" value="{{ old($k, $user->$k) }}" @if(in_array($k, ['name', 'phone', 'location'])) required @endif>
                @endif
                @error($k)<span class="auth-error">{{ $message }}</span>@enderror
            </div>
        @endforeach
        <div class="auth-field auth-field--full">
            <label for="bio">About you</label>
            <textarea id="bio" name="bio" rows="3">{{ old('bio', $user->bio) }}</textarea>
        </div>
        <div class="auth-field--full"><button class="cm-btn" type="submit">Save profile</button></div>
    </form>

    <form method="POST" action="{{ route('community.profile.password') }}" class="cm-panel auth-form">
        @csrf @method('PUT')
        <h2>Change password</h2>
        <label for="current_password">Current password</label>
        <input id="current_password" type="password" name="current_password" required autocomplete="current-password">
        <label for="password">New password</label>
        <input id="password" type="password" name="password" required minlength="10" autocomplete="new-password">
        <label for="password_confirmation">Confirm new password</label>
        <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password">
        <button class="cm-btn" type="submit" style="margin-top:16px">Update password</button>
    </form>
@endsection
