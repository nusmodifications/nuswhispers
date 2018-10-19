@extends('layouts.admin')

@section('title', 'My Profile')

@section('content')

<div class="page-header">
    <h1>
        <span class="typcn typcn-user"></span> Manage User
    </h1>
</div>

<h4>Connected Accounts</h4>

<p>
    <b>Facebook:</b> you will need to connect to a Facebook account with editor or admin access to the
    Facebook page so that newly approved confessions can be automatically submitted to that page.
</p>

<table class="table table-bordered table-hover">
    <tbody>
        @foreach ($providers as $id => $name)
        <tr>
            <td style="width: 60%">
                <span class="typcn typcn-social-{{$id}}"></span> {{$name}}
            </td>
            <td class="actions">
                @if (isset($profiles[$id]))
                @php $linkedData = json_decode($profiles[$id]['data']) @endphp
                Connected as {{ $linkedData->name ? $linkedData->name : $linkedData->first_name }} <a href="{{ route('admin.profile.unconnect', $id) }}"><span
                        class="typcn typcn-delete"></span></a>
                @else
                <a href="{{ route('admin.profile.connect', $id) }}">Login with {{$name}}</a>
                @endif
            </td>
        </tr>
        @endforeach
    </tbody>
</table>



<form action="{{ route('admin.profile.update') }}">
    @csrf

    <div class="my-3 py-3">
        <h4>Edit Profile</h4>

        <div class="form-group">
            <label for="email">Email Address <span class="text-danger">*</span></label>
            <input id="email" name="email" type="email" class="form-control {{ $errors->first('email') ? 'is-invalid' : '' }}"
                value="{{ old('email') ?? $user->email }}" placeholder="foo@nuswhispers.com" required autofocus>
            <div class="invalid-feedback">{{ $errors->first('email') }}</div>
        </div>

        <div class="form-group">
            <label for="name">Display Name <span class="text-danger">*</span></label>
            <input id="name" name="name" type="text" class="form-control {{ $errors->first('name') ? 'is-invalid' : '' }}"
                value="{{ old('name') ?? $user->name }}" required>
            <div class="invalid-feedback">{{ $errors->first('name') }}</div>
        </div>
    </div>

    <div class="my-3 py-3">
        <h4>Change Password</h4>

        <div class="form-group">
            <label for="password">New Password <span class="text-danger">*</span></label>
            <input id="password" name="password" type="password" class="form-control {{ $errors->first('password') ? 'is-invalid' : '' }}"
                required autofocus>
            <div class="invalid-feedback">{{ $errors->first('password') }}</div>
        </div>

        <div class="form-group">
            <label for="repeat_password">Repeat New Password <span class="text-danger">*</span></label>
            <input id="repeat_password" name="repeat_password" type="password" class="form-control {{ $errors->first('repeat_password') ? 'is-invalid' : '' }}"
                required autofocus>
            <div class="invalid-feedback">{{ $errors->first('repeat_password') }}</div>
        </div>
    </div>

    <button type="submit" class="btn btn-primary">Update Profile</button>
</form>

@endsection
