@extends('layouts.main')
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
                                <div class="col-3 mb-3">
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
                                <div class="col-3 mb-3">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" id="seller{{ $seller->vendor_id }}" name="sellers"
                                            value="{{ $seller->vendor_id }}" class="custom-control-input">
                                        <label class="custom-control-label"
                                            for="seller{{ $seller->vendor_id }}">{{ $seller->name }}</label>
                                    </div>
                                </div>
                            @endforeach
                            <div class="d-flex justify-content-center">
                                <button class="btn btn-warning" onclick="initExcelwing();">엑셀 추출하기</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('scripts')
    <script>
        function initExcelwing() {
            const inputSellers = document.querySelectorAll('input[name="sellers"]:checked');
            const sellerIDs = [];
            for (inputSeller of inputSellers) {
                sellerIDs.push(parseInt(inputSeller.value));
            }
            b2BID = parseInt($("input[name='b2Bs']:checked").val());
            requestExcelwing(b2BID, sellerIDs);
        }

        function requestExcelwing(b2BID, sellerIDs) {
            popupLoader(1, "선택하신 상품셋을 B2B 업체를 위한 대량 등록 양식에 맞추어 엑셀 파일로 작성 중입니다.");
            $.ajax({
                url: "/api/product/excelwing",
                type: "POST",
                dataType: "JSON",
                data: {
                    b2BID: b2BID,
                    sellerIDs: sellerIDs
                },
                success: function(response) {
                    console.log(response);
                    closePopup();
                    if (response.status) {
                        let html =
                            '<img class="w-100" src="{{ asset('media/Asset_Notif_Success.svg') }}"><h4 class="swal2-title mt-5">엑셀 파일을 성공적으로 추출했습니다.<br>아래 링크를 눌러 다운로드를 진행해주세요.</h4>';
                        html += "<a href='" + response.return+"' target='_blank' download>다운로드</a>";
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
                    console.log(response);
                }
            });
        }
    </script>
@endsection
