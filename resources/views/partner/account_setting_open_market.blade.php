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
                        연동 정보에서 '연동업체 선택' 탭에서 업체명 셀윙을 선택해주세요.
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
                        <label for="" class="form-label">별칭(아이디)</label>
                        <input class="form-control" type="text" id="name" placeholder="본 쿠팡 계정 별칭을 입력해주세요.">
                    </div>
                    <div class="text-center">
                        <button class="btn btn-primary" onclick="handleCoupangAccount();">추가하기</button>
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
            const name = $('#name').val();
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
                    name
                },
                success: ajaxSuccessHandling,
                error: AjaxErrorHandling
            });
        }
    </script>
@endsection
