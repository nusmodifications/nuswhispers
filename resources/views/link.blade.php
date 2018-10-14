<li class="{{ request()->is($url) ? 'active' : '' }}">
    <a href="{{ url($url) }}">{{ $slot }}</a>
</li>
