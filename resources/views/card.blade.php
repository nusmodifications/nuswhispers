<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>NUSWhispers</title>

  <script src="//use.typekit.net/zog5enw.js"></script>
  <script>try{Typekit.load({ async: true });}catch(e){}</script>
  <link rel="stylesheet" href="{{ mix('css/admin.css') }}">
</head>
<body>
    <div class="card-logo"></div>

	@yield('content')

	<!-- Scripts -->
	<script src="{{ mix('js/admin.js') }}"></script>
</body>
</html>
