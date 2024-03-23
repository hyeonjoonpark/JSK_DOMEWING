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
    <title>셀윙 | 파트너 회원가입</title>
    <!-- StyleSheets  -->
    <link rel="stylesheet" href="{{ asset('assets/css/dashlite.css') }}">
    <link id="skin-default" rel="stylesheet" href="{{ asset('assets/css/dashlite.css') }}">
    <style>
        .round {
            aspect-ratio: 1.2;
        }
    </style>
</head>

<body class="nk-body npc-crypto bg-white pg-auth">
    <!-- app body @s -->
    <div class="nk-app-root">
        <div class="nk-split nk-split-page nk-split-lg">
            <div class="nk-split-content nk-block-area nk-block-area-column nk-auth-container bg-white w-lg-45">
                <div class="absolute-top-right d-lg-none p-3 p-sm-5">
                    <a href="#" class="toggle btn btn-white btn-icon btn-light" data-target="athPromo"><em
                            class="icon ni ni-info"></em></a>
                </div>
                <div class="nk-block nk-block-middle nk-auth-body">
                    <div class="brand-logo pb-5">
                        <a href="/" class="logo-link">
                            <img class="logo-light logo-img logo-img-lg" src="{{ asset('assets/images/logo.png') }}"
                                srcset="{{ asset('assets/images/logo.png') }} 2x" alt="logo">
                            <img class="logo-dark logo-img logo-img-lg" src="{{ asset('assets/images/logo.png') }}"
                                srcset="{{ asset('assets/images/logo.png') }} 2x" alt="logo-dark">
                        </a>
                    </div>
                    <div class="nk-block-head">
                        <div class="nk-block-head-content">
                            <h5 class="nk-block-title">회원가입</h5>
                            <div class="nk-block-des">
                                <p>셀윙의 새로운 계정을 생성합니다</p>
                                <p class="text-danger">{{ $errors->first() }}</p>
                            </div>
                        </div>
                    </div><!-- .nk-block-head -->
                    <form action="/partner/auth/register" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="form-group">
                            <label class="form-label" for="name">성명</label>
                            <div class="form-control-wrap">
                                <input type="text"
                                    class="form-control form-control-lg @error('name') is-invalid @enderror"
                                    id="name" name="name" value="{{ old('name') }}" placeholder="성명을 기입해주세요">
                            </div>
                            <p class="text-danger">{{ $errors->first('name') }}</p>
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="email">이메일</label>
                            <div class="form-control-wrap">
                                <input type="email"
                                    class="form-control form-control-lg @error('email') is-invalid @enderror"
                                    id="email" name="email" value="{{ old('email') }}"
                                    placeholder="이메일을 기입해주세요">
                            </div>
                            <p class="text-danger">{{ $errors->first('email') }}</p>
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="phone">휴대폰 번호</label>
                            <div class="form-control-wrap">
                                <input type="phone"
                                    class="form-control form-control-lg @error('phone') is-invalid @enderror"
                                    id="phone" name="phone" value="{{ old('phone') }}"
                                    placeholder="휴대폰 번호 11자리를 기입해주세요">
                            </div>
                            <p class="text-danger">{{ $errors->first('phone') }}</p>
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="password">비밀번호</label>
                            <div class="form-control-wrap">
                                <a tabindex="-1" href="#" class="form-icon form-icon-right passcode-switch lg"
                                    data-target="password">
                                    <em class="passcode-icon icon-show icon ni ni-eye"></em>
                                    <em class="passcode-icon icon-hide icon ni ni-eye-off"></em>
                                </a>
                                <input type="password"
                                    class="form-control form-control-lg @error('password') is-invalid @enderror"
                                    id="password" name="password" placeholder="비밀번호를 기입해주세요"
                                    value="{{ old('password') }}">
                            </div>
                            <p class="text-danger">{{ $errors->first('password') }}</p>
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="password_confirmation">비밀번호 확인</label>
                            <div class="form-control-wrap">
                                <a tabindex="-1" href="#" class="form-icon form-icon-right passcode-switch lg"
                                    data-target="password_confirmation">
                                    <em class="passcode-icon icon-show icon ni ni-eye"></em>
                                    <em class="passcode-icon icon-hide icon ni ni-eye-off"></em>
                                </a>
                                <input type="password"
                                    class="form-control form-control-lg @error('password') is-invalid @enderror"
                                    id="password_confirmation" name="password_confirmation"
                                    placeholder="비밀번호를 기입해주세요" value="{{ old('password_confirmation') }}">
                            </div>
                            <p class="text-danger">{{ $errors->first('password') }}</p>
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="businessName">업체명</label>
                            <div class="form-control-wrap">
                                <input type="text"
                                    class="form-control form-control-lg @error('businessName') is-invalid @enderror"
                                    id="businessName" name="businessName" value="{{ old('businessName') }}"
                                    placeholder="업체명을 기입해주세요">
                            </div>
                            <p class="text-danger">{{ $errors->first('businessName') }}</p>
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="businessNumber">사업자 등록번호</label>
                            <div class="form-control-wrap">
                                <input type="text"
                                    class="form-control form-control-lg @error('businessNumber') is-invalid @enderror"
                                    id="businessNumber" name="businessNumber" value="{{ old('businessNumber') }}"
                                    placeholder="사업자 등록번호를 숫자만('-' 빼고) 기입해주세요">
                            </div>
                            <p class="text-danger">{{ $errors->first('businessNumber') }}</p>
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="businessImage">사업자 등록증</label>
                            <input type="file" class="form-control" id="businessImage" name="businessImage">
                            <p class="text-danger">{{ $errors->first('businessImage') }}</p>
                        </div>
                        <div class="form-group">
                            <label for="type" class="form-label">파트너 유형</label>
                            <select name="type" id="type" class="form-control js-select2">
                                <option value="FREE">무료</option>
                                <option value="PLUS" disabled>플러스</option>
                                <option value="PREMIUM" disabled>프리미엄</option>
                            </select>
                            <p class="text-danger">{{ $errors->first('type') }}</p>
                        </div>
                        <div class="form-group">
                            <div class="custom-control custom-control-xs custom-checkbox">
                                <input type="checkbox" class="custom-control-input" id="ppt" name="ppt">
                                <label class="custom-control-label" for="ppt"><a tabindex="-1"
                                        href="#">이용약관</a>에 동의합니다.</label>
                            </div>
                            <p class="text-danger">{{ $errors->first('ppt') }}</p>
                        </div>
                        <div class="form-group">
                            <button type="submit" class="btn btn-lg btn-primary btn-block">회원가입</button>
                        </div>
                        <div class="form-note-s2 pt-4"> 이미 계정이 있으신가요 ? <a
                                href="/partner/auth/login"><strong>로그인하기</strong></a>
                        </div>
                    </form>
                </div><!-- .nk-block -->
            </div><!-- nk-split-content -->
            <div class="nk-split-content nk-split-stretch bg-lighter d-flex toggle-break-lg toggle-slide toggle-slide-right"
                data-toggle-body="true" data-content="athPromo" data-toggle-screen="lg" data-toggle-overlay="true">
                <div class="slider-wrap w-100 w-max-550px p-3 p-sm-5 m-auto">
                    <div class="slider-init" data-slick='{"dots":true, "arrows":false}'>
                        <div class="slider-item">
                            <div class="nk-feature nk-feature-center">
                                <div class="nk-feature-img">
                                    <img class="round" src="{{ asset('images/business/01_Landing.svg') }}"
                                        srcset="{{ asset('images/business/01_Landing.svg') }} 2x" alt="">
                                </div>
                                <div class="nk-feature-content py-4 p-sm-5">
                                    <h4>셀윙</h4>
                                    <p>셀윙은 주문 수집부터 품절 관리, 송장 입력에 이르기까지 모든 과정을 자동화한 프로그램입니다.</p>
                                </div>
                            </div>
                        </div><!-- .slider-item -->
                        <div class="slider-item">
                            <div class="nk-feature nk-feature-center">
                                <div class="nk-feature-img">
                                    <img class="round" src="{{ asset('images/business/02_AboutUs.svg') }}"
                                        srcset="{{ asset('images/business/02_AboutUs.svg') }} 2x" alt="">
                                </div>
                                <div class="nk-feature-content py-4 p-sm-5">
                                    <h4>셀윙</h4>
                                    <p>오픈 마켓부터 종합몰에 이르는 다양한 유통 채널에 상품을 등록해 매출을 증대시킬 수 있습니다.</p>
                                </div>
                            </div>
                        </div><!-- .slider-item -->
                        <div class="slider-item">
                            <div class="nk-feature nk-feature-center">
                                <div class="nk-feature-img">
                                    <img class="round" src="{{ asset('images/business/09_Contact.svg') }}"
                                        srcset="{{ asset('images/business/09_Contact.svg') }} 2x" alt="">
                                </div>
                                <div class="nk-feature-content py-4 p-sm-5">
                                    <h4>셀윙</h4>
                                    <p>고객 지원만 관리하면서 판매에 집중함으로써 사업주님들이 매출 효율성을 극대화할 수 있도록 돕습니다.</p>
                                </div>
                            </div>
                        </div><!-- .slider-item -->
                    </div><!-- .slider-init -->
                    <div class="slider-dots"></div>
                    <div class="slider-arrows"></div>
                </div><!-- .slider-wrap -->
            </div><!-- nk-split-content -->
        </div><!-- nk-split -->
    </div><!-- app body @e -->
    <!-- JavaScript -->
    <script src="./assets/js/bundle.js?ver=3.1.1"></script>
    <script src="./assets/js/scripts.js?ver=3.1.1"></script>
</body>

</html>
