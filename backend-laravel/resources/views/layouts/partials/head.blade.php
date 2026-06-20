<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">

<title>@yield('title', config('app.name', 'Simple Transaction Webapp'))</title>

<link rel="stylesheet" href="/vendor/overlayscrollbars/styles/overlayscrollbars.min.css">
<link rel="stylesheet" href="/vendor/bootstrap-icons/font/bootstrap-icons.min.css">
<link rel="stylesheet" href="/vendor/adminlte/css/adminlte.min.css">
@vite(['resources/css/app.css', 'resources/js/app.js'])
