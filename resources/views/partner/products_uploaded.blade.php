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
                                            {{ in_array($openMarket->id, [40, 51]) ? '' : 'disabled' }}>
                                        <label class="custom-control-label"
                                            for="openMarket{{ $openMarket->id }}">{{ $openMarket->name }}</label>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                        <div class="col-12 text-center">
                            <button type="submit" class="btn btn-primary">조회하기</button>
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
                                                    class="wing-font">{{ $selectedOpenMarketId == 40 ? '무료' : number_format($product->up_shipping_fee, 0) . '원' }}</span><br>
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
                                            onclick="requestEdit('{{ $product->origin_product_no }}');">수정</button>
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

        // function onEdit(originProductNo) {
        //     requestEdit(originProductNo); // 백엔드 데이터 로직을 호출하면서 상품 번호 전달
        // }

        function requestEdit(originProductNo, apiToken, productName, price, shippingFee, type) {
            $.ajax({
                url: "/api/partner/product/edit-uploaded", // 백엔드 URL
                type: "POST", // HTTP 메소드
                dataType: "JSON", // 응답 데이터 형식
                data: {
                    apiToken,
                    originProductNo,
                    productName,
                    price, // 가격 정보 추가
                    shippingFee, // 배송비 정보 추가
                    type
                },
                success: function(response) {
                    console.log('Response:', response);
                    if (response.status) {
                        console.log('Product Name: ', response.data.product_name);
                        console.log('Price: ', response.data.price);
                        console.log('Shipping Fee: ', response.data.shipping_fee);
                        alert('상품 정보가 성공적으로 업데이트 되었습니다.');
                    } else {
                        alert('상품 정보 업데이트 실패: ' + response.message);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error:', status, error);
                    alert('상품 정보 업데이트 중 오류 발생');
                }
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
    </script>
@endsection
