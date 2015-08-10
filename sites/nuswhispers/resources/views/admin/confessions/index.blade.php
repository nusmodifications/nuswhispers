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
    <li class="{{ Request::is('admin/confessions/index/scheduled') ? 'active' : '' }}" role="presentation"><a href="/admin/confessions/index/scheduled">Scheduled ({{ \App\Models\Confession::scheduled()->count() }})</a></li>
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
          @if ($confession->status == 'Scheduled')
          <?php $queue = $confession->queue()->get()->get(0) ?>
          <span class="label label-info">Scheduled: {{ $queue->update_status_at->diffForHumans() }}</span>
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
          <div class="btn-group">
            <a class="btn btn-sm btn-primary" href="/admin/confessions/feature/{{ $confession->confession_id }}">
              Feature
            </a>
            <button type="button" class="btn btn-sm btn-primary dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
              <span class="caret"></span>
              <span class="sr-only">Toggle Dropdown</span>
            </button>
            <ul class="dropdown-menu" role="menu">
              <li><a href="/admin/confessions/feature/{{ $confession->confession_id }}/1">Feature in 1 hour</a></li>
              <li><a href="/admin/confessions/feature/{{ $confession->confession_id }}/2">Feature in 2 hours</a></li>
              <li><a href="/admin/confessions/feature/{{ $confession->confession_id }}/3">Feature in 3 hours</a></li>
              <li><a href="/admin/confessions/feature/{{ $confession->confession_id }}/4">Feature in 4 hour</a></li>
              <li><a href="/admin/confessions/feature/{{ $confession->confession_id }}/5">Feature in 5 hours</a></li>
              <li><a href="/admin/confessions/feature/{{ $confession->confession_id }}/6">Feature in 6 hours</a></li>
              <li><a href="/admin/confessions/feature/{{ $confession->confession_id }}/7">Feature in 7 hour</a></li>
              <li><a href="/admin/confessions/feature/{{ $confession->confession_id }}/8">Feature in 8 hours</a></li>
              <li><a href="/admin/confessions/feature/{{ $confession->confession_id }}/9">Feature in 9 hours</a></li>
              <li><a href="/admin/confessions/feature/{{ $confession->confession_id }}/10">Feature in 10 hour</a></li>
              <li><a href="/admin/confessions/feature/{{ $confession->confession_id }}/11">Feature in 11 hours</a></li>
              <li><a href="/admin/confessions/feature/{{ $confession->confession_id }}/12">Feature in 12 hours</a></li>
            </ul>
          </div>
          @else
          <a class="btn btn-sm" href="/admin/confessions/unfeature/{{ $confession->confession_id }}">
            Remove from Featured
          </a>
          @endif
          @if ($confession->status != 'Approved' && $confession->status != 'Featured')
          <div class="btn-group">
            <a class="btn btn-sm btn-primary" href="/admin/confessions/approve/{{ $confession->confession_id }}">
              Approve
            </a>
            <button type="button" class="btn btn-sm btn-primary dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
              <span class="caret"></span>
              <span class="sr-only">Toggle Dropdown</span>
            </button>
            <ul class="dropdown-menu" role="menu">
              <li><a href="/admin/confessions/approve/{{ $confession->confession_id }}/1">Approve in 1 hour</a></li>
              <li><a href="/admin/confessions/approve/{{ $confession->confession_id }}/2">Approve in 2 hours</a></li>
              <li><a href="/admin/confessions/approve/{{ $confession->confession_id }}/3">Approve in 3 hours</a></li>
              <li><a href="/admin/confessions/approve/{{ $confession->confession_id }}/4">Approve in 4 hours</a></li>
              <li><a href="/admin/confessions/approve/{{ $confession->confession_id }}/5">Approve in 5 hours</a></li>
              <li><a href="/admin/confessions/approve/{{ $confession->confession_id }}/6">Approve in 6 hours</a></li>
              <li><a href="/admin/confessions/approve/{{ $confession->confession_id }}/7">Approve in 7 hours</a></li>
              <li><a href="/admin/confessions/approve/{{ $confession->confession_id }}/8">Approve in 8 hours</a></li>
              <li><a href="/admin/confessions/approve/{{ $confession->confession_id }}/9">Approve in 9 hours</a></li>
              <li><a href="/admin/confessions/approve/{{ $confession->confession_id }}/10">Approve in 10 hours</a></li>
              <li><a href="/admin/confessions/approve/{{ $confession->confession_id }}/11">Approve in 11 hours</a></li>
              <li><a href="/admin/confessions/approve/{{ $confession->confession_id }}/12">Approve in 12 hours</a></li>
            </ul>
          </div>
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
      <div class="post-content">#{{ $confession->confession_id }}:&nbsp;<?php echo $confession->getFormattedContent() ?></div>
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
        @else
        &nbsp;
        @endif
        <a class="post-comments" href="/admin/confessions/edit/{{ $confession->confession_id }}#comments" title="{{ $confession->moderatorComments()->count() }} Comment(s)">
          <span class="typcn typcn-messages"></span> {{ $confession->moderatorComments()->count() }}
        </a>
      </div>
    </div>
    @endforeach
  </div>

  <div class="search-filters">
    <?php echo str_replace('pagination', 'pagination pagination-sm', $confessions->render()); ?>
  </div>

</form><!-- #confessions-search-form -->
@endsection
