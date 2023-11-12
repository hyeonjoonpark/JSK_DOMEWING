<!DOCTYPE html>
<html lang="ko" class="js">

<head>
    @include('domewing.partials.head')
</head>

<body>

    <header>
        @include('domewing.partials.header')
    </header>

    <main>
        {{-- main content put here --}}
        @yield('content')
    </main>

    <footer>
        @include('domewing.partials.footer')
    </footer>

    @include('domewing.partials.scripts')
    @yield('scripts')
</body>

</html>
