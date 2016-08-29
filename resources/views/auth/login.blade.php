@extends('card')

@section('content')
<div class="container-fluid">
	<div class="row">
		<div class="col-md-4 col-md-offset-4">
			@if (count($errors) > 0)
				<div class="alert alert-danger">
					Oops, we can't seem to log you in. Please check your username and password, or try again later.
				</div>
			@endif

			<form class="card form" role="form" method="POST" action="{{ url('/login') }}">
				<input type="hidden" name="_token" value="{{ csrf_token() }}">

				<div class="form-group">
					<label class="control-label">E-Mail Address</label>
					<input type="email" class="form-control input-lg" name="email" value="{{ old('email') }}">
				</div>

				<div class="form-group">
					<label class="control-label">Password</label>
					<input type="password" class="form-control input-lg" name="password">
				</div>

				<div class="form-group">
					<div class="checkbox">
						<label>
							<input type="checkbox" name="remember"> Remember Me
						</label>
					</div>
				</div>

				<div class="form-group">
					<button type="submit" class="btn btn-block btn-lg btn-primary">Login</button>
				</div>
                <p class="form-group forgot-password">
                    <a href="/password/reset">Forgot Your Password?</a>
                </p>
			</form>
		</div>
	</div>
</div>
@endsection
