<div class="border-bottom pb-3 mb-3">
    Changed from <b>{{ $log->status_before }}</b> to <b>{{ $log->status_after }}</b>
    <div class="text-muted">
        {{ $log->created_on->diffForHumans() }} by {{ $log->user->name ? $log->user->name : $log->user->email }}
    </div>
</div>
