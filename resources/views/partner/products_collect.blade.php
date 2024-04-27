@extends('partner.layouts.main')
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

        #topBtn {
            position: fixed;
            /* 고정된 위치 */
            bottom: 20px;
            /* 하단에서 20px 떨어진 위치 */
            right: 30px;
            /* 우측에서 30px 떨어진 위치 */
            z-index: 99;
            /* 다른 요소 위에 표시 */
            font-size: 18px;
            /* 글자 크기 */
            border: none;
            /* 테두리 없음 */
            outline: none;
            /* 외곽선 없음 */
            background-color: #555;
            /* 배경색 */
            color: white;
            /* 글자색 */
            cursor: pointer;
            /* 마우스 커서를 포인터로 변경 */
            padding: 15px;
            /* 패딩 */
            border-radius: 10px;
            /* 모서리 둥글게 */
        }

        #topBtn:hover {
            background-color: #333;
            /* 호버 시 배경색 변경 */
        }
    </style>
@endsection
@section('title')
    상품 수집윙
@endsection
@section('subtitle')
    <p>내 상품 DB에 수집하기 위한 상품 수집관입니다.</p>
@endsection
@section('content')
    <div class="row g-gs">
        <div class="col-12">
            <div class="card card-bordered">
                <div class="card-inner">
                    <h6 class="title">통합 검색</h6>
                    <p>테이블의 모든 컬럼 중 원하는 키워드를 검색하세요.</p>
                    <form method="GET" action="{{ route('partner.products.collect') }}">
                        @csrf
                        <div class="row g-gs">
                            <div class="col-12 col-lg-6">
                                <div class="form-group">
                                    <label for="categoryId" class="form-label">카테고리</label>
                                    <select class="form-select js-select2" data-search="on" name="categoryId"
                                        id="categoryId">
                                        <option value="-1">카테고리 선택</option>
                                        @foreach ($categories as $category)
                                            <option value="{{ $category->id }}"
                                                {{ $categoryId == $category->id ? 'selected' : '' }}>
                                                {{ $category->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-12 col-lg-6">
                                <div class="form-group">
                                    <label for="" class="form-label">상품 검색</label>
                                    <input type="text" class="form-control" placeholder="검색 키워드를 기입해주세요"
                                        name="searchKeyword" value="{{ $searchKeyword }}">
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-group">
                                    <label for="" class="form-label">상품 코드 대량 검색</label>
                                    <input type="text" class="form-control"
                                        placeholder="쉼표(,)로 구분해서 여러 상품 코드를 기입할 수 있습니다." name="productCodesStr"
                                        value="{{ $productCodesStr }}">
                                </div>
                            </div>
                            <div class="col-12 text-center">
                                <button type="submit" class="btn btn-primary">검색</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-12">
            <div class="card card-bordered">
                <div class="card-inner">
                    <h6 class="title">상품윙 테이블</h6>
                    <p>검색된 상품이 총 {{ number_format($products->total(), 0) }}건입니다. 페이지 당 500건의 상품이 출력됩니다.</p>
                    <div class="form-group">
                        @include('partner.partials.pagination', [
                            'page' => $products->currentPage(),
                            'numPages' => $products->lastPage(),
                            'searchKeyword' => $searchKeyword,
                            'categoryId' => $categoryId,
                        ])
                    </div>
                    <div class="table-responsive">
                        <table class="table text-nowrap align-middle">
                            <thead>
                                <tr>
                                    <th scope="col">
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" class="custom-control-input" id="selectAll">
                                            <label class="custom-control-label" for="selectAll"></label>
                                        </div>
                                    </th>
                                    <th scope="col">대표 이미지</th>
                                    <th scope="col">상품</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($products as $product)
                                    <tr id="tr{{ $product->productCode }}">
                                        <td scope="row">
                                            <div class="custom-control custom-checkbox">
                                                <input type="checkbox" class="custom-control-input"
                                                    id="check{{ $product->productCode }}" name="selectedProducts"
                                                    value="{{ $product->productCode }}">
                                                <label class="custom-control-label"
                                                    for="check{{ $product->productCode }}"></label>
                                            </div>
                                        </td>
                                        <td>
                                            <img src="{{ $product->productImage }}" alt="상품 대표 이미지" width=100 height=100>
                                        </td>
                                        <td>
                                            <a class="product-contents"
                                                href="javascript:view('{{ $product->productCode }}');">
                                                <p>
                                                    {{ $product->name }}<br>
                                                    <span class="font-weight-bold">{{ $product->productName }}</span><br>
                                                    {{ $product->productCode }}<br>
                                                    <span
                                                        class="wing-font">{{ number_format($product->productPrice, 0) }}</span>
                                                    <img class="wing" src="{{ asset('assets/images/wing.svg') }}"
                                                        alt="윙"><br>
                                                    배송비: <span
                                                        class="wing-font">{{ number_format($product->shipping_fee, 0) }}</span>
                                                    <img class="wing" src="{{ asset('assets/images/wing.svg') }}"
                                                        alt="윙">
                                                </p>
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="form-group">
                        @include('partner.partials.pagination', [
                            'page' => $products->currentPage(),
                            'numPages' => $products->lastPage(),
                            'searchKeyword' => $searchKeyword,
                            'categoryId' => $categoryId,
                        ])
                    </div>
                </div>
            </div>
        </div>
    </div>
    <button class="btn btn-success btn-collect" onclick="initCollect();">수집하기</button>
    <button id="topBtn" onclick="topFunction()">Top</button>
    <div class="modal" role="dialog" id="collectProductsModal">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">저장할 상품 테이블 선택</h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="" class="form-label">상품 테이블</label>
                        <select name="partnerTable" id="partnerTableToken" class="form-select">
                            @foreach ($partnerTables as $partnerTable)
                                <option value="{{ $partnerTable->token }}">{{ $partnerTable->title }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-success" onclick="collectProducts();">수집하기</button>
                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal">취소하기</button>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('scripts')
    <script>
        $(document).on('click', '#selectAll', function() {
            const isChecked = $(this).is(':checked');
            $('input[name="selectedProducts"]').prop('checked', isChecked);
        });
        $(document).ready(function() {
            $("#partnerTableToken").select2({
                dropdownParent: $("#collectProductsModal")
            });
        });

        function view(productCode) {
            popupLoader(1, "상품 정보를 불러오는 중입니다.");
            $.ajax({
                url: "/api/partner/product/view",
                type: 'POST',
                dataType: "JSON",
                data: {
                    productCode,
                    apiToken
                },
                success: function(response) {
                    closePopup();
                    const {
                        status,
                        data,
                        message
                    } = response;
                    if (status === true) {
                        const {
                            productName,
                            productPrice,
                            productImage,
                            productDetail,
                            shipping_fee,
                            category
                        } = data;
                        $('#viewCategory').html(category);
                        $('#viewProductName').html(productName);
                        $("#viewProductCode").html(productCode);
                        $('#viewProductPrice').html(
                            `${numberFormat(productPrice)} <img class="wing" src="{{ asset('assets/images/wing.svg') }}" alt="윙" />`
                        );
                        $('#viewProductImage').attr('src', productImage);
                        $('#viewProductDetail').html(productDetail);
                        $("#viewShippingFee").html(
                            `${numberFormat(shipping_fee)} <img class="wing" src="{{ asset('assets/images/wing.svg') }}" alt="윙" />`
                        );
                        $("#viewProduct").modal('show');
                    } else {
                        swalWithReload(message, 'error');
                    }
                },
                error: function(error) {
                    closePopup();
                    swalWithReload('API 통신 중 에러가 발생했습니다.', 'error');
                }
            });
        }

        function initCollect() {
            const partnerTables = @json($partnerTables);
            if (partnerTables.length > 0) {
                $('#collectProductsModal').modal('show')
            } else {
                $('#createProductTableModal').modal('show');
            }
        }

        function collectProducts() {
            popupLoader(1, '수집한 상품들을 내 테이블에 기록 중입니다.');
            const productCodes = $('input[name="selectedProducts"]:checked').map(function() {
                return $(this).val();
            }).get();
            const partnerTableToken = $('#partnerTableToken').val();
            $.ajax({
                url: "/api/partner/product/collect",
                type: "POST",
                dataType: "JSON",
                data: {
                    productCodes,
                    partnerTableToken,
                    apiToken
                },
                success: function(response) {
                    closePopup();
                    console.log(response);
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

        function topFunction() {
            document.body.scrollTop = 0; // For Safari
            document.documentElement.scrollTop = 0; // For Chrome, Firefox, IE and Opera
        }
    </script>
@endsection
