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
                    <p>
                        <a href="{{ asset('assets/excel/partner_upload_order.xlsx') }}" target="_blank" download>
                            빈 엑셀 양식 다운로드
                        </a>
                    </p>
                    <div class="d-flex text-nowrap">
                        <input type="file" class="form-control" id="products">
                        <button class="btn btn-primary" onclick="editProducts();">업로드</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('scripts')
    <script>
        function editProducts() {
            const products = $('#products')[0].files[0];
            if (products) {
                popupLoader(1, '수정된 상품들을 데이터베이스에 반영 중입니다.');
                const formData = new FormData();
                formData.append('products', products);
                formData.append('rememberToken', '{{ Auth::guard('user')->user()->remember_token }}');
                $.ajax({
                    url: '/api/product/edit',
                    type: 'POST',
                    dataType: 'JSON',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        console.log(response);
                        closePopup();
                        const status = response.status;
                        if (status === true) {
                            $("#errors").html("모든 상품이 성공적으로 수정됐습니다.");
                            const productCodes = response.productCodes;
                            $("#copyProductCodes").html(productCodes);
                            document.getElementById('copyProductCodes').addEventListener('click', function() {
                                const text = this.innerText;
                                navigator.clipboard.writeText(text).then(function() {
                                    alert('클립보드에 복사되었습니다: ' + text);
                                }, function(err) {
                                    console.error('클립보드 복사에 실패했습니다.', err);
                                });
                            });
                            swalSuccess(response.return);
                        } else {
                            if (response.message) {
                                swalError(response.message);
                            }
                            const errors = response.errors;
                            let html = "";
                            for (const error of errors) {
                                const productCode = error.productCode;
                                const message = error.error;
                                html += productCode + ": " + message + "<br>";
                            }
                            $("#errors").html(html);
                        }
                    },
                    error: function(response) {
                        closePopup();
                        console.error(response);
                    }
                });
            } else {
                swalError("엑셀 파일을 업로드해주세요.");
            }
        }
    </script>
@endsection
