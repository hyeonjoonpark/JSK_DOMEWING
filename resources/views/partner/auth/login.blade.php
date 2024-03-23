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
    <title>셀윙 | 파트너 콘솔 로그인</title>
    <!-- StyleSheets  -->
    <link rel="stylesheet" href="./assets/css/dashlite.css?ver=3.1.1">
    <link id="skin-default" rel="stylesheet" href="./assets/css/theme.css?ver=3.1.1">
</head>

<body class="nk-body bg-white npc-general pg-auth">
    <div class="nk-app-root">
        <!-- main @s -->
        <div class="nk-main ">
            <!-- wrap @s -->
            <div class="nk-wrap nk-wrap-nosidebar">
                <!-- content @s -->
                <div class="nk-content ">
                    <div class="nk-block nk-block-middle nk-auth-body  wide-xs">
                        <div class="brand-logo pb-4 text-center">
                            <a href="{{ route('business_page') }}" class="logo-link">
                                <img class="logo-light logo-img logo-img-lg" src="{{ asset('assets/images/logo.png') }}"
                                    alt="셀윙 파트너 로고">
                                <img class="logo-dark logo-img logo-img-lg" src="{{ asset('assets/images/logo.png') }}"
                                    alt="셀윙 파트너 로고">
                            </a>
                        </div>
                        <div class="card card-bordered">
                            <div class="card-inner card-inner-lg">
                                <div class="nk-block-head">
                                    <div class="nk-block-head-content">
                                        <h4 class="nk-block-title">로그인</h4>
                                        <div class="nk-block-des">
                                            <p>이메일과 비밀번호로 셀윙 파트너 콘솔에 접속합니다.</p>
                                        </div>
                                    </div>
                                </div>
                                <form action="{{ route('partner.login') }}" method="post">
                                    @csrf
                                    <p class='text-danger'>{{ $errors->first('invalidLogin') }}</p>
                                    <div class="form-group">
                                        <div class="form-label-group">
                                            <label class="form-label" for="email"
                                                value="{{ old('email') }}">이메일</label>
                                        </div>
                                        <div class="form-control-wrap">
                                            <input type="text" class="form-control form-control-lg" id="email"
                                                name="email" placeholder="이메일을 기입해주세요">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <div class="form-label-group">
                                            <label class="form-label" for="password">비밀번호</label>
                                            <a class="link link-primary link-sm"
                                                href="html/pages/auths/auth-reset-v2.html">비밀번호를 잊으셨나요?</a>
                                        </div>
                                        <div class="form-control-wrap">
                                            <a href="#" class="form-icon form-icon-right passcode-switch lg"
                                                data-target="password">
                                                <em class="passcode-icon icon-show icon ni ni-eye"></em>
                                                <em class="passcode-icon icon-hide icon ni ni-eye-off"></em>
                                            </a>
                                            <input type="password" class="form-control form-control-lg" id="password"
                                                value="{{ old('password') }}" name="password"
                                                placeholder="비밀번호를 기입해주세요">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <button class="btn btn-lg btn-primary btn-block">로그인</button>
                                        <p class="text-danger" id="loginErrMsg"></p>
                                    </div>
                                </form>
                                <div class="form-note-s2 text-center pt-4"> 셀윙이 처음이신가요? <a
                                        href="{{ route('partner.register') }}">회원가입</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- wrap @e -->
            </div>
            <!-- content @e -->
        </div>
        <!-- main @e -->
    </div>
    <!-- app-root @e -->
    <!-- JavaScript -->
    <script src="./assets/js/bundle.js?ver=3.1.1"></script>
    <script src="./assets/js/scripts.js?ver=3.1.1"></script>

</html>
