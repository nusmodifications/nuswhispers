@extends('layouts.auth')

@section('title', 'Reset Password')

@section('content')

@include('alert')

<form method="POST" action="{{ route('password.email') }}">
    @csrf

    <div class="form-group">
        <label for="email">Email Address</label>
        <input name="email" type="email" id="email" class="form-control" placeholder="foo@nuswhispers.com" value="{{ old('email') }}"
            required>
    </div>

    <div class="
            form-group">
        <button class="btn btn-lg btn-primary btn-block" type="submit">Send Link</button>
    </div>
</form>

@endsection
