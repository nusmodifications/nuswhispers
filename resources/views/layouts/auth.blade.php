<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>NUSWhispers &ndash; @yield('title')</title>
  <link rel="icon" href="favicon.ico">
  <link rel="stylesheet" href="{{ mix('css/auth.css') }}">
</head>

<body>
  <div class="auth">@yield('content')</div>
  <script src="{{ mix('js/admin.js') }}"></script>
</body>

</html>
