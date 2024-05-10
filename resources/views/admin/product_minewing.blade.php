@php
    set_time_limit(0);
@endphp
@extends('layouts.main')
@section('style')
    <style>
        .product-list-image {
            border: 1px solid black;
            border-bottom: 2px solid black;
            border-right: 2px solid black;
            width: 100px;
            height: 100px;
        }

        /* Active state for pagination */
        .pagination.active {
            background-color: #007bff;
            border-color: #007bff;
            color: #fff;
        }

        /* Inactive state for pagination */
        .pagination {
            display: inline-block;
            padding: 8px 12px;
            margin: 4px;
            font-size: 14px;
            color: #007bff;
            border: 1px solid #007bff;
            text-decoration: none;
            cursor: pointer;
            border-radius: 4px;
        }

        .pagination:hover {
            background-color: #f8f9fa;
        }
    </style>
@endsection
@section('title')
    상품윙
@endsection
@section('subtitle')
    <p>
        마인윙으로부터 수집된 상품들을 관리합니다.
    </p>
@endsection
@section('content')
    <div class="row g-gs mb-5">
        <div class="col">
            <div class="card card-bordered">
                <div class="card-inner">
                    <h6 class="title">통합 검색</h6>
                    <p>테이블의 모든 컬럼 중 원하는 키워드를 검색하세요.</p>
                    <div class="form-group">
                        <label for="" class="form-label">상품 검색</label>
                        <form class="d-flex text-nowrap" method="GET" action="{{ route('admin.minewing') }}">
                            @csrf
                            <input type="text" class="form-control" placeholder="검색 키워드를 기입해주세요" name="searchKeyword"
                                value="{{ $searchKeyword }}">
                            <button type="submit" class="btn btn-primary">검색</button>
                        </form>
                    </div>
                    <div class="form-group">
                        <label for="" class="form-label">상품 코드 대량 검색</label>
                        <form class="d-flex text-nowrap" method="POST" action="/admin/product/minewing">
                            @csrf
                            <input type="text" class="form-control" placeholder="쉼표(,)로 구분해서 여러 상품 코드를 기입할 수 있습니다."
                                name="productCodes" value="{{ $productCodesStr }}">
                            <button type="submit" class="btn btn-primary">검색</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-gs">
        <div class="col">
            <div class="card card-bordered">
                <div class="card-inner">
                    <h6 class="title">상품윙 테이블</h6>
                    <p>검색된 상품이 총 {{ number_format($products->total(), 0) }}건입니다. 페이지 당 500건의 상품이 출력됩니다.</p>
                    <div class="form-group">
                        @include('partials.pagination', [
                            'page' => $products->currentPage(),
                            'numPages' => $products->lastPage(),
                            'searchKeyword' => $searchKeyword,
                            'productCodesStr' => $productCodesStr,
                        ])
                    </div>
                    <div class="text-center mt-3 mb-3">
                        <button class="btn btn-primary" onclick="productsDownload();">상품셋 다운로드</button>
                    </div>
                    <div class="table-responsive">
                        <table class="table text-nowrap align-middle">
                            <thead>
                                <tr>
                                    <th scope="col"><input type="checkbox" onclick="selectAll(this);"></th>
                                    <th scope="col">대표 이미지</th>
                                    <th scope="col">상품명</th>
                                    <th scope="col">코드</th>
                                    <th scope="col">가격</th>
                                    <th scope="col">원청사</th>
                                    <th scope="col">수집일자</th>
                                    <th scope="col">ACTION</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($products as $product)
                                    <tr>
                                        <td scope="row"><input type="checkbox" name="selectedProducts"
                                                value="{{ $product->productCode }}"></td>
                                        <td><a href="{{ $product->productHref }}" target="_blank"><img
                                                    src="{{ $product->productImage }}" alt="상품 대표 이미지" width=100
                                                    height=100></a></td>
                                        <td><a href="{{ $product->productHref }}"
                                                target="_blank">{{ $product->productName }}</a></td>
                                        <td><a href="javascript:void(0);" id="copy{{ $product->productCode }}"
                                                data-bs-toggle="tooltip" data-bs-placement="top" title="클릭하여 복사"
                                                onclick="copyCode('{{ $product->productCode }}');">{{ $product->productCode }}</a>
                                        </td>
                                        <td><a href="{{ $product->productHref }}"
                                                target="_blank">{{ number_format($product->productPrice, 0) }}원</a></td>
                                        <td><a href="{{ $product->productHref }}" target="_blank">{{ $product->name }}</a>
                                        </td>
                                        <td>{{ date('Y-m-d', strtotime($product->createdAt)) }}</td>
                                        <td>
                                            <button class="btn btn-danger" onclick="getProductCodes();">품절</button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="form-group">
                        @include('partials/pagination', [
                            'page' => $products->currentPage(),
                            'numPages' => $products->lastPage(),
                            'searchKeyword' => $searchKeyword,
                        ])
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="modal" tabindex="-1" role="dialog" id="selectB2bModal" data-bs-backdrop="static"
        data-bs-keyboard="false">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">B2B 업체 선택</h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col">
                            <div class="form-group">
                                <label for="" class="form-label">B2B 업체 리스트</label>
                                <div class="row">
                                    <div class="col-6 mb-3">
                                        <div class="custom-control custom-checkbox">
                                            <div class="custom-control custom-checkbox">
                                                <input type="checkbox" id="sellwing" name="sellwing" value="0"
                                                    class="custom-control-input" checked>
                                                <label class="custom-control-label" for="sellwing">셀윙</label>
                                            </div>
                                        </div>
                                    </div>
                                    @foreach ($b2bs as $b2b)
                                        <div class="col-6 mb-3">
                                            <div class="custom-control custom-checkbox">
                                                <div class="custom-control custom-checkbox">
                                                    <input type="checkbox" id="b2b{{ $b2b->vendor_id }}" name="b2bs"
                                                        value="{{ $b2b->vendor_id }}" class="custom-control-input">
                                                    <label class="custom-control-label"
                                                        for="b2b{{ $b2b->vendor_id }}">{{ $b2b->name }}</label>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" id="runSoldOutBtn">선택완료</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">종료하기</button>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('scripts')
    <link rel="stylesheet" href="{{ asset('assets/css/editors/summernote.css') }}">
    <script src="{{ asset('assets/js/editors.js') }}"></script>
    <script src="{{ asset('assets/js/libs/editors/summernote.js') }}"></script>
    <script>
        $(document).on('click', '#selectAll', function() {
            const isChecked = $(this).is(':checked');
            $('input[name="selectedProducts"]').prop('checked', isChecked);
        });

        function getProductCodes() {
            const productCodes = $('input[name="selectedProducts"]:checked').map(function() {
                return $(this).val();
            }).get();
            initSoldOut(productCodes, 'sold-out');
        }

        function productsDownload() {
            popupLoader(1, '데이터베이스로부터 상품들을 추출 중입니다.');
            const productCodes = $('input[name="selectedProducts"]:checked').map(function() {
                return $(this).val();
            }).get();
            $.ajax({
                url: "/api/product/download",
                type: "POST",
                dataType: "JSON",
                data: {
                    rememberToken: '{{ Auth::guard('user')->user()->remember_token }}',
                    productCodes: productCodes
                },
                success: function(response) {
                    closePopup();
                    const status = response.status;
                    if (status === true) {
                        const html = `
                        <a href="${response.return}">상품셋 엑셀 파일 다운로드</a>
                        `;
                        Swal.fire({
                            icon: 'success',
                            title: '진행 성공',
                            html: html
                        });
                    } else {
                        closePopup();
                        swalError(response.return);
                    }
                },
                error: function(response) {
                    console.log(response);
                }
            });
        }

        function copyCode(productCode) {
            const button = $('#copy' + productCode); // jQuery를 사용하여 버튼 선택

            // 클립보드에 텍스트 복사 시도
            navigator.clipboard.writeText(productCode)
                .then(() => {
                    // 복사 성공 시, 툴팁 메시지 및 위치 변경
                    button.attr('data-bs-original-title', '복사 완료!')
                        .attr('data-bs-placement', 'bottom') // 툴팁 위치를 하단으로 변경
                        .tooltip('dispose') // 기존 툴팁 인스턴스 제거
                        .tooltip('show'); // 변경된 설정으로 툴팁 표시

                    // 1초 후 원래의 툴팁 메시지 및 위치로 재설정
                    setTimeout(() => {
                        button.attr('data-bs-original-title', '클릭하여 복사')
                            .attr('data-bs-placement', 'top') // 툴팁 위치를 상단으로 복귀
                            .tooltip('dispose') // 변경된 툴팁 인스턴스 제거
                            .tooltip(); // 원래 설정으로 툴팁 재생성
                    }, 1000); // 1초 후 원래 상태로 복귀
                })
                .catch(err => {
                    console.error('복사 실패: ', err);
                });
        }
    </script>
@endsection
