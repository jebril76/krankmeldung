<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Krankmeldungen') }}</title>

    <!-- Scripts -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link href="{{ url('/css/jquery-ui-1.10.3.custom.min.css') }}" rel="stylesheet">
    <script src="{{url('/js/jquery-3.3.1.min.js') }}"></script>
    <script src="{{url('/js/jquery-ui-1.10.3.custom.min.js') }}"></script>
    <script src="{{url('/js/jquery.ui.datepicker-de.js') }}"></script>
    <script src="{{url('/js/print.min.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    @stack('style')
</head>
<body>
    <div id="app">
        <main class="p-1">
            @yield('content')
        </main>
    </div>
    @stack('script')
</body>
</html>
