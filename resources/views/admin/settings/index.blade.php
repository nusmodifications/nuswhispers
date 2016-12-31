@extends('admin')

@section('content')
  <div class="page-header">
    <h1 class="page-title"><span class="typcn typcn-spanner"></span>Settings</h1>
  </div>

  @include('message')

  <form method="post">
    <div class="admin-content-wrapper">
      <h2>Confession Settings</h2>
      <div class="form-group">
        <label for="word_blacklist">Word Blacklist</label>
        <textarea class="form-control" name="word_blacklist" placeholder="List of words separated by commas" rows="5">{{ array_get($settings, 'word_blacklist', '') }}</textarea>
        <p class="help-block">Confessions with content within the word blacklist will be automatically rejected upon submission.</p>
      </div>
      <p class="form-actions">
        <button class="btn btn-primary" type="submit">Submit</button>
      </p>
    </div>
  </form>
@endsection
