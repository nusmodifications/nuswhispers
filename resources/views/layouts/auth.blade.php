<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>NUSWhispers &ndash; @yield('title')</title>
    <link rel="icon" href="/favicon.ico" sizes="16x16 32x32 48x48">
    <link rel="icon" href="/favicon.svg" type="image/svg+xml" sizes="any">
    @vite('resources/sass/auth.scss')
</head>

<body>
    <div class="auth">@yield('content')</div>
</body>

</html>
