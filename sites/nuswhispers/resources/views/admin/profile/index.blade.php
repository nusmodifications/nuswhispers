@extends('admin')

@section('content')
<div class="page-header">
  <h1 class="page-title"><span class="typcn typcn-user"></span>My Profile</h1>
</div>

@include('message')

<div class="admin-content-wrapper">
  <h2>Connected Accounts</h2>
  <p class="description"><strong>Facebook:</strong> you will need to connect to a Facebook account with editor or admin access to the Facebook page so that newly approved confessions can be automatically submitted to that page.</p>

  <table class="table table-striped table-hover user-profiles-list">
    <tbody>
      @foreach ($providers as $id => $name)
      <tr>
        <td><span class="typcn typcn-social-{{$id}}"></span> {{$name}}</td>
        <td class="actions">
          @if (isset($profiles[$id]))
            <?php $linkedData = json_decode($profiles[$id]['data']); ?>
            Connected as {{$linkedData->first_name}} <a href="/admin/profile/delete/{{$id}}"><span class="typcn typcn-delete"></span></a>
          @else
            <a href="/admin/profile/connect/{{$id}}">Connect</a>
          @endif
        </td>
      </tr>
      @endforeach
    </tbody>
  </table>

  <h2>Edit My Profile</h2>
  <?php echo \Form::model($user, ['route' => 'admin.profile.edit', 'class' => 'profile-form form']) ?>

  <div class="form-group {{$errors->first('email') ? 'has-error' : ''}}">
    <label for="email">E-mail Address <span class="text-danger">*</span></label>
    <?php echo \Form::text('email', null, ['class' => 'form-control']) ?>
    @if ($errors->first('email'))
    <p class="alert alert-danger">{{$errors->first('email')}}</p>
    @endif
  </div>

  <div class="form-group {{$errors->first('name') ? 'has-error' : ''}}">
    <label for="name">Display Name <span class="text-danger">*</span></label>
    <?php echo \Form::text('name', null, ['class' => 'form-control']) ?>
    @if ($errors->first('name'))
    <p class="alert alert-danger">{{$errors->first('name')}}</p>
    @endif
  </div>

  <h2>Change Password</h2>

  <div class="form-group {{$errors->first('new_password') ? 'has-error' : ''}}">
    <label for="new_password">New Password</label>
    <?php echo \Form::password('new_password', ['class' => 'form-control', 'autocomplete' => 'off']) ?>
    @if ($errors->first('new_password'))
    <p class="alert alert-danger">{{$errors->first('new_password')}}</p>
    @endif
  </div>

  <div class="form-group {{$errors->first('repeat_password') ? 'has-error' : ''}}">
    <label for="repeat_password">Repeat Password</label>
    <?php echo \Form::password('repeat_password', ['class' => 'form-control', 'autocomplete' => 'off']) ?>
    @if ($errors->first('repeat_password'))
    <p class="alert alert-danger">{{$errors->first('repeat_password')}}</p>
    @endif
  </div>

  <p class="form-actions">
  <?php echo \Form::submit('Update Profile', ['class' => 'btn btn-primary']) ?>
  </p>

  <?php echo \Form::close() ?>
</div>
@endsection
