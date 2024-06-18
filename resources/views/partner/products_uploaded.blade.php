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
    업로드된 상품
@endsection
@section('subtitle')
    <p>각 오픈 마켓별로 업로드된 상품들을 관리할 수 있는 페이지입니다.</p>
@endsection
@section('content')
    <div class="row g-gs">
        <div class="col-12">
            <div class="card card-bordered">
                <div class="card-inner">
                    <h6 class="title">오픈 마켓 리스트</h6>
                    <p>조회할 오픈 마켓을 선택해 주세요.</p>
                    <form class="row g-gs" method="GET">
                        @foreach ($openMarkets as $openMarket)
                            <div class="col-12 col-md-3">
                                <div class="custom-control custom-checkbox">
                                    <div class="custom-control custom-radio">
                                        <input type="radio" id="openMarket{{ $openMarket->id }}"
                                            name="selectedOpenMarketId" value="{{ $openMarket->id }}"
                                            class="custom-control-input"
                                            {{ $selectedOpenMarketId == $openMarket->id ? 'checked' : '' }}
                                            {{ in_array($openMarket->id, [40, 51, 54]) ? '' : 'disabled' }}>
                                        <label class="custom-control-label"
                                            for="openMarket{{ $openMarket->id }}">{{ $openMarket->name }}</label>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                        <div class="col-12">
                            <div class="form-group">
                                <label class="form-label">계정 목록</label>
                                <ul class="custom-control-group g-3 align-center">
                                    <li>
                                        <div class="custom-control custom-control-sm custom-checkbox">
                                            <input type="checkbox" class="custom-control-input" id="checkAllAccounts">
                                            <label class="custom-control-label" for="checkAllAccounts">전체 선택/해제</label>
                                        </div>
                                    </li>
                                    @foreach ($accounts as $item)
                                        <li>
                                            <div class="custom-control custom-control-sm custom-checkbox">
                                                <input type="checkbox" class="custom-control-input" name="accountHashes[]"
                                                    id="{{ $item->hash }}" value="{{ $item->hash }}"
                                                    {{ in_array($item->hash, $accountHashes) ? 'checked' : '' }}>
                                                <label class="custom-control-label"
                                                    for="{{ $item->hash }}">{{ $item->username }}</label>
                                            </div>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="row g-gs">
                                <div class="col-12 col-md-6">
                                    <div class="form-group">
                                        <label for="" class="form-label">키워드 검색</label>
                                        <input type="text" class="form-control" name="searchKeyword"
                                            placeholder="검색 키워드를 입력해주세요." value="{{ $searchKeyword }}">
                                    </div>
                                </div>
                                <div class="col-12 col-md-6">
                                    <div class="form-group">
                                        <label for="" class="form-label">상품 코드 대량 검색</label>
                                        <textarea name="searchProductCodes" id="searchProductCodes" class="form-control"
                                            placeholder="쉼표(,)로 상품 코드 단위를 구분해서 입력해주세요.">{{ $searchProductCodes }}</textarea>
                                    </div>
                                </div>
                                <div class="col-12 text-center">
                                    <button type="submit" class="btn btn-primary">조회하기</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-12">
            <div class="card card-bordered">
                <div class="card-inner">
                    <p>검색된 상품이 총 {{ number_format($uploadedProducts->total(), 0) }}건입니다. 페이지 당 500건의 상품이 출력됩니다.</p>
                    <div class="form-group">
                        @include('partner.partials.uploaded_pagination', [
                            'page' => $uploadedProducts->currentPage(),
                            'numPages' => $uploadedProducts->lastPage(),
                            'openMarket' => $openMarket,
                            'searchKeyword' => $searchKeyword,
                            'searchProductCodes' => $searchProductCodes,
                            'accountHashes' => $accountHashes,
                        ])
                    </div>
                    <div class="text-center">
                        <button class="btn btn-danger" onclick="initDelete();">선택
                            상품
                            일괄삭제</button>
                    </div>
                    <table class="table align-middle">
                        <thead>
                            <tr>
                                <th scope="col">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input" id="selectAll">
                                        <label class="custom-control-label" for="selectAll"></label>
                                    </div>
                                </th>
                                <th scope="col">대표 이미지</th>
                                <th scope="col">업로드 상품 정보</th>
                                <th scope="col">내 테이블 상품 정보</th>
                                <th scope="col">ACTION</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($uploadedProducts as $product)
                                <tr>
                                    <td scope="row">
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" class="custom-control-input"
                                                id="{{ $product->origin_product_no }}" name="selectedProducts"
                                                value="{{ $product->origin_product_no }}">
                                            <label class="custom-control-label"
                                                for="{{ $product->origin_product_no }}"></label>
                                        </div>
                                    </td>
                                    <td>
                                        <img src="{{ $product->productImage }}" alt="상품 대표 이미지" width=100>
                                    </td>
                                    <td>
                                        <div class="product-contents">
                                            <p>
                                                {{ $product->name }}<br>
                                                <span class="font-weight-bold">{{ $product->upName }}</span><br>
                                                {{ $product->origin_product_no }}<br>
                                                <span class="wing-font">{{ number_format($product->price, 0) }}</span>원 /
                                                배송비: <span
                                                    class="wing-font">{{ number_format($product->up_shipping_fee, 0) . '원' }}</span><br>
                                                <b>{{ $product->username }}</b><br>
                                                업로드 일시: {{ $product->created_at }}
                                            </p>
                                        </div>
                                    </td>
                                    <td>
                                        <a class="product-contents" href="javascript:view('{{ $product->productCode }}');">
                                            <p>
                                                {{ $product->name }}<br>
                                                <span class="font-weight-bold">{{ $product->mpName }}</span><br>
                                                {{ $product->productCode }}<br>
                                                <span
                                                    class="wing-font">{{ number_format($product->productPrice, 0) }}</span>
                                                <img class="wing" src="{{ asset('assets/images/wing.svg') }}"
                                                    alt="윙"><br>
                                                배송비: <span
                                                    class="wing-font">{{ number_format($product->mp_shipping_fee, 0) }}</span>
                                                <img class="wing" src="{{ asset('assets/images/wing.svg') }}"
                                                    alt="윙"><br>
                                                상품 생성일: {{ $product->mca }}
                                            </p>
                                        </a>
                                    </td>
                                    <td class="text-nowrap">
                                        <button class="btn btn-success"
                                            onclick="initEdit('{{ $product->productCode }}', '{{ $product->origin_product_no }}', '{{ $product->upName }}', '{{ $product->price }}', '{{ $product->up_shipping_fee }}');">수정</button>
                                        <button class="btn btn-danger"
                                            onclick="onDelete(['{{ $product->origin_product_no }}']);">삭제</button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
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
        $(document).on('click', '#checkAllAccounts', function() {
            const isChecked = $(this).is(':checked');
            $('input[name="accountHashes[]"]').prop('checked', isChecked);
        });

        function initEdit(productCode, originProductNo, productName, price, shippingFee) {
            const editFormHTML = `
    <div class="text-start">
        <div class="form-group">
            <label class="form-label">상품명</label>
            <div class="d-flex">
                <input type="text" class="form-control" id="productName" value="${productName}" placeholder="새 상품명을 입력해주세요.">
                <button class="btn btn-primary text-nowrap" onclick="validateProductName('${productCode}', 'TEST');">가공</button>
            </div>
        </div>
        <div class="form-group">
            <label class="form-label">상품가</label>
            <input type="text" class="form-control" id="price" value="${price}" placeholder="상품가를 입력해주세요." oninput="numberFormatter(this, 10, 0);">
        </div>
        <div class="form-group">
            <label class="form-label">배송비</label>
            <input type="text" class="form-control" id="shippingFee" value="${shippingFee}" placeholder="배송비를 입력해주세요." oninput="numberFormatter(this, 10, 0);">
        </div>
    </div>
    `;
            Swal.fire({
                title: "상품 수정",
                html: editFormHTML,
                showCancelButton: true,
                cancelButtonText: "취소",
                confirmButtonText: "확인"
            }).then((result) => {
                if (result.isConfirmed) {
                    const updatedProductName = $('#productName').val();
                    const updatedPrice = parseInt($('#price').val());
                    const updatedShippingFee = parseInt($('#shippingFee').val());

                    requestEdit(originProductNo, updatedProductName, updatedPrice,
                        updatedShippingFee);
                }
            });
        }

        function requestEdit(originProductNo, productName, price, shippingFee) {
            popupLoader(0, '수정된 상품 정보를 오픈 마켓 및 셀윙 DB에 반영 중입니다.');
            const vendorId = $('input[name="selectedOpenMarketId"]:checked').val();
            $.ajax({
                url: '/api/partner/product/edit-uploaded',
                type: 'POST',
                dataType: 'JSON',
                data: {
                    apiToken,
                    vendorId,
                    originProductNo,
                    productName,
                    price,
                    shippingFee
                },
                success: ajaxSuccessHandling,
                error: AjaxErrorHandling
            });
        }

        function initDelete() {
            const originProductsNo = $('input[name="selectedProducts"]:checked').map(function() {
                return $(this).val();
            }).get();
            onDelete(originProductsNo);
        }

        function onDelete(originProductsNo) {
            Swal.fire({
                icon: "warning",
                title: "업로드된 상품 삭제",
                text: "해당 오픈 마켓에 업로드된 해당 상품들이 삭제 처리 됩니다. 정말로 진행하시겠습니까?",
                showCancelButton: true,
                cancelButtonText: "취소",
                confirmButtonText: "삭제"
            }).then((result) => {
                if (result.isConfirmed) {
                    popupLoader(0, "해당 오픈 마켓으로부터 상품들을 삭제하는 중입니다.");
                    const vendorId = $('input[name="selectedOpenMarketId"]:checked').val();
                    $.ajax({
                        url: "/api/partner/product/delete-uploaded",
                        type: 'POST',
                        dataType: "JSON",
                        data: {
                            apiToken,
                            originProductsNo,
                            vendorId
                        },
                        success: ajaxSuccessHandling,
                        error: AjaxErrorHandling
                    });
                }
            });
        }

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

        function validateProductName(productCode, type) {
            $('.btn').prop('disabled', true);
            const productName = $('#productName').val();
            $.ajax({
                url: "/api/partner/product/edit-product",
                type: "POST",
                dataType: "JSON",
                data: {
                    apiToken,
                    productCode,
                    productName,
                    type
                },
                success: function(response) {
                    $('.btn').prop('disabled', false);
                    if (type === 'TEST') {
                        console.log('here');
                        $('#productName').val(response.data.processedProductName);
                    } else {
                        closePopup();
                        const status = response.status;
                        const message = response.message;
                        let icon = 'success';
                        if (status === false) {
                            console.log(response);
                            icon = 'error';
                        }
                        Swal.fire({
                            icon: icon,
                            title: message
                        }).then((result) => {
                            location.reload();
                        });
                    }
                },
                error: AjaxErrorHandling
            });
        };
    </script>
@endsection
