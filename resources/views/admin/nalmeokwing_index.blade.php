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

        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
            padding: 20px;
        }

        .styled-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .styled-list li {
            background: #fff;
            margin: 10px 0;
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            display: flex;
            align-items: center;
            transition: transform 0.2s ease;
        }

        .styled-list li:hover {
            transform: scale(1.02);
        }

        .styled-list .icon {
            margin-right: 10px;
            color: #007bff;
            font-size: 1.2em;
        }

        .styled-list li:nth-child(1) .icon {
            color: #ff6347;
        }

        .styled-list li:nth-child(2) .icon {
            color: #1e90ff;
        }

        .styled-list li:nth-child(3) .icon {
            color: #32cd32;
        }
    </style>
@endsection
@section('title')
    날먹윙 상품 조회
@endsection
@section('subtitle')
    <p>
        날먹윙 및 갓윙 상품들을 조회하는 페이지입니다.
    </p>
@endsection
@section('content')
    <div class="row g-gs">
        <div class="col-12">
            <div class="card card-bordered">
                <div class="card-inner">
                    <h6 class="title">검색 필터</h6>
                    <p>아래 옵션들을 활용해 특정 조건의 상품들을 조회할 수 있습니다.</p>
                    <p>업데이트 중입니다.</p>
                </div>
            </div>
        </div>
        <div class="col-12">
            <div class="card card-bordered">
                <div class="card-inner">
                    <h6 class="title">상품 목록</h6>
                    <p>총 {{ number_format($data['numProducts']) }}개의 상품이 검색되었습니다.</p>
                    <ul class="styled-list">
                        <li>
                            <i class="icon fa fa-image"></i>
                            이미지, 가격, 원청사 등을 눌러 상품 고유 페이지로 이동합니다.
                        </li>
                        <li>
                            <i class="icon fa fa-info-circle"></i>
                            상품명을 눌러 상품 상세페이지를 조회할 수 있습니다.
                        </li>
                        <li>
                            <i class="icon fa fa-clipboard"></i>
                            코드를 눌러 상품 코드를 클립보드에 복사합니다.
                        </li>
                    </ul>
                    <div class="form-group">
                        @include('partials.nalmeokwing_pagination', [
                            'page' => $data['products']->currentPage(),
                            'numPages' => $data['products']->lastPage(),
                        ])
                    </div>
                    <div class="table-responsive">
                        <table class="table text-nowrap align-middle">
                            <thead>
                                <tr>
                                    <th scope="col">대표 이미지</th>
                                    <th scope="col">상품명</th>
                                    <th scope="col">코드</th>
                                    <th scope="col">가격</th>
                                    <th scope="col">원청사</th>
                                    <th scope="col">수집일자</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($data['products'] as $product)
                                    <tr>
                                        <td>
                                            <a href="{{ $product->productHref }}" target="_blank">
                                                <img src="{{ $product->productImage }}" alt="상품 대표 이미지" width=100
                                                    height=100>
                                            </a>
                                        </td>
                                        <td>
                                            <a href="javascript:view('{{ $product->productCode }}');">
                                                {{ $product->productName }}
                                            </a>
                                        </td>
                                        <td>
                                            <a href="javascript:void(0);" id="copy{{ $product->productCode }}"
                                                data-bs-toggle="tooltip" data-bs-placement="top" title="클릭하여 복사"
                                                onclick="copyCode('{{ $product->productCode }}');">
                                                {{ $product->productCode }}
                                            </a>
                                        </td>
                                        <td>
                                            <a href="{{ $product->productHref }}" target="_blank">
                                                {{ number_format($product->productPrice, 0) }}원
                                            </a>
                                        </td>
                                        <td>
                                            <a href="{{ $product->productHref }}" target="_blank">
                                                {{ $product->name }}
                                            </a>
                                        </td>
                                        <td class="text-center">
                                            {{ date('Y-m-d', strtotime($product->createdAt)) }}<br>
                                            {{ date('H:i:s', strtotime($product->createdAt)) }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="form-group">
                        @include('partials.nalmeokwing_pagination', [
                            'page' => $data['products']->currentPage(),
                            'numPages' => $data['products']->lastPage(),
                        ])
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('scripts')
    <script>
        function view(productCode) {
            popupLoader(1, "상품 정보를 불러오는 중입니다.");
            $.ajax({
                url: "/api/product/view",
                type: 'POST',
                dataType: "JSON",
                data: {
                    productCode,
                    rememberToken
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
