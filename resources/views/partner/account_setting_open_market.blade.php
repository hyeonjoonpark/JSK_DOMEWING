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
                        API 생성하실 때, 반드시 sellwing.kr 도메인과 3.38.96.202 IP 주소를 기입해주세요.
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
        <div class="col-12 col-lg-6">
            <div class="card card-bordered">
                <div class="card-inner">
                    <h6 class="title">11번가</h6>
                    <p>
                        11번가 서비스 등록 및 확인 페이지로부터 OPEN API KEY 정보를 입력해주세요.
                    </p>
                    <div class="form-group">
                        <label for="" class="form-label">계정명(별칭)</label>
                        <input class="form-control" type="text" id="st11Username" placeholder="계정명 혹은 별칭을 입력해주세요.">
                    </div>
                    <div class="form-group">
                        <label for="" class="form-label">OPEN API KEY</label>
                        <input class="form-control" type="text" id="st11AccessKey"
                            placeholder="11ST OPEN API KEY 를 입력해주세요.">
                    </div>
                    <div class="text-center">
                        <button class="btn btn-primary" onclick="addSt11Account();">추가하기</button>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-lg-6">
            <div class="card card-bordered">
                <div class="card-inner">
                    <h6 class="title">롯데온</h6>
                    <p>
                        롯데온 스토어센터의 OpenAPI관리에서 (연동방법 - 직접입력 누르시고) 서버 IP등록에 3.38.96.202를 반드시 입력 후 인증키를 발급해주세요. 인증키
                        정보를 입력해주세요.
                    </p>
                    <div class="form-group">
                        <label for="" class="form-label">스토어명</label>
                        <input class="form-control" type="text" id="lotteOnUsername"
                            placeholder="계정명 혹은 별칭을 입력해주세요.">
                    </div>
                    <div class="form-group">
                        <label for="" class="form-label">인증키</label>
                        <input class="form-control" type="text" id="lotteOnAccessKey" placeholder="롯데온 인증키를 입력해주세요.">
                    </div>
                    <div class="form-group">
                        <label for="" class="form-label">거래처번호</label>
                        <input class="form-control" type="text" id="lotteOnPartnerCode"
                            placeholder="롯데온 거래처번호를 입력해주세요.">
                    </div>
                    <div class="text-center">
                        <button class="btn btn-primary" onclick="addLotteOnAccount();">추가하기</button>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-lg-6">
            <div class="card card-bordered">
                <div class="card-inner">
                    <h6 class="title">카카오쇼핑</h6>
                    <p>
                        카카오쇼핑에서 발급받은 인증키 정보를 입력해주세요.
                    </p>
                    <div class="form-group">
                        <label for="" class="form-label">스토어명</label>
                        <input class="form-control" type="text" id="kakaoShoppingUsername"
                            placeholder="계정명 혹은 별칭을 입력해주세요.">
                    </div>
                    <div class="form-group">
                        <label for="" class="form-label">인증키</label>
                        <input class="form-control" type="text" id="kakaoShoppAccessKey"
                            placeholder="카카오쇼핑 인증키를 입력해주세요.">
                    </div>
                    <div class="text-center">
                        <button class="btn btn-primary" onclick="addKakaoShoppingAccount();">추가하기</button>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-lg-6">
            <div class="card card-bordered">
                <div class="card-inner">
                    <h6 class="title">티몬</h6>
                    <p>
                        티몬에서 발급받은 인증키 정보를 입력해주세요.
                    </p>
                    <div class="form-group">
                        <label for="" class="form-label">스토어명</label>
                        <input class="form-control" type="text" id="tMonUsername" placeholder="계정명 혹은 별칭을 입력해주세요.">
                    </div>
                    <div class="form-group">
                        <label for="" class="form-label">인증키</label>
                        <input class="form-control" type="text" id="tMonAccessKey" placeholder="티몬 인증키를 입력해주세요.">
                    </div>
                    <div class="text-center">
                        <button class="btn btn-primary" onclick="addTMonAccount();">추가하기</button>
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

        function addSt11Account() {
            const username = $('#st11Username').val();
            const accessKey = $('#st11AccessKey').val();
            popupLoader(0, '해당 계정 정보를 동기화 중입니다.');
            $.ajax({
                url: "/api/partner/account-setting/st11",
                type: "POST",
                dataType: "JSON",
                data: {
                    apiToken,
                    username,
                    accessKey
                },
                success: ajaxSuccessHandling,
                error: AjaxErrorHandling
            });
        }

        function addLotteOnAccount() {
            const username = $('#lotteOnUsername').val();
            const accessKey = $('#lotteOnAccessKey').val();
            const partnerCode = $('#lotteOnPartnerCode').val();
            popupLoader(0, '해당 계정 정보를 동기화 중입니다.');
            $.ajax({
                url: "/api/partner/account-setting/lotte-on",
                type: "POST",
                dataType: "JSON",
                data: {
                    apiToken,
                    username,
                    accessKey,
                    partnerCode
                },
                success: ajaxSuccessHandling,
                error: AjaxErrorHandling
            });
        }

        function addKakaoShoppingAccount() {
            const username = $('#kakaoShoppingUsername').val();
            const accessKey = $('#kakaoShoppingAccessKey').val();
            popupLoader(0, '해당 계정 정보를 동기화 중입니다.');
            $.ajax({
                url: "/api/partner/account-setting/kakao-shopping",
                type: "POST",
                dataType: "JSON",
                data: {
                    apiToken,
                    username,
                    accessKey,
                },
                success: ajaxSuccessHandling,
                error: AjaxErrorHandling
            });
        }

        function addTMonAccount() {
            const username = $('#tMonUsername').val();
            const accessKey = $('#tMonAccessKey').val();
            popupLoader(0, '해당 계정 정보를 동기화 중입니다.');
            $.ajax({
                url: "/api/partner/account-setting/tmon",
                type: "POST",
                dataType: "JSON",
                data: {
                    apiToken,
                    username,
                    accessKey,
                },
                success: ajaxSuccessHandling,
                error: AjaxErrorHandling
            });
        }
    </script>
@endsection
