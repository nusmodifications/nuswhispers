@extends('layouts.admin')

@section('title', 'Settings')

@section('content')
<div class="page-header">
    <h1>
        <span class="typcn typcn-spanner"></span>
        Settings
    </h1>
</div>

<form method="post">
    @csrf

    <div class="form-group mb-3">
        <label for="word_blacklist">Word Blacklist</label>
        <textarea class="form-control" name="word_blacklist" placeholder="List of words separated by commas" rows="5">{{ array_get($settings, 'word_blacklist', '') }}</textarea>
        <small class="form-text">Confessions with content within the word blacklist will be automatically rejected
            upon submission.</small>
    </div>

    <div class="form-group mb-3">
        <label>Filter Errant Fingerprints</label>
        <div class="form-inline text-dark">
            Auto-reject confessors with a net number of
            <input name="rejection_net_score" class="form-control mx-2" type="number" style="width: 5em" value="{{ array_get($settings, 'rejection_net_score', 5) }}" />
            rejected confessions for the past
            <input name="rejection_decay" class="form-control mx-2" type="number" style="width: 5em" value="{{ array_get($settings, 'rejection_decay', 30) }}" />
            days.
        </div>
        <small class="form-text">Set 0 to any of the fields above to disable the filter.</small>
    </div>

    <hr>

    <button class="btn btn-primary" type="submit">Submit</button>
</form>
@endsection
