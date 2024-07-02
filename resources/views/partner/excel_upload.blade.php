@extends('partner.layouts.main')
@section('title')
    상품 대량 주문
@endsection
@section('subtitle')
    <p>상품 대량 주문 엑셀 양식을 사용하여 대량 주문이 가능합니다.</p>
@endsection
@section('content')
    <div class="row g-gs">
        <div class="col-12">
            <div class="card card-bordered">
                <div class="card-inner">
                    <h6 class="title">상품 대량 주문 엑셀 업로드</h6>
                    <p>주문 정보 기입이 완료된 엑셀 양식을 업로드해주세요.</p>
                    <p>엑셀 업로드 시 한번에 최대 500개까지의 주문입력이 가능합니다.</p>
                    <p>
                        <a href="{{ asset('assets/excel/partner_upload_order.xlsx') }}" target="_blank" download>
                            빈 엑셀 양식 다운로드
                        </a>
                    </p>
                    <div class="d-flex text-nowrap">
                        <input type="file" class="form-control" id="orders">
                        <button class="btn btn-primary" onclick="uploadOrder();">업로드</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12">
        <div class="card card-bordered">
            <div class="card-inner">
                <h5 class="card-title">업로드 결과</h5>
                <h6 class="card-subtitle">상품 코드와 해당 에러 메시지가 출력됩니다.</h6>
                <p id="errors" class="mt-3"></p>
            </div>
        </div>
    </div>
@endsection
@section('scripts')
    <script>
        function uploadOrder() {
            const orders = $('#orders')[0].files[0];
            if (orders) {
                popupLoader(1, '업로드한 주문들을 데이터베이스에 반영 중입니다.');
                const formData = new FormData();
                formData.append('orders', orders);
                formData.append('apiToken', apiToken);
                $.ajax({
                    url: '/api/partner/excelwings/upload',
                    type: 'POST',
                    dataType: 'JSON',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        console.log(response);
                        closePopup();
                        if (!response.status) {
                            swalError(response.message);
                            const errors = response.errors;
                            let html = "";
                            if (errors.length > 0) {
                                for (const error of errors) {
                                    html += error;
                                }
                            }
                            $("#errors").html(html);
                        } else {
                            swalSuccess(response.message);
                            $("#errors").html("모든 주문이 성공적으로 입력되었습니다.");
                        }

                    },
                    error: function(response) {
                        closePopup();
                        console.error(response);
                        swalError("주문 업로드에 실패하였습니다. 엑셀 파일을 다시 확인해주세요.");
                    }
                });
            } else {
                swalError("엑셀 파일을 업로드해주세요.");
            }
        }
    </script>
@endsection
