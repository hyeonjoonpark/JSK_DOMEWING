<!DOCTYPE html>
<html lang="zxx" class="js">

<head>
    <base href="../../../">
    <meta charset="utf-8">
    <meta name="author" content="Softnio">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description"
        content="A powerful and conceptual apps base dashboard template that especially build for developers and programmers.">
    <!-- Fav Icon  -->
    <link rel="shortcut icon" href="{{ asset('assets/images/favicon.png') }}">
    <!-- Page Title  -->
    <title>셀윙 파트너스 | 이메일 인증 결과</title>
    <!-- StyleSheets  -->
    <link rel="stylesheet" href="./assets/css/dashlite.css?ver=3.1.1">
    <link id="skin-default" rel="stylesheet" href="./assets/css/theme.css?ver=3.1.1">
</head>

<body class="nk-body npc-crypto bg-white pg-auth">
    <!-- app body @s -->
    <div class="nk-app-root">
        <div class="nk-split nk-split-page nk-split-md">
            <div class="nk-split-content nk-block-area nk-block-area-column nk-auth-container bg-white w-lg-45">
                <div class="absolute-top-right d-lg-none p-3">
                    <a href="#" class="toggle btn btn-white btn-icon btn-light" data-target="athPromo"><em
                            class="icon ni ni-info"></em></a>
                </div>
                <div class="nk-block nk-block-middle nk-auth-body">
                    <div class="brand-logo pb-5">
                        <a href="html/index.html" class="logo-link">
                            <img class="logo-light logo-img logo-img-lg" src="{{ asset('assets/images/logo.png') }}"
                                srcset="{{ asset('assets/images/logo.png') }} 2x" alt="logo">
                            <img class="logo-dark logo-img logo-img-lg" src="{{ asset('assets/images/logo.png') }}"
                                srcset="{{ asset('assets/images/logo.png') }} 2x" alt="logo-dark">
                        </a>
                    </div>
                    <div class="nk-block-head">
                        <div class="nk-block-head-content">
                            <h5 class="nk-block-title">{{ $title }}</h5>
                            <div class="nk-block-des text-success">
                                <p>{{ $content }}</p>
                            </div>
                            <a href="{{ route('partner.login') }}" class="btn btn-primary mt-5">로그인 페이지로 돌아가기</a>
                        </div>
                    </div>
                </div><!-- .nk-block -->
            </div><!-- .nk-split-content -->
            <div class="nk-split-content nk-split-stretch bg-lighter d-flex toggle-break-lg toggle-slide toggle-slide-right"
                data-toggle-body="true" data-content="athPromo" data-toggle-screen="lg" data-toggle-overlay="true">
                <div class="slider-wrap w-100 w-max-550px  p-3 p-sm-5 m-auto">
                    <div class="nk-feature nk-feature-center">
                        <div class="nk-feature-img">
                            <img class="round" src="{{ asset('images/business/01_Landing.svg') }}"
                                srcset="{{ asset('images/business/01_Landing.svg') }} 2x" alt="">
                        </div>
                        <div class="nk-feature-content  py-4 p-sm-5">
                            <h4>셀윙</h4>
                            <p>셀윙은 주문 수집부터 품절 관리, 송장 입력에 이르기까지 모든 과정을 자동화한 프로그램입니다.</p>
                        </div>
                    </div><!-- .nk-feature -->
                </div><!-- .slider-wrap -->
            </div><!-- .nk-split-content -->
        </div><!-- .nk-split -->
    </div><!-- app body @e -->
    <!-- JavaScript -->
    <script src="./assets/js/bundle.js?ver=3.1.1"></script>
    <script src="./assets/js/scripts.js?ver=3.1.1"></script>
</body>

</html>
