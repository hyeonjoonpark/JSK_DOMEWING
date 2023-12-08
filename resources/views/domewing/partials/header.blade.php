{{-- header content put here --}}

<div class="nk-header is-light" style="padding:0%">
    <div class="container-fluid px-3">
        <div class="nk-header-wrap pt-3 pb-1 ">
            <div class="btn-group pe-5">
                <button type="button" class="btn fs-22px dropdown-toggle dropdown-toggle-split" data-bs-toggle="dropdown"
                    style="color: transparent;">
                    <img class="icon-size me-1" src={{ asset('media\Asset_Nav_Language.svg') }}>
                    <h4 class="my-auto" style="color: var(--dark-blue)">{{ $language->short }}</h4>
                    <img class="icon-size ms-1" src={{ asset('media\Asset_Control_SmallDropdown.svg') }}>
                </button>
                <div class="dropdown-menu dropdown-menu-end">
                    <ul class="link-list-opt no-bdr">
                        <li><a href="{{ url('lang/1') }}"><span>English</span></a></li>
                        <li><a href="{{ url('lang/2') }}"><span>Korea</span></a></li>
                    </ul>
                </div>
            </div>

            <div class="nk-header-tools">
                <div class="toggle-wrap nk-block-tools-toggle">
                    <a href="#" class="btn btn-icon btn-trigger toggle-expand me-n1" data-target="pageMenu"><em
                            class="icon fa-solid fa-bars fa-2xl"></em></a>
                    <div class="toggle-expand-content" data-content="pageMenu">
                        <ul class="nav">
                            <li class="nav-item">
                                <a class="nav-link fs-22px" style="color: var(--dark-blue)"
                                    href={{ route('admin.dashboard') }}>{{ $translation['supplier_page'] }}</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link fs-22px" style="color: var(--dark-blue)"
                                    href={{ route('domewing.home') }}>{{ $translation['about_us'] }}</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link fs-22px" style="color: var(--dark-blue)"
                                    href="/domewing/#contactUsSection">{{ $translation['contact'] }}</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link fs-22px" style="color: var(--dark-blue)"
                                    href="#">{{ $translation['help'] }}</a>
                            </li>


                            @if (Auth::guard('member')->check())
                                <li class="nav-item">
                                    <a class="nav-link fs-22px" style="color: var(--dark-blue)"
                                        href="/domewing/account-settings">{{ $translation['profile'] }}</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link fs-22px" style="color: var(--dark-blue)"
                                        href="/domewing/auth/logout">{{ $translation['logout'] }}</a>
                                </li>
                            @else
                                <li class="nav-item">
                                    <a class="nav-link fs-22px" style="color: var(--dark-blue)"
                                        href="/domewing/auth/register">{{ $translation['signup'] }}</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link fs-22px" style="color: var(--dark-blue)"
                                        href="/domewing/auth/login">{{ $translation['login'] }}</a>
                                </li>
                            @endif
                        </ul>
                    </div>
                </div>
            </div><!-- .nk-header-tools -->
        </div><!-- .nk-header-wrap -->
    </div><!-- .container-fliud -->

    <div class="container-fluid px-4 default-bg-theme"
        style="background-color: {{ $theme_color->color_code ?? 'var(--dark-blue)' }} !important;">
        <div class="row py-3">
            <div class="col-3 m-auto d-none d-lg-block">
                <a href={{ route('domewing.home') }}>
                    <img class="img-fluid" src="{{ asset('media/Asset_Logo_Darkbg.svg') }}" alt="Logo">
                </a>
            </div>
            <div class="col-lg-9 col-md-12 px-lg-5 px-2">
                <div class="d-flex justify-content-between">
                    <form class="col-xl-11 col-lg-10 col-9" action="{{ route('domewing.search') }}" method="GET">
                        <div class="form-control-wrap">
                            <div class="input-group input-group-lg">
                                <div class="input-group-prepend">
                                    <span class="input-group-text" style="background-color: var(--white);">
                                        <img class="icon-size" src={{ asset('media\Asset_Nav_Search.svg') }}>
                                    </span>
                                </div>
                                <input type="text" style="background-color: var(--white); border-left:none;"
                                    class="form-control" placeholder="Product Name" id="search" name="search_keyword"
                                    value="{{ request('search_keyword') }}">
                            </div>
                        </div>
                    </form>

                    <button type="button" class="btn col-md-1 col-2 p-1 px-3 justify-content-center w-auto"
                        style="background-color: var(--white);">
                        <a href="/domewing/shopping-cart">
                            <img class="icon-size clickable" src={{ asset('media\Asset_Nav_Cart.svg') }}>
                        </a>
                    </button>
                </div>

                <div class="hstack pt-3 w-95">
                    <div class="px-2 fs-22px" style="color:var(--cyan-blue)">Category</div>
                    <div class="px-2"></div>
                    <div class="vr" style="color:var(--cyan-blue); width:2px; opacity:1;"></div>
                    <div class="px-2"></div>
                    <ul class="nav horizontal-scrolling">
                        @foreach ($headerCategory as $item)
                            <li class='nav-item'><a href='/domewing/products/search?category={{ $item['title'] }}'
                                    class='nav-link px-3 fs-15px' style='color:var(--white);'>{{ $item['title'] }}</a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
