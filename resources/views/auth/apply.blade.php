@extends('auth.layout', ['heading' => 'Apply for Access', 'inlineErrors' => true])

@section('form')
    <p class="auth-lead">This is a curated, members-only community. Submit your details below &mdash; every application is reviewed personally and you will receive an email once it is approved.</p>

    <form method="POST" action="{{ route('apply.store') }}" class="auth-form auth-grid" novalidate>
        @csrf
        <div class="hp" aria-hidden="true"><label>Website <input type="text" name="website" tabindex="-1" autocomplete="off"></label></div>

        @php
            $f = fn ($name, $label, $type = 'text', $req = false, $auto = null) => compact('name', 'label', 'type', 'req', 'auto');
            $fields = [
                $f('name', 'Full Name', 'text', true, 'name'),
                $f('email', 'Email Address', 'email', true, 'email'),
                $f('phone', 'Phone Number', 'tel', true, 'tel'),
                $f('profession', 'Profession / Designation'),
                $f('company', 'Company / Organisation'),
                $f('country', 'Country', 'select', true, 'country-name'),
                $f('location', 'Location (City / Region)', 'text', true, 'address-level2'),
                $f('linkedin_url', 'LinkedIn URL', 'url'),
            ];
        @endphp

        @foreach($fields as $fd)
            <div class="auth-field">
                <label for="{{ $fd['name'] }}">{{ $fd['label'] }} @if($fd['req'])<abbr title="required">*</abbr>@endif</label>
                @if($fd['type'] === 'select')
                    <select id="country" name="country" required autocomplete="country-name">
                        <option value="">Select your country</option>
                        @foreach(\App\Support\Countries::all() as $c)<option value="{{ $c }}" @selected(old('country') === $c)>{{ $c }}</option>@endforeach
                    </select>
                @else
                    <input id="{{ $fd['name'] }}" type="{{ $fd['type'] }}" name="{{ $fd['name'] }}" value="{{ old($fd['name']) }}" @if($fd['req']) required @endif @if($fd['auto']) autocomplete="{{ $fd['auto'] }}" @endif>
                @endif
                @error($fd['name'])<span class="auth-error">{{ $message }}</span>@enderror
            </div>
        @endforeach

        <div class="auth-field auth-field--full">
            <label for="reason">Why do you want to join?</label>
            <textarea id="reason" name="reason" rows="4">{{ old('reason') }}</textarea>
            @error('reason')<span class="auth-error">{{ $message }}</span>@enderror
        </div>

        <div class="auth-field--full">
            <button type="submit" class="auth-btn">Submit Application</button>
            <p class="auth-alt">Already approved? <a href="{{ route('login') }}">Log in</a></p>
        </div>
    </form>
@endsection
