@php
use NUSWhispers\Models\Confession;

$queue = $confession->queue->first();
$nextStatus = $queue ? $queue->status_after : $confession->status;
@endphp

@extends('layouts.admin')

@section('title', 'Edit Confession #' . $confession->getKey())

@section('content')
<div class="page-header">
    <h1>
        <span class="typcn typcn-heart"></span>
        Edit Confession #{{ $confession->getKey() }}
    </h1>
    <div>
        <a class="btn btn-sm btn-outline-primary" href="{{ url('admin/confessions') }}">
            Back to Confessions
        </a>
    </div>
</div>

<form class="row" method="POST" action="{{ route('admin.confessions.update', $confession) }}">
    @csrf

    <div class="col-12 col-lg-8">
        <div class="card mb-3">
            <div class="card-header">Content</div>
            <div class="card-body">
                <textarea rows="10" name="content" class="form-control {{ $errors->first('content') ? 'is-invalid' : '' }}"
                    required>{{ old('content') ?? $confession->content }}</textarea>
                <div class="invalid-feedback">{{ $errors->first('content') }}</div>
            </div>
        </div>
        @if (config('app.manual_mode') && in_array($confession->status, ['Approved', 'Featured'], true))
        <div class="card mb-3">
            <div class="card-header">Copy and paste in Facebook!</div>
            <div class="card-body">
                <textarea rows="10" name="fb_content" readonly onfocus="this.select()" class="form-control" required>{{ $confession->getFacebookMessage() }}</textarea>
            </div>
        </div>
        @endif
        <div class="card mb-3">
            <div class="card-header">Photo</div>
            @if ($confession->images)
            <div class="card-img-top"><img class="mw-100" src="{{ $confession->images }}" alt="Confession Photo"></div>
            @endif
            <div class="card-body">
                <input type="url" name="images" placeholder="URL to photo" class="form-control" value="{{ old('images') ?? $confession->images }}">
            </div>
        </div>
        <a name="comments"></a>
        <div class="card mb-3">
            <div class="card-header">Moderator Comments</div>
            <div class="card-body">
                @each('admin.confessions.comments.item', $confession->moderatorComments, 'comment',
                'admin.confessions.comments.empty')

                <hr>

                <textarea rows="5" name="comment" class="form-control {{ $errors->first('comment') ? 'is-invalid' : '' }}"
                    placeholder="Leave a comment here for other moderators to see."></textarea>
                <div class="invalid-feedback">{{ $errors->first('comment') }}</div>
                <div class="mt-3">
                    <button type="submit" name="action" class="btn btn-primary" value="post_comment">Post Comment</button>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-lg-4 pl-0">
        <div class="card mb-3">
            <div class="card-header">Status</div>
            <div class="card-body">
                <div class="form-group">
                    <select name="status" class="form-select custom-select">
                        @foreach (Confession::statuses() as $status)
                        @if ($status !== 'Scheduled')
                        <option value="{{ $status }}" {{ $nextStatus === $status ? 'selected' : '' }}>
                            {{ $status }}
                        </option>
                        @endif
                        @endforeach
                    </select>
                </div>

                <div class="scheduled-form-group mb-3 d-none">
                    Scheduled Date / Time
                    @php
                    if (! ($schedule = old('schedule')) && $queue) {
                    $schedule = $queue->update_status_at->timestamp;
                    }
                    @endphp
                    <div class="mt-2 d-flex date-picker form-control custom-select">
                        <i class="typcn typcn-calendar-outline"></i>
                        <div class="label"></div>
                        <input type="hidden" name="schedule" value="{{ $schedule }}">
                    </div>
                </div>

                <hr>

                <p class="text-muted text-center">Latest status updated {{
                    $confession->status_updated_at->diffForHumans() }}.</p>

                <hr>

                <div class="form-group">
                    <label for="fb_post_id">Facebook #ID</label>
                    <input type="text" class="form-control" id="fb_post_id" name="fb_post_id" value="{{ $confession->fb_post_id }}">
                </div>

                <button name="action" class="btn btn-block btn-primary" type="submit">
                    Update Confession
                </button>
            </div>
        </div>
        <div class="card mb-3">
            <div class="card-header">Status History (Last 5)</div>
            <div class="card-body">
                @each('admin.confessions.logs.item', $confession->logs, 'log', 'admin.confessions.logs.empty')
            </div>
        </div>
        <div class="card mb-3">
            <div class="card-header">Categories</div>
            <div class="card-body">
                @foreach ($categories as $category)
                @include('admin.confessions.category')
                @endforeach
            </div>
        </div>
    </div>
</form>
@endsection

@push('scripts')
<script src="{{ mix('js/confessions/edit.js') }}"></script>
@endpush
