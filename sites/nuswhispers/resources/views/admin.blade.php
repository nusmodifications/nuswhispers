<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>NUSWhispers &ndash; Admin</title>

  <script src="//use.typekit.net/zog5enw.js"></script>
  <script>try{Typekit.load({ async: true });}catch(e){}</script>
  <link rel="stylesheet" href="/assets/css/admin.css">
  <link rel="stylesheet" href="/assets/css/daterangepicker-bs3.css">
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
        <li class="{{ Request::is('admin/profile') ? 'active' : '' }}"><a href="/admin/profile"><span class="typcn typcn-user"></span>My Profile</a></li>
      </ul>
      <hr>
      <h2 class="nav-section-label">Confessions Management</h2>
      <ul class="nav main-nav">
        <li class="{{ Request::is('admin/confessions/index/pending') || Request::is('admin/confessions') ? 'active' : '' }}">
          <a href="/admin/confessions">
            <span class="typcn typcn-warning"></span>Pending
            <span class="badge">{{ \App\Models\Confession::pending()->count() }}</span>
          </a>
        </li>
        <li class="{{ Request::is('admin/confessions/index/approved') ? 'active' : '' }}">
          <a href="/admin/confessions/index/approved">
              <span class="typcn typcn-tick"></span>Approved
          </a>
        </li>
      </ul>
      <hr>
      <ul class="nav main-nav">
        @if (\Auth::user()->role == 'Administrator')
        <li class="{{ Request::is('admin/users') ? 'active' : '' }}"><a href="/admin/users"><span class="typcn typcn-group"></span>User Management</a></li>
        @endif
        <li><a href="/auth/logout"><span class="typcn typcn-eject"></span>Logout</a></li>
      </ul>
    </nav><!-- #sidebar-wrapper -->
    <div id="content-wrapper">

      <div id="content" class="container">
        @yield('content')
      </div><!-- #content -->
    </div><!-- #content-wrapper -->
  </div>

  <!-- Scripts -->
  <script src="//cdnjs.cloudflare.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
  <script src="//cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.1/js/bootstrap.min.js"></script>
  <script src="/assets/js/moment.min.js"></script>
  <script src="/assets/js/daterangepicker.min.js"></script>
  <script src="/assets/js/admin.js"></script>
</body>
</html>
