@php
$request = request();
$url = $request->fullUrlWithQuery(
array_merge($request->query(), ['status' => $status])
);
@endphp

<li class="nav-item">
    <a class="nav-link {{ request('status', 'pending') === $status ? 'active' : '' }}" href="{{ $url }}">
        {{ $slot }}
    </a>
</li>
