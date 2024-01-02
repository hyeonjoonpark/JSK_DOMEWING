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
                            <a href="/admin/dashboard" class="nk-menu-link">
                                <span class="nk-menu-icon"><em class="icon fa-solid fa-chart-line"></em></span>
                                <span class="nk-menu-text">대시보드</span>
                            </a>
                        </li>
                        <li class="nk-menu-item">
                            <a href="/admin/product/mining" class="nk-menu-link">
                                <span class="nk-menu-icon"><em class="icon fa-solid fa-person-digging"></em></span>
                                <span class="nk-menu-text">마인윙</span>
                            </a>
                        </li>
                        <li class="nk-menu-item has-sub">
                            <a href="#" class="nk-menu-link nk-menu-toggle">
                                <span class="nk-menu-icon"><em class="icon fa-solid fa-cloud-arrow-up"></em></span>
                                <span class="nk-menu-text">상품윙</span>
                            </a>
                            <ul class="nk-menu-sub">
                                <li class="nk-menu-item">
                                    <a href="/admin/product/minewing" class="nk-menu-link"><span class="nk-menu-text">상품
                                            관리</span></a>
                                </li>
                                <li class="nk-menu-item">
                                    <a href="/admin/product/sold-out" class="nk-menu-link"><span class="nk-menu-text">품절
                                            상품</span></a>
                                </li>
                                <li class="nk-menu-item">
                                    <a href="/admin/product/legacy" class="nk-menu-link"><span class="nk-menu-text">레거시
                                            상품</span></a>
                                </li>
                            </ul>
                        </li>
                        <li class="nk-menu-item has-sub">
                            <a href="#" class="nk-menu-link nk-menu-toggle">
                                <span class="nk-menu-icon"><em class="icon fa-solid fa-circle-nodes"></em></span>
                                <span class="nk-menu-text">매핑윙</span>
                            </a>
                            <ul class="nk-menu-sub">
                                <li class="nk-menu-item">
                                    <a href="/admin/mappingwing/unmapped" class="nk-menu-link"><span
                                            class="nk-menu-text">매핑윙 업데이트</span></a>
                                </li>
                                <li class="nk-menu-item">
                                    <a href="/admin/mappingwing/mapped" class="nk-menu-link"><span
                                            class="nk-menu-text">매핑윙 관리</span></a>
                                </li>
                            </ul><!-- .nk-menu-sub -->
                        </li><!-- .nk-menu-item -->
                        <li class="nk-menu-item">
                            <a href="/admin/product/excelwing" class="nk-menu-link">
                                <span class="nk-menu-icon"><em class="icon fa-solid fa-file-excel"></em></span>
                                <span class="nk-menu-text">엑셀윙</span>
                            </a>
                        </li>
                        <li class="nk-menu-item">
                            <a href="/admin/orderwing" class="nk-menu-link">
                                <span class="nk-menu-icon"><em class="icon fa-solid fa-truck"></em></span>
                                <span class="nk-menu-text">발주윙</span>
                            </a>
                        </li>
                        {{-- <li class="nk-menu-item">
                            <a href="/admin/product/register" class="nk-menu-link">
                                <span class="nk-menu-icon"><em class="icon fa-solid fa-industry"></em></span>
                                <span class="nk-menu-text">신규 상품 생성</span>
                            </a>
                        </li> --}}
                        {{-- <li class="nk-menu-item">
                            <a href="/admin/product/keywords" class="nk-menu-link">
                                <span class="nk-menu-icon"><em class="icon fa-solid fa-industry"></em></span>
                                <span class="nk-menu-text">상품 키워드 테스트</span>
                            </a>
                        </li> --}}
                        {{-- <li class="nk-menu-item">
                            <a href="/admin/cms_dashboard" class="nk-menu-link">
                                <span class="nk-menu-icon"><em class="icon fa-solid fa-newspaper"></em></span>
                                <span class="nk-menu-text">CMS 저작물 관리 시스템</span>
                            </a>
                        </li>
                        <li class="nk-menu-item">
                            <a href="/admin/cms/{{ Auth::user()->remember_token }}" class="nk-menu-link">
                                <span class="nk-menu-icon"><em class="icon fa-solid fa-newspaper"></em></span>
                                <span class="nk-menu-text">Seller CMS</span>
                            </a>
                        </li> --}}
                        <li class="nk-menu-item">
                            <a href="/admin/account-setting" class="nk-menu-link">
                                <span class="nk-menu-icon"><em class="icon fa-solid fa-gear"></em></span>
                                <span class="nk-menu-text">환경설정</span>
                            </a>
                        </li>
                        <li class="nk-menu-item">
                            <a href="/auth/logout" class="nk-menu-link">
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
