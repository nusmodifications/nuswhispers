@php
use NUSWhispers\Models\Confession;
@endphp

@extends('layouts.admin')

@section('title', 'Manage Confessions')

@section('content')
<form method="get">
    <div class="page-header">
        <h1>
            <span class="typcn typcn-heart"></span> Manage Confessions
        </h1>
        <div class="form-inline">
            <input class="form-control form-control-sm mr-sm-2" type="search" name="q" size="30" value="{{ request('q') }}"
                placeholder="Search" aria-label="Search">
            <button class="btn btn-sm btn-primary">Search</button>
        </div>
    </div>

    @if (! $hasPageToken)
    <div class="alert alert-danger">
        <strong>Warning:</strong> You have not <a href="{{ route('admin.profile.index') }}">connected your Facebook
            account</a>.
        You will not be able to approve any confessions until you do so.
    </div>
    @endif

    <ul class="nav nav-tabs">
        @component('admin.confessions.tab', ['status' => 'all'])
        All
        @endcomponent

        @component('admin.confessions.tab', ['status' => 'featured'])
        Featured
        @endcomponent

        @component('admin.confessions.tab', ['status' => 'pending'])
        Pending ({{ Confession::pending()->count() }})
        @endcomponent

        @component('admin.confessions.tab', ['status' => 'scheduled'])
        Scheduled ({{ Confession::scheduled()->count() }})
        @endcomponent

        @component('admin.confessions.tab', ['status' => 'approved'])
        Approved
        @endcomponent

        @component('admin.confessions.tab', ['status' => 'rejected'])
        Rejected
        @endcomponent
    </ul>

    <div class="d-flex my-4">
        <div class="form-inline flex-grow-1">
            <select name="category" class="form-control form-control-sm custom-select">
                @foreach ($categories as $label => $value)
                <option value="{{ $value }}" {{ request('category') === $value ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>

            <div class="d-flex date-picker mx-2 form-control form-control-sm custom-select">
                <i class="typcn typcn-calendar-outline"></i>
                <div class="label">Anytime</div>
                <input type="hidden" name="start" value="{{ request('start') }}">
                <input type="hidden" name="end" value="{{ request('end') }}">
            </div>

            <button class="clear btn btn-sm btn-secondary mr-2">Clear Dates</button>

            <button class="btn btn-sm btn-primary mx-2" type="submit">Filter</button>
        </div>
        {{ $confessions->links('admin.pagination') }}
    </div>

    @foreach ($confessions as $confession)
    @include('admin.confessions.item', ['confession' => $confession])
    @endforeach

    <div class="d-flex my-4 justify-content-end">
        {{ $confessions->links('admin.pagination') }}
    </div>
</form>
@endsection

@push('scripts')
<script src="{{ mix('assets/admin/confessions/index.js') }}"></script>
@endpush
