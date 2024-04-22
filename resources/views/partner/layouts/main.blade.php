<!DOCTYPE html>
<html lang="ko" class="js">

<head>
    @include('partner.partials.head')
    @yield('style')
</head>

<body class="nk-body bg-white has-sidebar ">
    <div id="pageLoader" class="page-loader">
        <img src="{{ asset('assets/images/search-loader.gif') }}" alt="페이지 로더 이미지"><br>
        <h3 class="nk-block-title page-title">페이지를 로딩 중입니다</h3>
    </div>
    <div class="nk-app-root">
        <!-- main @s -->
        <div class="nk-main ">
            <!-- sidebar @s -->
            @include('partner.partials.sidebar')
            <!-- sidebar @e -->
            <!-- wrap @s -->
            <div class="nk-wrap ">
                <!-- main header @s -->
                @include('partner.partials.header')
                <!-- main header @e -->
                <!-- content @s -->
                <div class="nk-content nk-content-fluid">
                    <div class="container-xl wide-lg">
                        <div class="nk-content-body">
                            <div class="nk-block-head nk-block-head-sm">
                                <div class="nk-block-between">
                                    <div class="nk-block-head-content">
                                        <h3 class="nk-block-title page-title">@yield('title')</h3>
                                        <div class="nk-block-des text-soft">
                                            <p>@yield('subtitle')</p>
                                        </div>
                                    </div><!-- .nk-block-head-content -->
                                </div><!-- .nk-block-between -->
                            </div><!-- .nk-block-head -->
                            <div class="nk-block">
                                @yield('content')
                            </div><!-- .nk-block -->
                        </div>
                    </div>
                </div>
                <!-- content @e -->
                <!-- footer @s -->
                @include('partner.partials.footer')
                <!-- footer @e -->
            </div>
            <!-- wrap @e -->
        </div>
        <!-- main @e -->
    </div>
    <!-- app-root @e -->
    @include('partner.partials.modals')
    <!-- JavaScript -->
    @include('partner.partials.scripts')
    @yield('scripts')
</body>

</html>
