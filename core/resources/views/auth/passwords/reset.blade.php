@extends('layouts.auth')

@section('title', 'Reset Password')

@section('content')

@include('alert')

<form method="POST" action="{{ route('password.update') }}">
    @csrf

    <input type="hidden" name="token" value="{{ $token }}">

    <div class="form-group">
        <label for="email">Email Address</label>
        <input name="email" type="email" id="email" class="form-control" placeholder="foo@nuswhispers.com" value="{{ old('email') }}"
            required>
    </div>

    <div class="form-group">
        <label for="password">Password</label>
        <input name="password" type="password" id="password" class="form-control" required>
    </div>

    <div class="form-group">
        <label for="password_confirmation">Confirm Password</label>
        <input name="password_confirmation" type="password" id="password_confirmation" class="form-control" required>
    </div>

    <div class="
            form-group">
        <button class="btn btn-lg btn-primary btn-block" type="submit">Reset Password</button>
    </div>
</form>

@endsection
