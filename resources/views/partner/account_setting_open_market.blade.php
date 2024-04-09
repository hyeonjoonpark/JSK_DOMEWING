@extends('partner.layouts.main')
@section('title')
    오픈마켓 계정 연동
@endsection
@section('subtitle')
    <p>오픈마켓 계정 연동을 추가하는 페이지입니다.</p>
@endsection
@section('content')
    <div class="row g-gs">
        <div class="col-12 col-lg-6">
            <div class="card card-bordered">
                <div class="card-inner">
                    <h6 class="title">쿠팡</h6>
                    <p>
                        API 생성하실 때, 반드시 sellwing.kr 도메인과 43.200.252.11 IP 주소를 기입해주세요.
                    </p>
                    <div class="form-group">
                        <label for="" class="form-label">업체코드</label>
                        <input class="form-control" id="code" type="text" placeholder="업체코드를 입력해주세요.">
                    </div>
                    <div class="form-group">
                        <label for="" class="form-label">유효 기간</label>
                        <div class="d-flex">
                            <div class="form-control-wrap w-100">
                                <div class="form-icon form-icon-left">
                                    <em class="icon ni ni-calendar"></em>
                                </div>
                                <input type="text" class="form-control date-picker" id="expiredAt"
                                    data-date-format="yyyy-mm-dd" placeholder="만료일 날짜를 지정해주세요.">
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="" class="form-label">Access Key</label>
                        <input class="form-control" type="text" id="accessKey" placeholder="Access Key 를 입력해주세요.">
                    </div>
                    <div class="form-group">
                        <label for="" class="form-label">Secret Key</label>
                        <input class="form-control" type="text" id="secretKey" placeholder="Secret Key 를 입력해주세요.">
                    </div>
                    <div class="form-group">
                        <label for="" class="form-label">쿠팡 로그인 아이디</label>
                        <input class="form-control" type="text" id="username" placeholder="쿠팡 판매자 로그인 아이디를 입력해주세요.">
                    </div>
                    <div class="text-center">
                        <button class="btn btn-primary" onclick="handleCoupangAccount();">추가하기</button>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-lg-6">
            <div class="card card-bordered">
                <div class="card-inner">
                    <h6 class="title">스마트스토어</h6>
                    <p>
                        네이버 커머스 API 센터로부터 얻은 정보를 입력해주세요.
                    </p>
                    <div class="form-group">
                        <label for="" class="form-label">애플리케이션 ID</label>
                        <input class="form-control" id="ssApplicationId" type="text" placeholder="애플리케이션 ID를 입력해주세요.">
                    </div>
                    <div class="form-group">
                        <label for="" class="form-label">애플리케이션 시크릿</label>
                        <input class="form-control" type="text" id="ssSecret"
                            placeholder="'보기' 버튼을 눌러 애플리케이션 시크릿 코드를 입력해주세요.">
                    </div>
                    <div class="form-group">
                        <label for="" class="form-label">스토어명</label>
                        <input class="form-control" type="text" id="ssStoreName" placeholder="스토어명을 입력해주세요.">
                    </div>
                    <div class="form-group">
                        <label for="" class="form-label">판매자 로그인 아이디</label>
                        <input class="form-control" type="text" id="ssUsername" placeholder="판매자 로그인 아이디를 입력해주세요.">
                    </div>
                    <div class="text-center">
                        <button class="btn btn-primary" onclick="addSmartStoreAccount();">추가하기</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('scripts')
    <script>
        function handleCoupangAccount() {
            const code = $('#code').val();
            const expiredAt = $('#expiredAt').val();
            const accessKey = $('#accessKey').val();
            const secretKey = $('#secretKey').val();
            const username = $('#username').val();
            popupLoader(1, '입력하신 정보를 업데이트 중입니다.');
            $.ajax({
                url: "/api/partner/account-setting/coupang",
                type: 'POST',
                dataType: 'JSON',
                data: {
                    apiToken,
                    code,
                    expiredAt,
                    accessKey,
                    secretKey,
                    username
                },
                success: ajaxSuccessHandling,
                error: AjaxErrorHandling
            });
        }

        function addSmartStoreAccount() {
            const username = $('#ssUsername').val();
            const applicationId = $('#ssApplicationId').val();
            const secret = $('#ssSecret').val();
            const storeName = $('#ssStoreName').val();
            popupLoader(0, '해당 계정 정보를 동기화 중입니다.');
            $.ajax({
                url: "/api/partner/account-setting/smart-store",
                type: "POST",
                dataType: "JSON",
                data: {
                    apiToken,
                    username,
                    applicationId,
                    secret,
                    storeName
                },
                success: ajaxSuccessHandling,
                error: AjaxErrorHandling
            });
        }
    </script>
@endsection
