@extends('partner.layouts.main')
@section('title')
    상품 업로드관
@endsection
@section('subtitle')
    <p>연동된 각종 오픈 마켓으로 상품 테이블을 업로드하는 페이지입니다.</p>
@endsection
@section('content')
    <div class="row g-gs">
        <div class="col">
            <div class="card card-bordered">
                <div class="card-inner">
                    <h6 class="title">오픈 마켓 리스트</h6>
                    <p>업로드할 상품 테이블과 오픈 마켓을 선택해주세요.</p>
                    <div class="row g-gs">
                        @foreach ($openMarkets as $openMarket)
                            <div class="col-12 col-md-3">
                                <div class="custom-control custom-checkbox">
                                    <div class="custom-control custom-radio">
                                        <input type="radio" id="openMarket{{ $openMarket->id }}" name="openMarkets"
                                            value="{{ $openMarket->id }}" class="custom-control-input">
                                        <label class="custom-control-label"
                                            for="openMarket{{ $openMarket->id }}">{{ $openMarket->name }}</label>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                        <div class="col-12">
                            <select class="form-select js-select2" data-search="on" name="partnerTableToken"
                                id="partnerTableToken">
                                @foreach ($partnerTables as $partnerTable)
                                    <option value="{{ $partnerTable->token }}">{{ $partnerTable->title }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12 text-center">
                            <button class="btn btn-primary" onclick="initUpload();">업로드하기</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="modal" role="dialog" id="partnerMarginModal">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">마진율 기입</h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="accountList" class="form-label">계정 선택</label>
                        <div class="row g-gs" id="accountList"></div>
                    </div>
                    <div class="form-group">
                        <label for="partnerMargin" class="form-label">마진율(%)</label>
                        <div class="row">
                            <div class="col-auto">
                                <div class="input-group">
                                    <input type="text" class="form-control" value="15" id="partnerMargin"
                                        placeholder="마진율(%)을 기입해주세요." oninput="numberFormatter(this, 2);" />
                                    <div class="input-group-append">
                                        <span class="input-group-text">%</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-success" onclick="upload();">생성하기</button>
                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal">취소하기</button>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('scripts')
    <script>
        function initUpload() {
            popupLoader(1, "연동된 계정 리스트를 불러오는 중입니다.");
            const vendorId = $('input[name="openMarkets"]:checked').val();
            $.ajax({
                url: '/api/partner/account-setting/list',
                type: 'POST',
                dataType: 'JSON',
                data: {
                    apiToken,
                    vendorId
                },
                success: function(response) {
                    closePopup();
                    const status = response.status;
                    if (status === true) {
                        const accounts = response.data;
                        let html = '';
                        let isFirst = true;
                        let index = 0;
                        for (const account of accounts) {
                            html += `
                            <div class="col-6">
                                <div class="custom-control custom-checkbox">
                                    <div class="custom-control custom-radio">
                                        <input type="radio" id="account${index}" name="accounts[]"
                                            value="${account.hash}" class="custom-control-input">
                                        <label class="custom-control-label"
                                            for="account${index}">${account.username}</label>
                                    </div>
                                </div>
                            </div>
                            `;
                        }
                        $('#accountList').html(html);
                        $('#partnerMarginModal').modal('show');
                    } else {
                        Swal.fire({
                            icon: "error",
                            text: response.message
                        }).then((result) => {
                            if (response.redirect) {
                                window.location.replace('/partner/account-setting/open-market');
                            }
                        });
                    }
                },
                error: AjaxErrorHandling
            });
        }

        function upload() {
            closePopup();
            popupLoader(0, "해당 상품 테이블을 스마트 스토어로 전송 중입니다.");
            const partnerTableToken = $('#partnerTableToken').val();
            const vendorId = $('input[name="openMarkets"]:checked').val();
            const partnerMargin = parseInt($('#partnerMargin').val());
            const accountHash = $('input[name="accounts[]"]:checked').val();
            $.ajax({
                url: '/api/partner/product/upload',
                type: 'POST',
                dataType: 'JSON',
                data: {
                    vendorId,
                    partnerTableToken,
                    partnerMargin,
                    accountHash,
                    apiToken
                },
                success: function(response) {
                    if (response.error !== '') {
                        console.log(response);
                    }
                    closePopup();
                    const status = response.status;
                    if (status === true) {
                        swalSuccess(response.message);
                    } else {
                        swalError(response.message);
                    }
                },
                error: AjaxErrorHandling
            });
        }
    </script>
@endsection
