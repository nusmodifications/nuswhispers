<div class="card border-light mb-3">
    <div class="card-header d-flex align-items-center">
        <div class="flex-grow-1">
            {{ $comment->user->name ? $comment->user->name : $comment->user->email }} <span class="text-muted">commented
                {{
                $comment->created_at->diffForHumans() }}</span>
        </div>
        <a class="btn btn-sm btn-outline-danger" href="{{ route('admin.confessions.comments.delete', $comment->getKey()) }}">Delete</a>
    </div>
    <div class="card-body">
        <div class="content">{{ $comment->content }}</div>
    </div>
</div>
