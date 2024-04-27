@extends('partner.layouts.main')
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
                                        <input type="radio" id="openMarket{{ $openMarket->id }}" name="openMarket"
                                            value="{{ $openMarket->id }}" class="custom-control-input"
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
                    <p>총 수집된 상품의 갯수는 <b>{{ number_format(count($uploadedProducts), 0) }}</b>개입니다.</p>
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
                                <th scope="col">업로드 상품 정보</th>
                                <th scope="col">도매윙 상품 정보</th>
                                <th scope="col">ACTION</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($uploadedProducts as $product)
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
                                        <div class="product-contents">
                                            <p>
                                                {{ $product->name }}<br>
                                                <span class="font-weight-bold">{{ $product->productName }}</span><br>
                                                {{ $product->productCode }}<br>
                                                <span class="wing-font">{{ number_format($product->price, 0) }}</span>
                                                <img class="wing" src="{{ asset('assets/images/wing.svg') }}"
                                                    alt="윙"><br>
                                                배송비: <span
                                                    class="wing-font">{{ number_format($product->up_shipping_fee, 0) }}</span>
                                                <img class="wing" src="{{ asset('assets/images/wing.svg') }}"
                                                    alt="윙">
                                            </p>
                                        </div>
                                    </td>
                                    <td>
                                        <a class="product-contents" href="javascript:view('{{ $product->productCode }}');">
                                            <p>
                                                {{ $product->name }}<br>
                                                <span class="font-weight-bold">{{ $product->productName }}</span><br>
                                                {{ $product->productCode }}<br>
                                                <span
                                                    class="wing-font">{{ number_format($product->productPrice, 0) }}</span>
                                                <img class="wing" src="{{ asset('assets/images/wing.svg') }}"
                                                    alt="윙"><br>
                                                배송비: <span
                                                    class="wing-font">{{ number_format($product->mp_shipping_fee, 0) }}</span>
                                                <img class="wing" src="{{ asset('assets/images/wing.svg') }}"
                                                    alt="윙">
                                            </p>
                                        </a>
                                    </td>
                                    <td>
                                        <button class="btn btn-success" onclick="onUpdate();">수정</button>
                                        <button class="btn btn-danger" onclick="onUpdate();">삭제</button>
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
        function onUpdate() {
            Swal.fire({
                icon: "warning",
                title: "해당 기능은 업데이트 중입니다."
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
