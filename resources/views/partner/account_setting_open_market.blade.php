@extends('partner.layouts.main')
@section('title')
    오픈마켓 계정 연동
@endsection
@section('subtitle')
    <p>오픈마켓 계정 연동을 추가하거나 수정하는 페이지입니다.</p>
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
                    <p>
                        @if ($coupangAccount === null)
                            등록된 계정 정보가 없습니다. 새로운 계정 정보를 추가해주십시오.
                        @else
                            이미 등록된 계정이 있습니다. 만료일: {{ $coupangAccount->expired_at }}
                        @endif
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
                    <div class="text-center">
                        <button class="btn btn-primary" onclick="handleCoupangAccount();">
                            @if ($coupangAccount === null)
                                추가하기
                            @else
                                수정하기
                            @endif
                        </button>
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
                    secretKey
                },
                success: ajaxSuccessHandling,
                error: AjaxErrorHandling
            });
        }
    </script>
@endsection
