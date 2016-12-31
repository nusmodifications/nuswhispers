<?php
if ($confession->status == 'Scheduled') {
  $queue = $confession->queue()->get()->get(0);
}
?>

@extends('admin')

@section('content')
  <div class="page-header">
    <h1 class="page-title"><span class="typcn typcn-heart"></span>Edit Confession #{{ $confession->confession_id }} <small><a href="/admin/confessions/">(Back to Confessions Listing)</a></small></h1>
  </div>

  @include('message')

  <?php echo \Form::model($confession, ['url' => url('admin/confessions/edit', $confession->confession_id), 'class' => 'form row']) ?>

  <div class="col-md-12 col-lg-8 edit-confessions-col">
    <div class="panel panel-default">
      <div class="panel-heading">Confession Content</div>
      <div class="panel-body {{$errors->first('content') ? 'has-error' : ''}}">
        <?php echo \Form::textarea('content', null, ['id' => '', 'class' => 'form-control']) ?>
        @if ($errors->first('content'))
        <p class="alert alert-danger">{{$errors->first('content')}}</p>
        @endif
      </div>
    </div>

    @if (env('MANUAL_MODE', false) && ($confession->status == 'Approved' || $confession->status == 'Featured'))
    <div class="panel panel-default">
      <div class="panel-heading">Copy and paste in Facebook!</div>
      <div class="panel-body">
      <?php echo \Form::textarea('fb_content', $confession->getFacebookMessage(), ['id' => '', 'class' => 'form-control', 'readonly' => 'readonly', 'onfocus' => 'this.select()']) ?>
      </div>
    </div>
    @endif

    <div class="panel panel-default panel-photo">
      <div class="panel-heading">Photo</div>
      <div class="panel-body">
        <p><?php echo \Form::text('images', null, ['id' => '', 'class' => 'form-control', 'placeholder' => 'URL to photo']) ?></p>
        @if ($confession->images)
        <img src="{{$confession->images}}" alt="Confession Photo">
        @endif
      </div>
    </div>

    <a name="comments"></a>
    <div class="panel panel-default">
      <div class="panel-heading">Moderator Comments</div>
      <div class="panel-body">
        @if ($confession->moderatorComments()->count() == 0)
        <p class="no-comments">No comments available.</p>
        @else
          @foreach ($confession->moderatorComments()->with('user')->orderBy('created_at', 'desc')->get() as $comment)
          <div class="comment" id="comment-{{ $comment->comment_id }}">
            <div class="comment-meta">
              <p><span class="comment-author">{{!empty($comment->user->name) ? $comment->user->name : $comment->user->email}}</span> commented {{$comment->created_at->diffForHumans()}}</p>
              @if (\Auth::user()->user_id == $comment->user_id || \Auth::user()->role == 'Administrator')
              <a class="delete-comment" href="/admin/confessions/comments/delete/{{ $comment->comment_id }}" title="Delete Comment"><span class="typcn typcn-times"></span></a>
              @endif
            </div>
            <div class="comment-content">
              {{$comment->content}}
            </div>
          </div>
          @endforeach
        @endif
      </div>
    </div>
    <div class="panel panel-default">
      <div class="panel-heading">Leave a Comment</div>
      <div class="panel-body comments-form {{$errors->first('comment') ? 'has-error' : ''}}">
        <p><?php echo \Form::textarea('comment', null, ['class' => 'form-control', 'placeholder' => 'Leave a comment here for other moderators to see.', 'rows' => 5]) ?></p>
        <p><?php echo \Form::submit('Post Comment', ['name' => 'action', 'class' => 'btn btn-primary']) ?></p>
        @if ($errors->first('comment'))
          <p class="alert alert-danger">{{$errors->first('comment')}}</p>
        @endif
      </div>
    </div>
  </div>

  <div class="col-md-12 col-lg-4">
    <div class="panel panel-default panel-status">
      <div class="panel-heading">Status</div>
      <div class="panel-body">
        <p>
        <?php
        $status = $confession->status == 'Scheduled' ? $queue->status_after : $confession->status;
        echo \Form::select('status', array_combine(\NUSWhispers\Models\Confession::statuses(), \NUSWhispers\Models\Confession::statuses()), $status, ['class' => 'form-control'])
        ?>
        </p>
        <p style="text-align:center; color: #999">Latest status updated {{$confession->status_updated_at->diffForHumans()}}.</p>
        @if (in_array($confession->status, ['Pending', 'Rejected', 'Scheduled']))
        <div class="schedule-confession" @if ($confession->status == 'Pending' || $confession->status == 'Rejected')style="display: none" @endif>
          @if ($confession->status == 'Pending')
          Schedule confession to go public:
          @else
          Currently scheduled to go public at:
          @endif
          <div class="schedule-date">
            <i class="glyphicon glyphicon-calendar fa fa-calendar"></i>
            <span>Now</span> <strong class="caret"></strong>
            <div class="tz" style="display:none"><?php echo date('Z') ?></div>
            <?php
            if (Request::input('schedule') != '')
              $schedule = Request::input('schedule');
            elseif (isset($queue))
              $schedule = $queue->update_status_at->format('c');
            else
              $schedule = '';
            echo Form::hidden('schedule', $schedule)
            ?>
          </div>
        </div>
        @endif

        <p>
          <?php echo \Form::label('fb_post_id', 'Facebook #ID:') ?>
          <?php echo \Form::text('fb_post_id', null, ['class' => 'form-control']) ?>
        </p>

        <hr>
        <?php echo \Form::submit('Update Confession', ['name' => 'action', 'class' => 'btn btn-block btn-primary']) ?>
      </div>
    </div>
    <div class="panel panel-default">
      <div class="panel-heading">Status History (Last 5)</div>
      <div class="panel-body">
        <ul class="status-history-list">
          @foreach ($confession->logs()->with('user')->orderBy('created_on', 'desc')->take(5)->get() as $log)
          <li>
            Changed from <strong>{{$log->status_before}}</strong> to <strong>{{$log->status_after}}</strong>
            <span class="status-meta">
              {{$log->created_on->diffForHumans()}} by {{!empty($log->user->name) ? $log->user->name : $log->user->email}}
            </span>
          </li>
          @endforeach
        </ul>
      </div>
    </div>
    <div class="panel panel-default">
      <div class="panel-heading">Categories</div>
      <div class="panel-body">
        <?php $categories = $confession->categories()->get()->keyBy('confession_category_id'); ?>
        @foreach (\NUSWhispers\Models\Category::categoryAsc()->get() as $cat)
        <div class="checkbox">
          <label>
          <?php echo \Form::checkbox('categories[]', $cat->confession_category_id, isset($categories[$cat->confession_category_id])) ?>
          {{$cat->confession_category}}
          </label>
        </div>
        @endforeach
      </div>
    </div>
  </div>

  <?php echo \Form::close() ?>

@endsection
