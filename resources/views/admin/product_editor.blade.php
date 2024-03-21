@extends('layouts.main')
@section('title')
    상품 대량 관리
@endsection
@section('subtitle')
    <p>셀윙 상품 대량 관리 엑셀 양식으로 상품 정보 수정, 품절 상태 변경, 재입고 처리가 가능합니다.</p>
@endsection
@section('content')
    <div class="row g-gs">
        <div class="col-12">
            <div class="card card-bordered">
                <div class="card-inner">
                    <h6 class="title">상품 대량 관리 엑셀 업로드</h6>
                    <p>상품 기입이 완료된 셀윙 엑셀 양식을 업로드해주세요.</p>
                    <div class="d-flex text-nowrap">
                        <input type="file" class="form-control" id="products">
                        <button class="btn btn-primary" onclick="editProducts();">업로드</button>
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
        <div class="col-12">
            <div class="card card-bordered">
                <div class="card-inner">
                    <h5 class="card-title">엑셀윙 연동</h5>
                    <h6 class="card-subtitle">아래 표기된 상품 코드들을 기반으로 엑셀윙을 추출합니다. 문자열을 클릭하면 클립보드에 복사됩니다.</h6>
                    <div class="form-group mt-3">
                        <label class="form-label" for="copyProductCodes">상품 코드</label><br>
                        <a class="mb-5" id="copyProductCodes"></a>
                    </div>
                    <div class="form-group mt-3">
                        <label for="" class="form-label">B2B 업체</label>
                        <div class="row">
                            @foreach ($b2bs as $b2B)
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
                        <button class="btn btn-warning" onclick="initExcelwing();">엑셀 추출하기</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('scripts')
    <script>
        function initExcelwing() {
            const productCodes = $('#copyProductCodes').html();
            const b2bId = parseInt($("input[name='b2Bs']:checked").val());
            console.log(productCodes);
            requestExcelwing(b2bId, productCodes);
        }

        function requestExcelwing(b2bId, productCodes) {
            popupLoader(1, '수정된 상품 코드들을 기반으로 엑셀윙 추출을 요청합니다.');
            $.ajax({
                url: "/api/product/edit/excelwing",
                type: 'POST',
                dataType: 'JSON',
                data: {
                    b2bId,
                    productCodes,
                    rememberToken
                },
                success: function(response) {
                    closePopup();
                    const {
                        status,
                        return: downloadLinks
                    } = response;
                    if (status) {
                        const linksHtml = downloadLinks.map((link, index) =>
                            `<a href="${link}">다운로드 ${index + 1}</a>`).join(' / ');
                        Swal.fire({
                            icon: "success",
                            title: "진행 성공",
                            html: linksHtml
                        });
                    } else {
                        swalError(response.return);
                    }
                },
                error: AjaxErrorHandling
            });
        }

        function editProducts() {
            const products = $('#products')[0].files[0];
            if (products) {
                popupLoader(1, '수정된 상품들을 데이터베이스에 반영 중입니다.');
                const formData = new FormData();
                formData.append('products', products);
                formData.append('rememberToken', '{{ Auth::user()->remember_token }}');
                $.ajax({
                    url: '/api/product/edit',
                    type: 'POST',
                    dataType: 'JSON',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
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
