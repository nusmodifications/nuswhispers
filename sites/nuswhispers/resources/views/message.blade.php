@if(env('MANUAL_MODE', false))
<p class="alert alert-warning">Manual mode is <strong>ON</strong>. Approved confessions will not be automatically posted to Facebook. You will have to do it manually and update the post ID accordingly.</p>
@endif

@if(\Session::has('message'))
<p class="alert {{ Session::get('alert-class', 'alert-info') }}">{{ Session::get('message') }}</p>
@endif
