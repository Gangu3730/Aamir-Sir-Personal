@extends('auth.layout', ['heading' => 'Application Received', 'hideIntro' => true])

@section('form')
    <p class="auth-lead">Thank you &mdash; your application is now <strong>pending review</strong>.</p>
    <p class="auth-lead">Once it is approved you will receive an email with a secure link to set your password and enter the community. If it is not approved, no access will be granted.</p>
    <p class="auth-alt"><a href="{{ url('/') }}">&larr; Back to the website</a></p>
@endsection
