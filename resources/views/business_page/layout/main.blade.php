<!DOCTYPE html>
<html lang="ko" class="js">

<head>
    @include('business_page.partials.head')
</head>

<body>

    <header>
        @include('business_page.partials.header')
    </header>

    <main>
        {{-- main content put here --}}
        @yield('content')
    </main>

    @include('business_page.partials.scripts')
    @yield('scripts')
</body>

</html>
