<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>NUS Whispers &ndash; Admin</title>

  <link href="http://fonts.googleapis.com/css?family=Montserrat:400,700" rel="stylesheet" type="text/css">
  <link rel="stylesheet" href="/assets/css/admin.css">
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
        <li class="{{ Request::is('admin/confessions') ? 'active' : '' }}"><a href="/admin/confessions"><span class="typcn typcn-pin"></span>Confessions</a></li>
        <li class="{{ Request::is('admin/users') ? 'active' : '' }}"><a href="/admin/users"><span class="typcn typcn-user"></span>User Management</a></li>
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
</body>
</html>
