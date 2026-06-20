<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('layouts.partials.head')
    </head>
    <body class="layout-fixed sidebar-expand-lg bg-body-tertiary">
        <div class="app-wrapper">
            @include('layouts.partials.header')
            @include('layouts.partials.sidebar')

            <main class="app-main">
                @include('layouts.partials.content-header')

                <div class="app-content">
                    <div class="container-fluid">
                        @yield('content')
                    </div>
                </div>
            </main>

            @include('layouts.partials.footer')
        </div>

        @include('layouts.partials.scripts')
    </body>
</html>
