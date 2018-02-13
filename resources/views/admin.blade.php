<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>NUSWhispers &ndash; Admin</title>

  <script src="//use.typekit.net/zog5enw.js"></script>
  <script>try{Typekit.load({ async: true });}catch(e){}</script>
  <link rel="stylesheet" href="{{ mix('css/admin.css') }}">
</head>
<body>

  <div id="wrapper">
    <nav id="sidebar-wrapper">
      <a class="whispers-brand navbar-brand" href="/#!home">
        <div class="whispers-logo"></div>
        <div class="whispers-logo-text">NUS<span class="highlight">Whispers</span>Admin</div>
      </a>
      <hr>
      <ul class="nav main-nav">
        <li class="{{ request()->is('admin/profile') ? 'active' : '' }}"><a href="/admin/profile"><span class="typcn typcn-user"></span>My Profile</a></li>
      </ul>
      <hr>
      <h2 class="nav-section-label">Confessions Management</h2>
      <ul class="nav main-nav">
        <li class="{{ request()->is('admin/confessions/index/pending') || request()->is('admin/confessions') ? 'active' : '' }}">
          <a href="/admin/confessions">
            <span class="typcn typcn-warning"></span>Pending
            <span class="badge">{{ \NUSWhispers\Models\Confession::pending()->count() }}</span>
          </a>
        </li>
        <li class="{{ request()->is('admin/confessions/index/approved') ? 'active' : '' }}">
          <a href="/admin/confessions/index/approved">
              <span class="typcn typcn-tick"></span>Approved
          </a>
        </li>
      </ul>
      <hr>
      <ul class="nav main-nav">
        <li class="{{ request()->is('admin/api-keys') ? 'active' : ''}}"><a href="/admin/api-keys"><span class="typcn typcn-key"></span>API Keys Management</a></li>
        @if (auth()->user()->role === 'Administrator')
        <li class="{{ request()->is('admin/users') ? 'active' : '' }}"><a href="/admin/users"><span class="typcn typcn-group"></span>User Management</a></li>
        <li class="{{ request()->is('admin/settings') ? 'active' : '' }}"><a href="/admin/settings"><span class="typcn typcn-spanner"></span>Settings</a></li>
        @endif
        <li><a href="/logout"><span class="typcn typcn-eject"></span>Logout</a></li>
      </ul>
    </nav><!-- #sidebar-wrapper -->
    <div id="content-wrapper">

      <div id="content" class="container">
        @yield('content')
      </div><!-- #content -->
    </div><!-- #content-wrapper -->
  </div>

  <!-- Scripts -->
  <script src="{{ mix('js/manifest.js') }}"></script>
  <script src="{{ mix('js/vendor.js') }}"></script>
  <script src="{{ mix('js/admin.js') }}"></script>
</body>
</html>
