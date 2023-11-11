{{-- header content put here --}}



{{-- <div class="custom-container custom-inner-content header-top-background header-top-padding">
    <div class="row">
        <div class="col-3 m-auto">
            <div class="custom-dropdown">
                <button class="custom-dropdown-button text-regular text-lg d-flex align-items-center text-dark-blue">
                    <img src="media\Asset_Nav_Language.svg" alt="Language">
                    <span id="selected-language">English</span>
                    <img src="media\Asset_Control_LargeDropdown.svg" alt="Dropdown" class="mx-2">
                </button>
                <div class="dropdown-menu custom-dropdown-content">
                    <a href="#">English</a>
                    <li>
                        <hr class="dropdown-divider">
                    </li>
                    <a href="#">Korean</a>
                </div>
            </div>
        </div>
        <div class="col-1"></div>
        <div class="col-8 d-flex justify-content-end">
            <div class="row">
                <div class="col-12 d-none d-lg-block">
                    <ul class="nav">
                        <li class="nav-item"><a href="{{ route('home') }}"
                                class="nav-link px-2 text-regular text-lg text-dark-blue">About</a></li>
                        <li class="nav-item"><a href="{{ route('home') }}"
                                class="nav-link px-2 text-regular text-lg text-dark-blue">Contact</a></li>
                        <li class="nav-item"><a href="{{ route('faq') }}"
                                class="nav-link px-2 text-regular text-lg text-dark-blue">Help</a></li>
                        <li class="nav-item"><a href="{{ route('signup') }}"
                                class="nav-link px-2 text-regular text-lg text-dark-blue">Sign
                                Up</a></li>
                        <li class="nav-item"><a href="{{ route('login') }}"
                                class="nav-link px-2 text-regular text-lg text-dark-blue">Log
                                in</a></li>
                    </ul>
                </div>

                <div class="col-12 d-lg-none">
                    <nav class="navbar bg-body-tertiary custom-navbar">
                        <div class="container-fluid">
                            <button class="navbar-toggler border-dark" type="button" data-bs-toggle="offcanvas"
                                data-bs-target="#offcanvasNavbar" aria-controls="offcanvasNavbar"
                                aria-label="Toggle navigation">
                                <span class="navbar-toggler-icon"></span>
                            </button>
                            <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasNavbar"
                                aria-labelledby="offcanvasNavbarLabel">
                                <div class="offcanvas-header">
                                    <h5 class="offcanvas-title" id="offcanvasNavbarLabel">DOMEWing</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="offcanvas"
                                        aria-label="Close"></button>
                                </div>
                                <div class="offcanvas-body">
                                    <ul class="navbar-nav justify-content-end flex-grow-1 pe-3">
                                        <li class="nav-item"><a href="{{ route('home') }}"
                                                class="nav-link px-2 text-regular text-lg text-dark-blue">About</a>
                                        </li>
                                        <li class="nav-item"><a href="{{ route('home') }}"
                                                class="nav-link px-2 text-regular text-lg text-dark-blue">Contact</a>
                                        </li>
                                        <li class="nav-item"><a href="{{ route('faq') }}"
                                                class="nav-link px-2 text-regular text-lg text-dark-blue">Help</a>
                                        </li>
                                        <li class="nav-item"><a href="{{ route('signup') }}"
                                                class="nav-link px-2 text-regular text-lg text-dark-blue">Sign
                                                Up</a></li>
                                        <li class="nav-item"><a href="{{ route('login') }}"
                                                class="nav-link px-2 text-regular text-lg text-dark-blue">Log
                                                in</a></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </nav>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="custom-container custom-inner-content header-bottom-background header-bottom-padding">
    <div class="row">
        <div class="col-3 m-auto">
            <img class="custom-logo-size pe-3" src="media\Asset_Logo_Darkbg.svg" alt="Logo">
        </div>
        <div class="col-8">
            <div class="form-group has-search">
                <span class="form-control-feedback icon-size-big"><img src="media\Asset_Nav_Search.svg"
                        alt="Search"></span>
                <input type="text" class="form-control custom-search-textbox" style="background-color: var(--white)"
                    placeholder="">
            </div>

            <div class="hstack" style="padding-top:10px;">
                <div class="px-2 text-regular text-lg" style="color:var(--cyan-blue)">Category</div>
                <div class="px-2"></div>
                <div class="vr vr-dark" style="color:var(--cyan-blue); width:2px;"></div>
                <div class="px-2"></div>
                <ul class="nav horizontal-scrolling">
                    @foreach ($categories as $category)
                        <li class='nav-item'><a href='{{ $category['link'] }}'
                                class='nav-link px-3 text-regular text-xs'
                                style='color:var(--white);'>{{ $category['label'] }}</a></li>
                    @endforeach
                </ul>
            </div>
        </div>
        <div class="col-1 d-none d-lg-block">
            <button type="button" class="btn btn-light custom-search-button" style="background-color: var(--white)">
                <a href="{{ route('search_result') }}">
                    <img class="icon-size" src="media\Asset_Nav_Cart.svg" alt="Language">
                </a>
            </button>
        </div>
    </div>
</div> --}}
