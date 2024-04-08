@extends('partner.layouts.main')
@section('title')
    오픈마켓 연동 계정 관리
@endsection
@section('subtitle')
    <p>연동된 오픈마켓 계정들을 관리하는 페이지입니다.</p>
@endsection
@section('content')
    <div class="row g-gs">
        <div class="col-6">
            <div class="card card-bordered">
                <div class="card-inner">
                    <h6 class="title">쿠팡</h6>
                    <p>각 계정을 클릭하여 관리합니다.</p>
                    @foreach ($coupangAccounts as $item)
                        <button class="btn btn-primary"
                            onclick="viewCoupangAccount('{{ $item->username }}','{{ $item->code }}','{{ $item->access_key }}','{{ $item->secret_key }}','{{ $item->hash }}','{{ date('Y-m-d', strtotime($item->expired_at)) }}');">{{ $item->username }}</button>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
    <div class="modal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false" role="dialog"
        id="viewCoupangAccountModal">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">쿠팡 계정 연동 정보</h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label class="form-label">쿠팡 로그인 아이디</label>
                        <input type="text" class="form-control" id="username">
                    </div>
                    <div class="form-group">
                        <label class="form-label">업체코드</label>
                        <input type="text" class="form-control" id="code">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Access Key</label>
                        <input type="text" class="form-control" id="accessKey">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Secret Key</label>
                        <input type="text" class="form-control" id="secretKey">
                    </div>
                    <div class="form-group">
                        <label class="form-label">만료일</label>
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
                </div>
                <div class="modal-footer justify-content-center">
                    <button class="btn btn-success" onclick="editCoupangAccount();">수정하기</button>
                    <button class="btn btn-danger" onclick="deleteCoupangAccount();">삭제하기</button>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('scripts')
    <script>
        var hashVar;

        function viewCoupangAccount(username, code, accessKey, secretKey, hash, expiredAt) {
            $('#username').val(username);
            $('#code').val(code);
            $('#accessKey').val(accessKey);
            $('#secretKey').val(secretKey);
            $('#expiredAt').val(expiredAt);
            hashVar = hash;
            $('#viewCoupangAccountModal').modal('show');
        }

        function editCoupangAccount() {
            popupLoader(1, "계정 정보를 수정 중입니다.");
            $.ajax({
                url: "/api/partner/account-setting/coupang/edit",
                type: "POST",
                dataType: "JSON",
                data: {
                    apiToken,
                    username: $('#username').val(),
                    code: $('#code').val(),
                    accessKey: $('#accessKey').val(),
                    secretKey: $('#secretKey').val(),
                    expiredAt: $('#expiredAt').val(),
                    hash: hashVar
                },
                success: ajaxSuccessHandling,
                error: AjaxErrorHandling
            });
        }

        function deleteCoupangAccount() {
            popupLoader(1, "해당 계정을 삭제 중입니다.");
            $.ajax({
                url: "/api/partner/account-setting/coupang/delete",
                type: "POST",
                dataType: "JSON",
                data: {
                    apiToken,
                    hash: hashVar
                },
                success: ajaxSuccessHandling,
                error: AjaxErrorHandling
            });
        }
    </script>
@endsection
