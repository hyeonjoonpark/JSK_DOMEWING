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
    상품 관리관
@endsection
@section('subtitle')
    <p>수집한 상품들을 관리하는 페이지입니다.</p>
@endsection
@section('content')
    <div class="row g-gs">
        <div class="col-12 text-center">
            <button class="btn btn-primary" onclick="$('#createProductTableModal').modal('show')">상품 테이블 생성</button>
        </div>
        <div class="col-12">
            <div class="card card-bordered">
                <div class="card-inner">
                    <form class="row g-gs" method="GET">
                        <div class="col-12 col-lg-4">
                            <div class="form-group">
                                <label class="form-label">상품 테이블</label>
                                <select class="form-select js-select2" data-search="on" name="partnerTableToken"
                                    id="partnerTableToken">
                                    @foreach ($partnerTables as $table)
                                        <option value="{{ $table->token }}"
                                            @if ($partnerTableToken === $table->token) selected @endif>{{ $table->title }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-group">
                                <label class="form-label">상품 키워드 검색</label>
                                <input type="text" class="form-control" name="searchKeyword"
                                    placeholder="검색 키워드를 기입해주세요." value="{{ $searchKeyword }}">
                            </div>
                        </div>
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
                    <h6 class="title">상품윙 테이블</h6>
                    @if ($products === null)
                        <p>아직 생성된 상품윙 테이블이 없습니다. 먼저 테이블을 생성하신 후, 상품 수집관에서 상품들을 수집해주세요.</p>
                    @else
                        <p>검색된 상품이 총 {{ number_format($products->total(), 0) }}건입니다. 페이지 당 500건의 상품이 출력됩니다.</p>
                        <div class="form-group">
                            @include('partner.partials.manage_pagination', [
                                'page' => $products->currentPage(),
                                'numPages' => $products->lastPage(),
                                'searchKeyword' => $searchKeyword,
                            ])
                        </div>
                        <div class="text-center">
                            <button class="btn btn-danger" onclick="initDelete();">선택 상품 일괄삭제</button>
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
                                        <th scope="col">ACTION</th>
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
                                                <img src="{{ $product->productImage }}" alt="상품 대표 이미지" width=100
                                                    height=100>
                                            </td>
                                            <td>
                                                <a class="product-contents"
                                                    href="javascript:view('{{ $product->productCode }}');">
                                                    <p>
                                                        {{ $product->name }}<br>
                                                        <span
                                                            class="font-weight-bold">{{ $product->productName }}</span><br>
                                                        {{ $product->productCode }}<br>
                                                        <span
                                                            class="wing-font">{{ number_format($product->productPrice, 0) }}</span>
                                                        <img class="wing" src="{{ asset('assets/images/wing.svg') }}"
                                                            alt="윙"><br>
                                                        배송비: <spanbtn btn-danger class="wing-font">
                                                            {{ number_format($product->shipping_fee, 0) }}</span>
                                                            <img class="wing" src="{{ asset('assets/images/wing.svg') }}"
                                                                alt="윙">
                                                    </p>
                                                </a>
                                            </td>
                                            <td>
                                                <button class="btn btn-success" onclick="edit();">수정</button>
                                                <button class="btn btn-danger" onclick="del();">삭제</button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="form-group">
                            @include('partner.partials.manage_pagination', [
                                'page' => $products->currentPage(),
                                'numPages' => $products->lastPage(),
                                'searchKeyword' => $searchKeyword,
                            ])
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    <button id="topBtn" onclick="topFunction()">Top</button>
@endsection
@section('scripts')
    <script>
        $(document).on('click', '#selectAll', function() {
            const isChecked = $(this).is(':checked');
            $('input[name="selectedProducts"]').prop('checked', isChecked);
        });
        $(document).ready(function() {
            $("#partnerTable").select2({
                dropdownParent: $("#collectProductsModal")
            });
            const hasTable = @json($hasTable);
            if (hasTable === false) {
                const html = `
                <h6 class="title">생성된 상품 테이블이 없습니다.</h6>
                <p>상품 테이블을 먼저 생성한 후 상품 수집을 진행해주세요.</p>
                `;
                Swal.fire({
                    icon: "warning",
                    html: html
                });
            }
        });

        function topFunction() {
            document.body.scrollTop = 0; // For Safari
            document.documentElement.scrollTop = 0; // For Chrome, Firefox, IE and Opera
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

        function edit() {
            Swal.fire({
                icon: "warning",
                text: "해당 기능은 업데이트 중입니다."
            });
        }

        function del() {
            Swal.fire({
                icon: "warning",
                text: "해당 기능은 업데이트 중입니다."
            });
        }

        function initDelete() {
            const html = `
            <h6 class="title">상품 삭제</h6>
            <p>정말로 해당 상품들을 삭제하시겠습니까?<br>이 작업은 돌이킬 수 없습니다.</p>
            `;
            Swal.fire({
                icon: "warning",
                html: html,
                showCancelButton: true,
                cancelButtonText: "취소",
                confirmButtonText: "확인"
            }).then((result) => {
                if (result.isConfirmed) {
                    requestDelete();
                }
            });
        }

        function requestDelete() {
            const productCodes = $('input[name="selectedProducts"]:checked').map(function() {
                return $(this).val();
            }).get();
            $.ajax({
                url: "/api/partner/product/delete-product",
                type: "POST",
                dataType: "JSON",
                data: {
                    apiToken,
                    productCodes
                },
                success: function(response) {
                    console.log(response);
                },
                error: function(error) {
                    console.log(error);
                }
            });
        }
    </script>
@endsection
