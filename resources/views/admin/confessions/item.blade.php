@inject('confessionService', 'NUSWhispers\Services\ConfessionService')

<div id="confession-{{ $confession->confession_id }}" class="post">
  <div class="post-header">
    <div class="post-meta">
      <span class="typcn typcn-watch"></span>
      Posted <time datetime="{{ $confession->created_at->toW3cString() }}" title="{{ $confession->created_at->toCookieString() }}">{{ $confession->created_at->diffForHumans() }}</time>
      @if ($confession->status === 'Featured')
        <span class="label label-success">Featured</span>
      @endif
      @if ($confession->status === 'Scheduled')
        <span class="label label-info">Scheduled: {{ $confession->queue()->first()->update_status_at->diffForHumans() }}</span>
      @endif
      @if ($confession->status === 'Approved')
        <span class="label label-primary">Approved</span>
      @endif
      @if ($confession->status === 'Rejected')
        <span class="label label-danger">Rejected</span>
      @endif
    </div>
    <div class="post-actions">
      @if ($hasPageToken)
        @if ($confession->status !== 'Featured')
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
        @if ($confession->status !== 'Approved' && $confession->status !== 'Featured')
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
        @if ($confession->status !== 'Rejected')
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
      {!!
        $confession->categories->map(function ($cat) {
          return '<a href="/admin/confessions?category=' . $cat->confession_category_id . '">' . $cat->confession_category . '</a>';
        })->implode(', ')
      !!}
    @else
      &nbsp;
    @endif
    <p class="post-comments">
      <a href="/admin/confessions/edit/{{ $confession->confession_id }}#comments" title="{{ $confession->moderatorComments()->count() }} Comment(s)">
        <span class="typcn typcn-messages"></span> {{ $confession->moderatorComments()->count() }}
      </a>
      @if ($confession->fingerprint)
        &nbsp;
        <a title="Featured confessions by the same fingerprint" href="/admin/confessions/index/featured?fingerprint={{ urlencode($confession->fingerprint) }}">
          <span class="typcn typcn-pin"></span>
          {{ $confessionService->countByFingerprint($confession, 'Featured') }}
        </a>
        &nbsp;
        <a title="Approved confessions by the same fingerprint" href="/admin/confessions/index/approved?fingerprint={{ urlencode($confession->fingerprint) }}">
          <span class="typcn typcn-arrow-up-thick"></span>
          {{ $confessionService->countByFingerprint($confession, 'Approved') }}
        </a>
        &nbsp;
        <a title="Pending confessions by the same fingerprint" href="/admin/confessions/index/pending?fingerprint={{ urlencode($confession->fingerprint) }}">
          <span class="typcn typcn-media-record"></span>
          {{ $confessionService->countByFingerprint($confession, 'Pending') }}
        </a>
        &nbsp;
        <a title="Rejected confessions by the same fingerprint" href="/admin/confessions/index/rejected?fingerprint={{ urlencode($confession->fingerprint) }}">
          <span class="typcn typcn-arrow-down-thick"></span>
          {{ $confessionService->countByFingerprint($confession, 'Rejected') }}
        </a>
      @endif
    </p>
  </div>
</div>
