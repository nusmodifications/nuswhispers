@extends('admin')

@section('content')
<form id="users-search-form" class="search-form form-inline" method="get">

  <div class="page-header">
    <h1 class="page-title"><span class="typcn typcn-group"></span>Users Management <a href="/admin/users/add" class="btn btn-sm btn-primary">Add New User</a></h1>

  </div>

  @include('message')

  <div class="search-filters">
    <?php echo str_replace('pagination', 'pagination pagination-sm', $users->render()); ?>
  </div>

  <table id="user-list" class="user-list table table-striped table-hover">
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
      <td>{{$user->email}}</td>
      <td>{{$user->name ?: '-'}}</td>
      <td>{{$user->role}}</td>
      <td>
        <a class="btn btn-sm" href="/admin/users/edit/{{ $user->user_id }}">
          Edit
        </a>
        @if (auth()->user()->user_id !== $user->user_id)
        <a class="btn btn-sm btn-danger" href="/admin/users/delete/{{ $user->user_id }}">
          Delete
        </a>
        @endif
      </td>
    </tr>
    @endforeach
    </tbody>
  </table>

  <div class="search-filters">
    <?php echo str_replace('pagination', 'pagination pagination-sm', $users->render()); ?>
  </div>

</form><!-- #users-search-form -->
@endsection
