@php use Illuminate\Http\Request; use NUSWhispers\Models\Confession; use
NUSWhispers\Models\User; @endphp

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <title>@yield('title') &ndash; NUSWhispers Admin</title>
        <link rel="icon" href="favicon.ico" />
        <link rel="stylesheet" href="{{ mix('admin/admin.css') }}" />
    </head>

    <body>
        <div class="container-fluid">
            <div class="row">
                <nav class="sidebar">
                    <a class="whispers" href="{{ route('admin.index') }}"
                        >NUS<span>Whispers</span>Admin</a
                    >
                    <hr />
                    <ul class="nav flex-column sidebar-nav">
                        @component('link', ['url' => 'admin/profile'])
                        <span class="typcn typcn-user"></span
                        >My Profile @endcomponent
                    </ul>
                    <hr />
                    <ul class="nav flex-column sidebar-nav">
                        <li class="sidebar-nav-category">Manage Confessions</li>

                        @component('link', [ 'url' => 'admin/confessions',
                        'is_active' => function (Request $request) { return
                        $request->is('admin/confessions') &&
                        $request->query('status', 'pending') === 'pending'; }])
                        <span class="typcn typcn-warning"></span
                        >Pending
                        <span
                            class="badge badge-warning mt-2 float-right"
                            >{{ Confession::pending()->count() }}</span
                        >
                        @endcomponent @component('link', [ 'url' =>
                        'admin/confessions?status=approved', 'is_active' =>
                        function (Request $request) { return
                        $request->is('admin/confessions') &&
                        $request->query('status', 'pending') === 'approved'; }])
                        <span class="typcn typcn-tick"></span
                        >Approved @endcomponent
                    </ul>
                    <hr />
                    <ul class="nav flex-column sidebar-nav">
                        @if (auth()->user()->role === 'Administrator')
                        @component('link', ['url' => 'admin/api-keys'])
                        <span class="typcn typcn-key"></span
                        >API Keys @endcomponent @endif @can('manage',
                        User::class) @component('link', ['url' =>
                        'admin/users'])
                        <span class="typcn typcn-group"></span
                        >Users @endcomponent @endcan @if (auth()->user()->role
                        === 'Administrator') @component('link', ['url' =>
                        'admin/settings'])
                        <span class="typcn typcn-spanner"></span
                        >Settings @endcomponent @endif @component('link', ['url'
                        => 'logout'])
                        <span class="typcn typcn-eject"></span
                        >Logout @endcomponent
                    </ul>
                </nav>
                <main class="main" role="main">
                    @include('message') @yield('content')
                </main>
            </div>
        </div>
        <script src="{{ mix('admin/manifest.js') }}"></script>
        <script src="{{ mix('admin/vendor.js') }}"></script>
        <script src="{{ mix('admin/admin.js') }}"></script>
        @stack('scripts')
    </body>
</html>
