@extends('admin')

@section('content')
<form id="confessions-search-form" class="search-form form-inline" method="get">

  <div class="page-header">
    <h1 class="page-title"><span class="typcn typcn-heart"></span>Confessions Management</h1>
    <div class="search-bar">
      <input class="form-control input-sm" name="q" type="text" value="{{ \Input::get('q') }}">
      <button class="btn btn-primary btn-sm" type="submit">Search</button>
    </div>
  </div>

  @include('message')

  @if (!$hasPageToken)
  <div class="alert alert-danger">
  <strong>Warning:</strong> You have not <a href="/admin/profile">connected your Facebook account</a>. You will not be able to approve any confessions until you do so.
  </div>
  @endif

  <ul class="nav nav-tabs">
    <li class="{{ Request::is('admin/confessions/index/all') ? 'active' : '' }}" role="presentation"><a href="/admin/confessions/index/all">All</a></li>
    <li class="{{ Request::is('admin/confessions/index/featured') ? 'active' : '' }}" role="presentation"><a href="/admin/confessions/index/featured">Featured</a></li>
    <li class="{{ Request::is('admin/confessions') || Request::is('admin/confessions/index/pending') ? 'active' : '' }}" role="presentation"><a href="/admin/confessions/index/pending">Pending ({{ \NUSWhispers\Models\Confession::pending()->count() }})</a></li>
    <li class="{{ Request::is('admin/confessions/index/scheduled') ? 'active' : '' }}" role="presentation"><a href="/admin/confessions/index/scheduled">Scheduled ({{ \NUSWhispers\Models\Confession::scheduled()->count() }})</a></li>
    <li class="{{ Request::is('admin/confessions/index/approved') ? 'active' : '' }}" role="presentation"><a href="/admin/confessions/index/approved">Approved</a></li>
    <li class="{{ Request::is('admin/confessions/index/rejected') ? 'active' : '' }}" role="presentation"><a href="/admin/confessions/index/rejected">Rejected</a></li>
  </ul>

  <div class="search-filters">
    <div class="form-group">
      <?php echo Form::select('category', array_flip($categoryOptions), Request::input('category'), ['class' => 'input-sm form-control']) ?>
      <div class="date-range">
        <i class="typcn typcn-calendar-outline"></i>
        <span>Anytime</span> <strong class="caret"></strong>
        <?php echo Form::hidden('start', Request::input('start')) ?>
        <?php echo Form::hidden('end', Request::input('end')) ?>
        <div class="tz" style="display:none"><?php echo date('Z') ?></div>
      </div>
      <a style="display: none" class="clear-dates" href="#" title="Clear date filter"><i class="typcn typcn-delete"></i></a>
    </div>
    <button class="btn btn-primary btn-sm btn-filter" type="submit">Filter</button>
    <?php echo str_replace('pagination', 'pagination pagination-sm', $confessions->render()); ?>
  </div>

  <div id="post-list" class="post-list">
    @foreach ($confessions as $confession)
      @include('admin.confessions.item', ['confession' => $confession])
    @endforeach
  </div>

  <div class="search-filters">
    <?php echo str_replace('pagination', 'pagination pagination-sm', $confessions->render()); ?>
  </div>

</form><!-- #confessions-search-form -->
@endsection
