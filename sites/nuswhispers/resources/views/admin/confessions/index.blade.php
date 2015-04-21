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
    <li class="{{ Request::is('admin/confessions') || Request::is('admin/confessions/index/pending') ? 'active' : '' }}" role="presentation"><a href="/admin/confessions/index/pending">Pending ({{ \App\Models\Confession::pending()->count() }})</a></li>
    <li class="{{ Request::is('admin/confessions/index/approved') ? 'active' : '' }}" role="presentation"><a href="/admin/confessions/index/approved">Approved</a></li>
    <li class="{{ Request::is('admin/confessions/index/rejected') ? 'active' : '' }}" role="presentation"><a href="/admin/confessions/index/rejected">Rejected</a></li>
  </ul>

  <div class="search-filters">
    <div class="form-group">
      <?php echo Form::select('category', array_flip($categoryOptions), Request::input('category'), ['class' => 'input-sm form-control']) ?>
      <div class="date-range">
        <i class="glyphicon glyphicon-calendar fa fa-calendar"></i>
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
    <div id="confession-{{ $confession->confession_id }}" class="post">
      <div class="post-header">
        <div class="post-meta">
          <span class="typcn typcn-watch"></span>
          Posted {{ $confession->created_at->diffForHumans() }}
          @if ($confession->status == 'Featured')
          <span class="label label-success">Featured</span>
          @endif
          @if ($confession->status == 'Approved')
          <span class="label label-primary">Approved</span>
          @endif
          @if ($confession->status == 'Rejected')
          <span class="label label-danger">Rejected</span>
          @endif
        </div>
        <div class="post-actions">
          @if ($hasPageToken)
          @if ($confession->status != 'Featured')
          <a class="btn btn-sm btn-primary" href="/admin/confessions/feature/{{ $confession->confession_id }}">
            Feature
          </a>
          @else
          <a class="btn btn-sm" href="/admin/confessions/unfeature/{{ $confession->confession_id }}">
            Remove from Featured
          </a>
          @endif
          @if ($confession->status != 'Approved' && $confession->status != 'Featured')
          <a class="btn btn-sm btn-primary" href="/admin/confessions/approve/{{ $confession->confession_id }}">
            Approve
          </a>
          @endif
          @if ($confession->status != 'Rejected')
          <a class="btn btn-sm" href="/admin/confessions/reject/{{ $confession->confession_id }}">
            Reject
          </a>
          @endif
          @endif
          <a class="btn btn-sm" href="/admin/confessions/edit/{{ $confession->confession_id }}">
            Edit
          </a>
          <a class="btn btn-sm btn-danger" href="/admin/confessions/delete/{{ $confession->confession_id }}">
            Delete
          </a>
        </div>
      </div>
      <div class="post-content">{{ $confession->content }}</div>
      @if ($confession->images)
      <div class="post-image"><img src="{{ $confession->images }}"></div>
      @endif
      <div class="post-footer">
        @if ($confession->categories()->count() > 0)
        <strong>Categories:</strong>
        <?php
        $categories = $confession->categories()->get();
        $formattedCategories = [];
        foreach ($categories as $cat)
          $formattedCategories[] = '<a href="/admin/confessions?category=' . $cat->confession_category_id . '">' . $cat->confession_category . '</a>';

        echo implode(', ', $formattedCategories);
        ?>
        @endif
      </div>
    </div>
    @endforeach
  </div>

  <div class="search-filters">
    <?php echo str_replace('pagination', 'pagination pagination-sm', $confessions->render()); ?>
  </div>

</form><!-- #confessions-search-form -->
@endsection
