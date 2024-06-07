<div class="nk-header nk-header-fixed is-light">
    <div class="container-fluid">
        <div class="nk-header-wrap">
            <div class="nk-menu-trigger d-xl-none ms-n1">
                <a href="#" class="nk-nav-toggle nk-quick-nav-icon" data-target="sidebarMenu"><em
                        class="icon ni ni-menu"></em></a>
            </div>
            <div class="nk-header-brand d-xl-none">
                <a href="/" class="logo-link">
                    <img class="logo-light logo-img" src="{{ asset('assets/images/logo.png') }}"
                        srcset="{{ asset('assets/images/logo.png') }} 2x" alt="logo">
                    <img class="logo-dark logo-img" src="{{ asset('assets/images/logo.png') }}"
                        srcset="{{ asset('assets/images/logo.png') }} 2x" alt="logo-dark">
                </a>
            </div><!-- .nk-header-brand -->
            <div class="nk-header-tools">
                <ul class="nk-quick-nav">
                    <li class="dropdown">
                        <img class="wing me-1" src="{{ asset('assets/images/wing.svg') }}" alt="윙" />
                        <h6 class="text-warning">{{ number_format($wingBalance) }}</h6>
                    </li>
                    <li class="dropdown">
                        <h6 class="text-warning"><a class="text-warning" href="https://domewing.com/wallet"
                                target="_blank">충전하기</a>
                        </h6>
                    </li>
                    <li class="dropdown user-dropdown">
                        <a href="#" class="dropdown-toggle" data-bs-toggle="dropdown">
                            <div class="user-toggle">
                                <div class="user-avatar sm">
                                    <img src="{{ asset('assets/images/favicon.png') }}" class="w-100" alt="">
                                </div>
                                <div class="user-info d-none d-md-block">
                                    <div class="user-status">{{ $partner->partnerClass->name }}</div>
                                    <div class="user-name dropdown-indicator">{{ $partner->name }}</div>
                                </div>
                            </div>
                        </a>
                        <div class="dropdown-menu dropdown-menu-md dropdown-menu-end dropdown-menu-s1">
                            <div class="dropdown-inner user-card-wrap bg-lighter d-none d-md-block">
                                <div class="user-card">
                                    <div class="user-avatar">
                                        <img src="{{ asset('assets/images/favicon.png') }}" class="w-100"
                                            alt="">
                                    </div>
                                    <div class="user-info">
                                        <div class="d-flex align-items-center">
                                            <span class="lead-text me-1">{{ $partner->name }}</span>
                                            <div class="user-status">{{ $partner->partnerClass->name }}</div>
                                        </div>
                                        <span class="sub-text">{{ $partner->email }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="dropdown-inner">
                                <ul class="link-list">
                                    <li><a href="javascript:$('#viewProfileModal').modal('show');"><em
                                                class="icon ni ni-user-alt"></em><span>프로필 보기</span></a></li>
                                    <li><a class="dark-switch" href="#"><em class="icon ni ni-moon"></em><span>다크
                                                모드</span></a>
                                    </li>
                                </ul>
                            </div>
                            <div class="dropdown-inner">
                                <ul class="link-list">
                                    <li><a href="/partner/auth/logout"><em
                                                class="icon ni ni-signout"></em><span>로그아웃</span></a></li>
                                </ul>
                            </div>
                        </div>
                    </li><!-- .dropdown -->
                    <li class="dropdown notification-dropdown me-n1">
                        <a href="#" class="dropdown-toggle nk-quick-nav-icon" data-bs-toggle="dropdown">
                            <div class="{{ empty($notificastions) ? '' : 'icon-status icon-status-info' }}"><em
                                    class="icon ni ni-bell"></em></div>
                        </a>
                        <div class="dropdown-menu dropdown-menu-xl dropdown-menu-end dropdown-menu-s1">
                            <div class="dropdown-head">
                                <span class="sub-title nk-dropdown-title">알림</span>
                                <a href="javascript:readAll();">모두 읽음 처리</a>
                            </div>
                            <div class="dropdown-body">
                                <div class="nk-notification">
                                    @forelse ($notifications as $item)
                                        <div class="nk-notification-item dropdown-inner">
                                            <div class="nk-notification-icon">
                                                <em class="icon icon-circle bg-warning-dim ni ni-curve-down-right"></em>
                                            </div>
                                            <div class="nk-notification-content">
                                                <div class="nk-notification-text">
                                                    {{ $item->data }}
                                                </div>
                                                <div class="nk-notification-time">{{ $item->created_at }}</div>
                                            </div>
                                        </div>
                                    @empty
                                        <div class="nk-notification-item dropdown-inner">
                                            알림 내역이 없습니다.
                                        </div>
                                    @endforelse
                                </div><!-- .nk-notification -->
                            </div><!-- .nk-dropdown-body -->
                        </div>
                    </li><!-- .dropdown -->
                </ul><!-- .nk-quick-nav -->
            </div><!-- .nk-header-tools -->
        </div><!-- .nk-header-wrap -->
    </div><!-- .container-fliud -->
</div>
