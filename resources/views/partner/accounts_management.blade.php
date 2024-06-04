@extends('partner.layouts.main')
@section('title')
    오픈마켓 연동 계정 관리
@endsection
@section('subtitle')
    <p>연동된 오픈마켓 계정들을 관리하는 페이지입니다.</p>
@endsection
@section('content')
    <div class="row g-gs">
        <div class="col-12 col-lg-6">
            <div class="card card-bordered">
                <div class="card-inner">
                    <h6 class="title">쿠팡</h6>
                    <p>각 계정을 클릭하여 관리합니다.</p>
                    <div class="row g-gs">
                        @foreach ($coupangAccounts as $item)
                            <div class="col-auto">
                                <button class="btn btn-primary"
                                    onclick="viewCoupangAccount('{{ $item->username }}','{{ $item->code }}','{{ $item->access_key }}','{{ $item->secret_key }}','{{ $item->hash }}','{{ date('Y-m-d', strtotime($item->expired_at)) }}');">{{ $item->username }}</button>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-lg-6">
            <div class="card card-bordered">
                <div class="card-inner">
                    <h6 class="title">스마트 스토어</h6>
                    <p>각 계정을 클릭하여 관리합니다.</p>
                    <div class="row g-gs">
                        @foreach ($smartStoreAccounts as $item)
                            <div class="col-auto">
                                <button class="btn btn-primary"
                                    onclick="viewSsAccount('{{ $item->username }}','{{ $item->application_id }}','{{ $item->secret }}','{{ $item->store_name }}','{{ $item->hash }}');">{{ $item->store_name }}</button>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-lg-6">
            <div class="card card-bordered">
                <div class="card-inner">
                    <h6 class="title">11번가</h6>
                    <p>각 계정을 클릭하여 관리합니다.</p>
                    <div class="row g-gs">
                        @foreach ($st11Accounts as $item)
                            <div class="col-auto">
                                <button class="btn btn-primary"
                                    onclick="viewSt11Account('{{ $item->username }}','{{ $item->access_key }}', '{{ $item->hash }}');">{{ $item->username }}</button>
                            </div>
                        @endforeach
                    </div>
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
                    <button class="btn btn-danger" onclick="initDel();">삭제하기</button>
                </div>
            </div>
        </div>
    </div>
    <div class="modal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false" role="dialog" id="viewSsModal">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">스마트 스토어 계정 연동 정보</h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label class="form-label">애플리케이션 ID</label>
                        <input type="text" class="form-control" id="ssApplicationId">
                    </div>
                    <div class="form-group">
                        <label class="form-label">애플리케이션 시크릿</label>
                        <input type="text" class="form-control" id="ssSecret">
                    </div>
                    <div class="form-group">
                        <label class="form-label">스토어명</label>
                        <input type="text" class="form-control" id="ssStoreName">
                    </div>
                    <div class="form-group">
                        <label class="form-label">판매자 로그인 아이디</label>
                        <input type="text" class="form-control" id="ssUsername">
                    </div>
                </div>
                <div class="modal-footer justify-content-center">
                    <button class="btn btn-success" onclick="smartStoreEdit();">수정하기</button>
                    <button class="btn btn-danger" onclick="initSmartStoreDel();">삭제하기</button>
                </div>
            </div>
        </div>
    </div>
    <div class="modal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false" role="dialog"
        id="viewSt11Modal">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">스마트 스토어 계정 연동 정보</h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label class="form-label">계정명(별칭)</label>
                        <input type="text" class="form-control" id="st11Username">
                    </div>
                    <div class="form-group">
                        <label class="form-label">OPEN API KEY</label>
                        <input type="text" class="form-control" id="st11AccessKey">
                    </div>
                </div>
                <div class="modal-footer justify-content-center">
                    <button class="btn btn-success" onclick="st11Edit();">수정하기</button>
                    <button class="btn btn-danger" onclick="initSt11Del();">삭제하기</button>
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

        function viewSsAccount(username, applicationId, secret, storeName, hash) {
            hashVar = hash;
            $('#ssApplicationId').val(applicationId);
            $('#ssSecret').val(secret);
            $('#ssStoreName').val(storeName);
            $('#ssUsername').val(username);
            $('#viewSsModal').modal('show');
        }

        function viewSt11Account(username, accessKey, hash) {
            hashVar = hash;
            $('#st11Username').val(username);
            $('#st11AccessKey').val(accessKey);
            $('#viewSt11Modal').modal('show');
        }

        function smartStoreEdit() {
            popupLoader(1, "계정 정보를 수정 중입니다.");
            $.ajax({
                url: "/api/partner/account-setting/smart-store/edit",
                type: "POST",
                dataType: "JSON",
                data: {
                    apiToken,
                    applicationId: $('#ssApplicationId').val(),
                    secret: $('#ssSecret').val(),
                    storeName: $('#ssStoreName').val(),
                    username: $('#ssUsername').val(),
                    hash: hashVar
                },
                success: ajaxSuccessHandling,
                error: AjaxErrorHandling
            });
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

        function initDel() {
            Swal.fire({
                icon: "warning",
                title: "계정 삭제",
                text: "해당 계정에 속한 모든 상품 정보 또한 삭제됩니다. 그래도 진행하시겠습니까?",
                showConfirmButton: true,
                showCancelButton: true,
                cancelButtonText: "취소",
                confirmButtonText: "확인"
            }).then((result) => {
                if (result.isConfirmed) {
                    deleteCoupangAccount();
                }
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

        function initSmartStoreDel() {
            Swal.fire({
                icon: "warning",
                title: "계정 삭제",
                text: "해당 계정에 속한 모든 상품 정보 또한 삭제됩니다. 그래도 진행하시겠습니까?",
                showConfirmButton: true,
                showCancelButton: true,
                cancelButtonText: "취소",
                confirmButtonText: "확인"
            }).then((result) => {
                if (result.isConfirmed) {
                    deleteSsAccount();
                }
            });
        }

        function deleteSsAccount() {
            popupLoader(1, "해당 계정을 삭제 중입니다.");
            $.ajax({
                url: "/api/partner/account-setting/smart-store/delete",
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

        function initSt11Del() {
            popupLoader(1, "해당 계정을 삭제 중입니다.");
            $.ajax({
                url: "/api/partner/account-setting/st11/delete",
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

        function st11Edit() {
            popupLoader(1, '수정된 사항을 적용 중입니다.');
            const username = $('#st11Username').val();
            const accessKey = $('#st11AccessKey').val();
            $.ajax({
                url: "/api/partner/account-setting/st11/edit",
                type: "POST",
                dataType: "JSON",
                data: {
                    apiToken,
                    hash: hashVar,
                    username,
                    accessKey
                },
                success: ajaxSuccessHandling,
                error: AjaxErrorHandling
            });
        }
    </script>
@endsection
