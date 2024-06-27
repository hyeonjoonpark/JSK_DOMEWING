@extends('partner.layouts.main')
@section('title')
    엑셀윙
@endsection
@section('subtitle')
    <p>
        엑셀윙은 B2B 업체별로 요구되는 대량 상품 등록을 위한 엑셀 양식에 맞추어 상품 데이터를 재구성하고 있습니다.
    </p>
@endsection
@section('content')
    <div class="row g-gs">
        <div class="col">
            <div class="card card-bordered mb-3">
                <div class="card-inner">
                    <h5 class="card-title">자동 엑셀 폼</h5>
                    <h6 class="card-subtitle mb-2">B2B 업체를 선택한 후, 원청사들을 선별하여 상품 데이터셋을 구성해 주세요.</h6>
                    <div class="form-group">
                        <label for="" class="form-label">B2B 업체</label>
                        <div class="row">
                            @foreach ($b2Bs as $b2B)
                                <div class="col-12 col-md-3 mb-3">
                                    <div class="custom-control custom-radio">
                                        <input type="radio" id="b2B{{ $b2B->id }}" name="b2Bs"
                                            value="{{ $b2B->id }}" class="custom-control-input"
                                            {{ $loop->first ? 'checked' : '' }}>
                                        <label class="custom-control-label"
                                            for="b2B{{ $b2B->id }}">{{ $b2B->name }}</label>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="" class="form-label">원청사</label>
                        <div class="row">
                            @foreach ($sellers as $seller)
                                <div class="col-12 col-md-3 mb-3">
                                    <div class="custom-control custom-radio">
                                        <input type="radio" id="seller{{ $seller->vendor_id }}" name="sellers"
                                            value="{{ $seller->vendor_id }}" class="custom-control-input"
                                            {{ $seller->vendor_id === 61 ? 'checked' : '' }}>
                                        <label class="custom-control-label"
                                            for="seller{{ $seller->vendor_id }}">{{ $seller->name }}</label>
                                    </div>
                                </div>
                            @endforeach
                            <div class="d-flex justify-content-center">
                                <button class="btn btn-warning" onclick="showMarginModal();">엑셀 추출하기</button>
                            </div>
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
                        <label for="partnerMargin" class="form-label">마진율(%)</label>
                        <div class="row">
                            <div class="col-auto">
                                <div class="input-group">
                                    <input type="text" class="form-control" id="partnerMargin"
                                        placeholder="마진율(%)을 기입해주세요." />
                                    <div class="input-group-append">
                                        <span class="input-group-text">%</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-success" onclick="initExcelwing();">생성하기</button>
                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal">취소하기</button>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('scripts')
    <script>
        function showMarginModal() {
            $('#partnerMarginModal').modal('show');
        }

        function initExcelwing() {
            const sellerID = parseInt($("input[name='sellers']:checked").val());
            const b2BID = parseInt($("input[name='b2Bs']:checked").val());
            const marginRate = $('#partnerMargin').val();

            $('#partnerMarginModal').modal('hide');

            requestExcelwing(b2BID, sellerID, marginRate);
        }

        function requestExcelwing(b2BID, sellerID, marginRate) {
            popupLoader(1, "선택하신 상품셋을 B2B 업체를 위한 대량 등록 양식에 맞추어 엑셀 파일로 작성 중입니다.");
            $.ajax({
                url: "/api/partner/excelwing",
                type: "POST",
                dataType: "JSON",
                data: {
                    b2BID: b2BID,
                    sellerID: sellerID,
                    marginRate: marginRate,
                    apiToken
                },
                success: function(response) {
                    console.log(response);
                    closePopup();
                    if (response.status == true) {
                        const urlZip = response.urlZip;
                        let html =
                            '<img class="w-100" src="{{ asset('media/Asset_Notif_Success.svg') }}"><h4 class="swal2-title mt-5">엑셀 파일을 성공적으로 추출했습니다.<br>아래 링크를 눌러 다운로드를 진행해주세요.</h4>';
                        html += `
                        <a href="${urlZip}" target="_blank">압축파일 다운로드</a><br><br>
                        `;
                        let i = 1;
                        for (downloadURL of response.return) {
                            html += "<a href='" + downloadURL + "' target='_blank' download>다운로드 " + i +
                                "</a> / ";
                            i++;
                        }
                        Swal.fire({
                            html: html
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            html: '<img class="w-100" src="{{ asset('media/Asset_Notif_Error.svg') }}"><h4 class="swal2-title mt-5">' +
                                response.return+'</h4>'
                        });
                    }
                },
                error: function(response) {
                    closePopup();
                    Swal.fire({
                        icon: 'error',
                        html: '<img class="w-100" src="{{ asset('media/Asset_Notif_Error.svg') }}"><h4 class="swal2-title mt-5">예기치 못한 오류가 발생했습니다. 다시 시도해 주십시오.</h4>'
                    });
                    console.log(response);
                }
            });
        }
    </script>
@endsection
