@extends('layouts.auth')

@section('title', 'Login')

@section('content')

<form method="POST" action="{{ route('login') }}">
    @csrf

    @if ($errors->isNotEmpty())
    <div class="alert alert-danger">
        Oops, we can't seem to log you in. Please check your username and password, or try again later.
    </div>
    @endif

    <div class="form-group">
        <label for="email">Email Address</label>
        <input name="email" type="email" id="email" class="form-control" placeholder="foo@nuswhispers.com" required
            autofocus>
    </div>

    <div class="form-group">
        <label for="password">Password</label>
        <input name="password" type="password" id="password" class="form-control" required>
    </div>

    <div class="form-group">
        <div class="form-check">
            <input type="checkbox" class="form-check-input" name="remember" id="remember">
            <label class="form-check-label" for="remember">
                Remember me
            </label>
        </div>
    </div>

    <div class="form-group">
        <button class="btn btn-lg btn-primary btn-block" type="submit">Login</button>
    </div>

    <div class="form-group text-center">
        <a href="{{ route('password.request') }}">Forgot your password?</a>
    </div>
</form>

@endsection
