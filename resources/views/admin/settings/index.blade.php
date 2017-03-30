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
      <hr />
      <div class="form-group">
        <label>Filter Errant Fingerprints</label>
        <p class="form-inline">
          Auto-reject confessors with a net number of
            <input name="rejection_net_score" class="form-control" type="number" style="width: 5em; margin: 0 1em;" value="{{ array_get($settings, 'rejection_net_score', 5) }}" />
          rejected confessions for the past
            <input name="rejection_decay" class="form-control" type="number" style="width: 5em; margin: 0 1em;" value="{{ array_get($settings, 'rejection_decay', 30) }}" />
          days.
        </p>
        <p class="help-block">Set 0 to any of the fields above to disable the filter.</p>
      </div>
      <hr />
      <p class="form-actions">
        <button class="btn btn-primary" type="submit">Submit</button>
      </p>
    </div>
  </form>
@endsection
