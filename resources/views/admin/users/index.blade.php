@extends('layouts.admin')

@section('title', 'Manage Users')

@section('content')

<div class="page-header">
    <h1>
        <span class="typcn typcn-group"></span>
        Users Management
    </h1>
    <div>
        <a href="/admin/users/add" class="btn btn-sm btn-primary">Add New User</a>
    </div>
</div>

<table class="table table-bordered table-striped table-hover">
    <thead>
        <tr>
            <th>Email Address</th>
            <th>Display Name</th>
            <th>Role</th>
            <th>Action(s)</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($users as $user)
        <tr>
            <td>{{ $user->email }}</td>
            <td>{{ $user->name ?: '-' }}</td>
            <td>{{ $user->role }}</td>
            <td>
                <a class="btn btn-secondary btn-sm" href="/admin/users/edit/{{ $user->user_id }}">
                    Edit
                </a>
                @can('delete', $user)
                <a class="btn btn-sm btn-danger" href="/admin/users/delete/{{ $user->user_id }}">
                    Delete
                </a>
                @endcan
            </td>
        </tr>
        @endforeach
    </tbody>
</table>

<div class="d-flex my-4 justify-content-end">
    {{ $users->links('admin.pagination') }}
</div>

@endsection
