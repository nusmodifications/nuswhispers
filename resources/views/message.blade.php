@if (config('app.manual_mode'))
<div class="alert alert-warning">
    Manual mode is <strong>ON</strong>. Approved confessions will not be automatically
    posted to Facebook. You will have to do it manually and update the post ID accordingly.
</div>
@endif

@if (session('message'))
<div class="alert {{ session('alert-class', 'alert-info') }}">
    {{ session('message') }}
</div>
@endif

@include('alert')
