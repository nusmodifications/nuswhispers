<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>NUSWhispers &ndash; Confession #{{ $confession->confession_id }}</title>
  <meta name="description" content="Have an interesting story to share or just need to get something off your chest? Tell us your story here at NUSWhispers! No one will know it was you." />
  <meta name="keywords" content="NUSWhispers,Confessions,NUS" />

  <meta property="og:title" content="NUSWhispers &ndash; Confession #{{ $confession->confession_id }}" />
  <meta property="og:description" content="{{ $confession->content }}" />
  @if ($confession->images)
  <meta property="og:image" content="{{ $confession->images }}" />
  @else
  <meta property="og:image" content="http://nuswhispers.com/favicon-512x512.png" />
  @endif
  <meta property="og:url" content="<?php echo url('confession', $confession->confession_id) ?>" />
  <meta property="og:type" content="article" />

  @if ($confession->images)
  <meta name="twitter:card" content="summary_large_image" />
  <meta name="twitter:image" content="{{ $confession->images }}" />
  @else
  <meta name="twitter:card" content="summary" />
  @endif

  <meta name="twitter:title" content="NUSWhispers &ndash; Confession #{{ $confession->confession_id }}" />
  <meta name="twitter:description" content="{{ $confession->content }}" />
  <meta name="twitter:url" content="<?php echo url('confession', $confession->confession_id) ?>" />

  <link rel="shortcut icon" href="/favicon.ico" type="image/x-icon">
  <link rel="icon" href="/favicon.ico" type="image/x-icon">
  <base href="/">
</head>
<body>
  <h1>NUSWhispers &ndash; Confession #{{ $confession->confession_id }}</h1>
  <div class="content">
  {{ $confession->content }}
  </div>
</body>
</html>
