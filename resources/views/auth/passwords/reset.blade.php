@extends('card')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-4 col-md-offset-4">
            @if (count($errors) > 0)
                <div class="alert alert-danger">
                    <strong>Whoops!</strong> There were some problems with your input.<br><br>
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form class="card form" role="form" method="POST" action="{{ url('/password/reset') }}">
                {{ csrf_field() }}

                <input type="hidden" name="token" value="{{ $token }}">

                <div class="form-group">
                    <label class="control-label">E-Mail Address</label>
                    <input type="email" class="input-lg form-control" name="email" value="{{ old('email') }}">
                </div>

                <div class="form-group">
                    <label class="control-label">Password</label>
                    <input type="password" class="input-lg form-control" name="password">
                </div>

                <div class="form-group">
                    <label class="control-label">Confirm Password</label>
                    <input type="password" class="input-lg form-control" name="password_confirmation">
                </div>

                <div class="form-group">
                    <button type="submit" class="btn btn-block btn-lg btn-primary">
                        Reset Password
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
