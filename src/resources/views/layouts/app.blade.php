<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="{{ asset('css/header.css') }}">
    <link rel="stylesheet" href="{{ asset('css/reset.css') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Alice&family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&family=Lora:ital@1&family=Playwrite+AU+VIC+Guides&display=swap" rel="stylesheet">
    <title>Coachtech</title>
    @yield('css')
</head>

<body>
    @yield('content')
</body>

</html>