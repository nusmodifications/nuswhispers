@inject('confessionService', 'NUSWhispers\Services\ConfessionService')

<div id="confession-{{ $confession->confession_id }}" class="confession bg-white px-3 py-2 mb-3 rounded-0">
    <div class="d-flex border-bottom border-light py-2 align-items-center">
        <div class="text-muted text-xs flex-grow-1">
            <span class="typcn typcn-watch"></span>
            Posted
            <time class="mr-1" datetime="{{ $confession->created_at->toW3cString() }}" title="{{ $confession->created_at->toCookieString() }}">{{
                $confession->created_at->diffForHumans() }}
            </time>
            @if ($confession->status === 'Featured')
            <span class="badge badge-success">Featured</span>
            @endif
            @if ($confession->status === 'Scheduled')
            @php $queue = $confession->queue->first() @endphp
            <span class="badge badge-warning">{{ $queue->status_after }}: {{
                $queue->update_status_at->diffForHumans() }}</span>
            @endif
            @if ($confession->status === 'Approved')
            <span class="badge badge-info">Approved</span>
            @endif
            @if ($confession->status === 'Rejected')
            <span class="badge badge-danger">Rejected</span>
            @endif
        </div>
        <div class="post-actions">
            @if ($hasPageToken)
            @if ($confession->status !== 'Featured')
            <div class="btn-group">
                <a class="btn btn-sm btn-primary" href="/admin/confessions/feature/{{ $confession->confession_id }}">
                    Feature
                </a>
                <button type="button" class="btn btn-sm btn-primary dropdown-toggle dropdown-toggle-split" data-toggle="dropdown"
                    aria-haspopup="true" aria-expanded="false">
                    <span class="sr-only">Toggle Dropdown</span>
                </button>
                <div class="dropdown-menu dropdown-menu-right">
                    @for ($i = 1; $i < 13; $i++) <a class="dropdown-item" href="/admin/confessions/feature/{{ $confession->confession_id }}/{{ $i }}">
                        Feature in {{ $i }} {{ Str::plural('hour', $i) }}
                        </a>
                        @endfor
                </div>
            </div>
            @else
            <a class="btn btn-secondary btn-sm" href="/admin/confessions/unfeature/{{ $confession->confession_id }}">
                Remove from Featured
            </a>
            @endif
            @if ($confession->status !== 'Approved' && $confession->status !== 'Featured')
            <div class="btn-group">
                <a class="btn btn-sm btn-primary" href="/admin/confessions/approve/{{ $confession->confession_id }}">
                    Approve
                </a>
                <button type="button" class="btn btn-sm btn-primary dropdown-toggle dropdown-toggle-split" data-toggle="dropdown"
                    aria-haspopup="true" aria-expanded="false">
                    <span class="sr-only">Toggle Dropdown</span>
                </button>
                <div class="dropdown-menu dropdown-menu-right">
                    @for ($i = 1; $i < 13; $i++) <a class="dropdown-item" href="/admin/confessions/approve/{{ $confession->confession_id }}/{{ $i }}">
                        Approve in {{ $i }} {{ Str::plural('hour', $i) }}
                        </a>
                        @endfor
                </div>
            </div>
            @endif
            @if ($confession->status !== 'Rejected')
            <a class="btn btn-secondary btn-sm" href="/admin/confessions/reject/{{ $confession->confession_id }}">
                Reject
            </a>
            @endif
            @endif
            <a class="btn btn-secondary btn-sm" href="/admin/confessions/edit/{{ $confession->confession_id }}">
                Edit
            </a>
            <a class="btn btn-sm btn-danger" href="/admin/confessions/delete/{{ $confession->confession_id }}">
                Delete
            </a>
        </div>
    </div>
    <div class="confession-content my-3">#{{ $confession->confession_id }}: {!! $confession->getFormattedContent() !!}</div>
    @if ($confession->images)
    <div class="my-3"><img class="mw-100" src="{{ $confession->images }}"></div>
    @endif
    <div class="d-flex border-top border-light py-3 align-items-center">
        <div class="text-muted flex-grow-1">
            @if ($confession->categories()->count() > 0)
            <strong>Categories:</strong>
            {!!
            $confession->categories->map(function ($cat) {
            return '<a href="/admin/confessions?category=' . $cat->confession_category_id . '">' .
                $cat->confession_category . '</a>';
            })->implode(', ')
            !!}
            @else
            &nbsp;
            @endif
        </div>
        <div>
            <a href="/admin/confessions/edit/{{ $confession->confession_id }}#comments" title="{{ $confession->moderatorComments()->count() }} Comment(s)">
                <span class="typcn typcn-messages"></span> {{ $confession->moderatorComments()->count() }}
            </a>
            @if ($confession->fingerprint)
            &nbsp;
            <a title="Featured confessions by the same fingerprint" href="/admin/confessions?status=featured&fingerprint={{ urlencode($confession->fingerprint) }}">
                <span class="typcn typcn-pin"></span>
                {{ $confessionService->countByFingerprint($confession, 'Featured') }}
            </a>
            &nbsp;
            <a title="Approved confessions by the same fingerprint" href="/admin/confessions?status=approved&fingerprint={{ urlencode($confession->fingerprint) }}">
                <span class="typcn typcn-arrow-up-thick"></span>
                {{ $confessionService->countByFingerprint($confession, 'Approved') }}
            </a>
            &nbsp;
            <a title="Pending confessions by the same fingerprint" href="/admin/confessions?status=pending&fingerprint={{ urlencode($confession->fingerprint) }}">
                <span class="typcn typcn-media-record"></span>
                {{ $confessionService->countByFingerprint($confession, 'Pending') }}
            </a>
            &nbsp;
            <a title="Rejected confessions by the same fingerprint" href="/admin/confessions?status=rejected&fingerprint={{ urlencode($confession->fingerprint) }}">
                <span class="typcn typcn-arrow-down-thick"></span>
                {{ $confessionService->countByFingerprint($confession, 'Rejected') }}
            </a>
            @endif
        </div>
    </div>
</div>
