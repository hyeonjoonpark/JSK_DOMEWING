<div class="nk-sidebar nk-sidebar-fixed " data-content="sidebarMenu">
    <div class="nk-sidebar-element nk-sidebar-head">
        <div class="nk-sidebar-brand">
            <a href="/" class="logo-link nk-sidebar-logo">
                <img class="logo-light logo-img" src="{{ asset('assets/images/logo.png') }}"
                    srcset="{{ asset('assets/images/logo.png') }} 2x" alt="logo">
                <img class="logo-dark logo-img" src="{{ asset('assets/images/logo.png') }}"
                    srcset="{{ asset('assets/images/logo.png') }} 2x" alt="logo-dark">
            </a>
        </div>
        <div class="nk-menu-trigger me-n2">
            <a href="#" class="nk-nav-toggle nk-quick-nav-icon d-xl-none" data-target="sidebarMenu"><em
                    class="icon ni ni-arrow-left"></em></a>
        </div>
    </div><!-- .nk-sidebar-element -->
    <div class="nk-sidebar-element">
        <div class="nk-sidebar-body" data-simplebar>
            <div class="nk-sidebar-content">
                <div class="nk-sidebar-menu">
                    <ul class="nk-menu">
                        <li class="nk-menu-heading">
                            <h6 class="overline-title text-primary-alt">메인 메뉴</h6>
                        </li><!-- .nk-menu-item -->
                        <li class="nk-menu-item">
                            <a href="{{ route('partner.dashboard') }}" class="nk-menu-link">
                                <span class="nk-menu-icon"><em class="icon fa-solid fa-chart-line"></em></span>
                                <span class="nk-menu-text">대시보드</span>
                            </a>
                        </li>
                        <li class="nk-menu-item has-sub">
                            <a href="#" class="nk-menu-link nk-menu-toggle">
                                <span class="nk-menu-icon"><em class="icon fa-solid fa-shopping-cart"></em></span>
                                <span class="nk-menu-text">상품윙</span>
                            </a>
                            <ul class="nk-menu-sub">
                                <li class="nk-menu-item">
                                    <a href="/partner/products/collect" class="nk-menu-link"><span
                                            class="nk-menu-text">상품 수집관</span></a>
                                </li>
                                <li class="nk-menu-item">
                                    <a href="/partner/products/manage" class="nk-menu-link"><span
                                            class="nk-menu-text">상품 관리관</span></a>
                                </li>
                                <li class="nk-menu-item">
                                    <a href="/partner/products/upload" class="nk-menu-link"><span
                                            class="nk-menu-text">상품 업로드관</span></a>
                                </li>
                                <li class="nk-menu-item">
                                    <a href="/partner/products/sale" class="nk-menu-link">
                                        <span class="nk-menu-text">업로드된 상품관</span>
                                    </a>
                                </li>
                            </ul>
                        </li>
                        @if (Auth::guard('partner')->user()->partner_class_id === 4)
                            <li class="nk-menu-item has-sub">
                                <a href="#" class="nk-menu-link nk-menu-toggle">
                                    <span class="nk-menu-icon"><em class="icon fa-solid fa-file-excel"></em></span>
                                    <span class="nk-menu-text">엑셀윙</span>
                                </a>
                                <ul class="nk-menu-sub">
                                    <li class="nk-menu-item">
                                        <a href="/partner/excelwing-download" class="nk-menu-link"><span
                                                class="nk-menu-text">다운로드</span>
                                        </a>
                                    </li>
                                    <li class="nk-menu-item">
                                        <a href="/partner/excelwing-upload" class="nk-menu-link"><span
                                                class="nk-menu-text">업로드</span></a>
                                    </li>
                                </ul>
                            </li>
                        @endif
                        <li class="nk-menu-item">
                            <a href="/partner/open-market" class="nk-menu-link">
                                <span class="nk-menu-icon"><em class="icon fa-solid fa-truck"></em></span>
                                <span class="nk-menu-text">오픈마켓</span>
                            </a>
                        </li>
                        <li class="nk-menu-item">

                        </li>
                        <li class="nk-menu-item has-sub">
                            <a href="#" class="nk-menu-link nk-menu-toggle">
                                <span class="nk-menu-icon"><em class="icon fa-solid fa-gear"></em></span>
                                <span class="nk-menu-text">환경설정</span>
                            </a>
                            <ul class="nk-menu-sub">
                                <li class="nk-menu-item">
                                    <a href="/partner/account-setting/partner" class="nk-menu-link"><span
                                            class="nk-menu-text">셀윙
                                            파트너 계정 설정</span></a>
                                </li>
                                <li class="nk-menu-item">
                                    <a href="/partner/account-setting/open-market" class="nk-menu-link"><span
                                            class="nk-menu-text">오픈마켓 계정 연동</span></a>
                                </li>
                                <li class="nk-menu-item">
                                    <a href="/partner/account-setting/accounts-management" class="nk-menu-link"><span
                                            class="nk-menu-text">오픈마켓 계정 관리</span></a>
                                </li>
                                <li class="nk-menu-item">
                                    <a href="/partner/account-setting/dowewing-integration" class="nk-menu-link"><span
                                            class="nk-menu-text">도매윙 계정 연동</span></a>
                                </li>
                            </ul>
                        </li>
                        <li class="nk-menu-item">
                            <a href="/partner/auth/logout" class="nk-menu-link">
                                <span class="nk-menu-icon"><em
                                        class="icon fa-solid fa-arrow-right-from-bracket"></em></span>
                                <span class="nk-menu-text">로그아웃</span>
                            </a>
                        </li>
                    </ul><!-- .nk-footer-menu -->
                </div><!-- .nk-sidebar-footer -->
            </div><!-- .nk-sidebar-content -->
        </div><!-- .nk-sidebar-body -->
    </div><!-- .nk-sidebar-element -->
</div>
