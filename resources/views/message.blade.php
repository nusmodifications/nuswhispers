@if(env('MANUAL_MODE', false))
<p class="alert alert-warning">Manual mode is <strong>ON</strong>. Approved confessions will not be automatically posted to Facebook. You will have to do it manually and update the post ID accordingly.</p>
@endif

@if(session()->has('message'))
<p class="alert {{ session()->get('alert-class', 'alert-info') }}">{{ session()->get('message') }}</p>
@endif

@if($errors->any())
  <div class="alert alert-danger">
    Please fix the following validation errors:
    <ul>
      @foreach($errors->all() as $error)
        <li>{{ $error }}</li>
      @endforeach
    </ul>
  </div>
@endif
