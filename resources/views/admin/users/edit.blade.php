@php
$roles = ['Moderator', 'Administrator']
@endphp

@extends('layouts.admin')

@section('title', 'Edit User')

@section('content')
<div class="page-header">
    <h1>
        <span class="typcn typcn-group"></span>
        Edit User
    </h1>
    <div>
        <a class="btn btn-sm btn-outline-primary" href="{{ url('admin/users') }}">
            Back to Users
        </a>
    </div>
</div>

<form method="post" action="{{ url('admin/users/edit', $user->getKey()) }}">
    @csrf

    <div class="form-group">
        <label for="email">Email Address <span class="text-danger">*</span></label>
        <input name="email" type="email" class="form-control {{ $errors->first('email') ? 'is-invalid' : '' }}" value="{{ old('email') ?? $user->email }}"
            placeholder="foo@nuswhispers.com" required autofocus>
        <div class="invalid-feedback">{{ $errors->first('email') }}</div>
    </div>

    <div class="form-group">
        <label for="name">Display Name <span class="text-danger">*</span></label>
        <input name="name" type="text" class="form-control {{ $errors->first('name') ? 'is-invalid' : '' }}" value="{{ old('name') ?? $user->name }}"
            required>
        <div class="invalid-feedback">{{ $errors->first('name') }}</div>
    </div>

    @if (auth()->user()->getKey() !== $user->getKey())
    <div class="form-group">
        <label for="role">Role <span class="text-danger">*</span></label>
        @php
        $currentRole = old('role') ?? $user->role;
        @endphp
        <select name="role" class="form-control custom-select" required>
            @foreach ($roles as $role)
            <option value="{{ $role }}" {{ $role === $currentRole ? 'selected' : '' }}>{{ $role }}</option>
            @endforeach
        </select>
        <div class="invalid-feedback">{{ $errors->first('role') }}</div>
    </div>
    @endif

    <div class="form-group">
        <label for="password">New Password <span class="text-danger">*</span></label>
        <input name="password" type="password" class="form-control {{ $errors->first('password') ? 'is-invalid' : '' }}"
            required autofocus>
        <div class="invalid-feedback">{{ $errors->first('password') }}</div>
    </div>

    <div class="form-group">
        <label for="repeat_password">Repeat New Password <span class="text-danger">*</span></label>
        <input name="repeat_password" type="password" class="form-control {{ $errors->first('repeat_password') ? 'is-invalid' : '' }}"
            required autofocus>
        <div class="invalid-feedback">{{ $errors->first('repeat_password') }}</div>
    </div>

    <button type="submit" class="btn btn-primary">Update User</button>
</form>

@endsection
