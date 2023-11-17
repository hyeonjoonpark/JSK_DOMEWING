<div class="nk-sidebar nk-sidebar-fixed " data-content="sidebarMenu">
    <div class="nk-sidebar-element nk-sidebar-head">
        <div class="nk-sidebar-brand">
            <a href="html/index.html" class="logo-link nk-sidebar-logo">
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
                            <a href="/admin/product/search" class="nk-menu-link">
                                <span class="nk-menu-icon"><em class="icon fa-solid fa-magnifying-glass"></em></span>
                                <span class="nk-menu-text">상품 수집</span>
                            </a>
                        </li>
                        <li class="nk-menu-item">
                            <a href="/admin/product/register" class="nk-menu-link">
                                <span class="nk-menu-icon"><em class="icon fa-solid fa-industry"></em></span>
                                <span class="nk-menu-text">신규 상품 생성</span>
                            </a>
                        </li>
                        <li class="nk-menu-item has-sub">
                            <a href="#" class="nk-menu-link nk-menu-toggle">
                                <span class="nk-menu-icon"><em class="icon fa-solid fa-cloud-arrow-up"></em></span>
                                <span class="nk-menu-text">상품 데이터 센터</span>
                            </a>
                            <ul class="nk-menu-sub">
                                <li class="nk-menu-item">
                                    <a href="/admin/product/manage" class="nk-menu-link"><span class="nk-menu-text">수집된
                                            상품 데이터</span></a>
                                </li>
                                <li class="nk-menu-item">
                                    <a href="/admin/product/uploaded" class="nk-menu-link"><span
                                            class="nk-menu-text">업로드된 상품 데이터</span></a>
                                </li>
                            </ul><!-- .nk-menu-sub -->
                        </li><!-- .nk-menu-item -->
                        <li class="nk-menu-item">
                            <a href="/admin/cms_dashboard" class="nk-menu-link">
                                <span class="nk-menu-icon"><em class="icon fa-solid fa-newspaper"></em></span>
                                <span class="nk-menu-text">CMS 저작물 관리 시스템</span>
                            </a>
                        </li>
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
