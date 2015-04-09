@extends('admin')

@section('content')
  <div class="page-header">
    <h1 class="page-title"><span class="typcn typcn-heart"></span>Edit Confession #{{ $confession->confession_id }} <small><a href="/admin/confessions/">(Back to Confessions Listing)</a></small></h1>
  </div>

  @include('message')

  <?php echo \Form::model($confession, ['route' => ['admin.confessions.edit', $confession->confession_id], 'class' => 'form row']) ?>

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

    @if ($confession->images)
    <div class="panel panel-default panel-photo">
      <div class="panel-heading">Photo</div>
      <div class="panel-body">
        <img src="{{$confession->images}}" alt="Confession Photo">
      </div>
    </div>
    @endif

    <!--<div class="panel panel-default">
      <div class="panel-heading">Admin Comments</div>
      <div class="panel-body">
      </div>
    </div>-->
  </div>

  <div class="col-md-12 col-lg-4">
    <div class="panel panel-default panel-status">
      <div class="panel-heading">Status</div>
      <div class="panel-body">
        <p><?php echo \Form::select('status', array_combine($confession->statuses(), $confession->statuses()), null, ['class' => 'form-control']) ?>
        </p>
        <p style="text-align:center; color: #999">Latest status updated {{$confession->status_updated_at->diffForHumans()}}.</p>
        @if ($confession->fb_post_id)
        <hr>
        <p>
          <?php echo \Form::label('fb_post_id', 'Facebook #ID:') ?>
          <?php echo \Form::text('fb_post_id', null, ['class' => 'form-control', 'disabled' => 'disabled']) ?>
        </p>
        @endif
        <hr>
        <?php echo \Form::submit('Update Confession', ['class' => 'btn btn-block btn-primary']) ?>
      </div>
    </div>
    <div class="panel panel-default">
      <div class="panel-heading">Status History</div>
      <div class="panel-body">
        <ul class="status-history-list">
          @foreach ($confession->logs()->with('user')->orderBy('created_on', 'desc')->get() as $log)
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
        @foreach (\App\Models\Category::categoryAsc()->get() as $cat)
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
