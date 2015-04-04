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
            Connected as <a href="{{$linkedData->link}}" target="_blank">{{$linkedData->name}}</a> <a href="/admin/profile/delete/{{$id}}"><span class="typcn typcn-delete"></span></a>
          @else
            <a href="/admin/profile/connect/{{$id}}">Connect</a>
          @endif
        </td>
      </tr>
      @endforeach
    </tbody>
  </table>
</div>
@endsection
