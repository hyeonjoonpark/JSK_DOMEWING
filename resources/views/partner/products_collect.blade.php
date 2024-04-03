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
                        ])
                    </div>
                    <div class="table-responsive">
                        <table class="table text-nowrap align-middle">
                            <thead>
                                <tr>
                                    <th scope="col"><input type="checkbox" id="selectAll"></th>
                                    <th scope="col">대표 이미지</th>
                                    <th scope="col">상품</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($products as $product)
                                    <tr id="tr{{ $product->productCode }}">
                                        <td scope="row"><input type="checkbox" name="selectedProducts"
                                                value="{{ $product->productCode }}"></td>
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
                        ])
                    </div>
                </div>
            </div>
        </div>
    </div>
    <button class="btn btn-success btn-collect" onclick="initCollect();">수집하기</button>
@endsection
@section('scripts')
    <script>
        $(document).on('click', '#selectAll', function() {
            const isChecked = $(this).is(':checked');
            $('input[name="selectedProducts"]').prop('checked', isChecked);
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
            const productCodes = $('input[name="selectedProducts"]:checked').map(function() {
                return $(this).val();
            }).get();
        }
    </script>
@endsection
