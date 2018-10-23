<li class="{{ isset($is_active) ? $is_active(request()) : request()->is($url) ? 'active' : '' }}">
    <a href="{{ url($url) }}">{{ $slot }}</a>
</li>
