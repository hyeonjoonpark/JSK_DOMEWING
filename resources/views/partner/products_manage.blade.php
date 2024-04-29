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
            <button class="btn btn-primary" onclick="$('#createProductTableModal').modal('show');">상품 테이블 생성</button>
            <button class="btn btn-secondary ml-2" onclick="$('#partnerTableModal').modal('show');">상품 테이블 관리</button>
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
                                'partnerTableToken' => $partnerTableToken,
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
                                                <img src="{{ $product->productImage }}" alt="상품 대표 이미지" width=100>
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
                                                        배송비: <span class="wing-font">
                                                            {{ number_format($product->shipping_fee, 0) }}</span>
                                                        <img class="wing" src="{{ asset('assets/images/wing.svg') }}"
                                                            alt="윙"><br>
                                                        수집일: {{ $product->created_at }}
                                                    </p>
                                                </a>
                                            </td>
                                            <td>
                                                <button class="btn btn-success" onclick="edit();">수정</button>
                                                <button class="btn btn-danger"
                                                    onclick="del('{{ $product->productCode }}');">삭제</button>
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
                                'partnerTableToken' => $partnerTableToken,
                            ])
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    <button id="topBtn" onclick="topFunction()">Top</button>
    <div class="modal" role="dialog" id="partnerTableModal">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">상품 테이블 관리</h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row g-gs">
                        @foreach ($partnerTables as $partnerTable)
                            <div class="col-12">
                                <div class="d-flex text-nowrap">
                                    <input type="text" class="form-control" value="{{ $partnerTable->title }}">
                                    <button class="btn btn-success">수정</button>
                                    <button class="btn btn-danger"
                                        onclick="initDeletePartnerTable('{{ $partnerTable->token }}')">삭제</button>
                                </div>
                            </div>
                        @endforeach
                    </div>
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

        function del(productCode) {
            const html = `
            <h6 class="title">상품 삭제</h6>
            <p>정말로 해당 상품을 삭제하시겠습니까?<br>이 작업은 돌이킬 수 없습니다.</p>
            `;
            Swal.fire({
                icon: "warning",
                html: html,
                showCancelButton: true,
                cancelButtonText: "취소",
                confirmButtonText: "확인"
            }).then((result) => {
                if (result.isConfirmed) {
                    const productCodes = [productCode];
                    requestDelete(productCodes);
                }
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
                    const productCodes = $('input[name="selectedProducts"]:checked').map(function() {
                        return $(this).val();
                    }).get();
                    requestDelete(productCodes);
                }
            });
        }

        function requestDelete(productCodes) {
            $.ajax({
                url: "/api/partner/product/delete-product",
                type: "POST",
                dataType: "JSON",
                data: {
                    apiToken,
                    productCodes
                },
                success: function(response) {
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
                        if (result.isConfirmed) {
                            location.reload();
                        }
                    });
                },
                error: function(error) {
                    console.log(error);
                }
            });
        }

        function initDeletePartnerTable(partnerTableToken) {
            // 삭제 확인 대화 상자 설정
            Swal.fire({
                title: '테이블 삭제',
                text: "정말 이 테이블을 삭제하시겠습니까? 이 작업은 되돌릴 수 없습니다.",
                icon: 'warning',
                showCancelButton: true,
                cancelButtonText: "취소",
                confirmButtonText: "확인"
            }).then((result) => {
                if (result.isConfirmed) {
                    deletePartnerTable(partnerTableToken);
                }
            });
        }

        // 테이블 삭제 로직을 별도의 함수로 분리하여 관리 및 유지보수 용이성 향상
        function deletePartnerTable(tableToken) {
            $.ajax({
                url: '/partner/account-setting/table-delete', // 수정된 URL
                type: 'POST',
                dataType: "JSON",
                contentType: "application/json",
                data: JSON.stringify({
                    apiToken,
                    tableToken
                }),
                success: function(response) {
                    console.log('삭제 성공:', response);
                },
                error: function(xhr, status, error) {
                    console.error('삭제 오류:', status, error);
                }
            });
        }

        // 응답 처리 로직 분리
        function handleDeleteResponse(response) {
            if (response.status) {
                Swal.fire('삭제 완료!', '테이블이 성공적으로 삭제되었습니다.', 'success').then((result) => {
                    if (result.isConfirmed) {
                        location.reload(); // 성공 후 페이지 리로드
                    }
                });
            } else {
                Swal.fire('오류!', response.message, 'error');
            }
        }


        function editPartnerTable() {
            var tableToken = $('#partnerTableToken').val(); // 현재 선택된 테이블 토큰
            var newTableName = prompt("새 테이블 이름을 입력해 주세요:");

            if (newTableName) {
                $.ajax({
                    url: '/partner/account-setting/table/edit', // 서버 엔드포인트
                    type: 'POST',
                    dataType: "JSON",
                    data: {
                        apiToken,
                        productCodes,
                        token: tableToken,
                        newName: newTableName
                    },
                    success: function(response) {
                        if (response.status) {
                            Swal.fire('업데이트 완료!', '테이블 이름이 업데이트 되었습니다.', 'success');
                            // 페이지 또는 데이터 새로고침 로직 필요 시 여기에 추가
                            Swal.fire({
                                icon: icon,
                                title: message
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    location.reload(); // 데이터 삭제 후 페이지 전체를 리로드
                                }
                            });
                        } else {
                            Swal.fire('오류!', response.message, 'error');
                        }
                    },
                    error: function() {
                        Swal.fire('오류!', '서버와의 통신 중 오류가 발생했습니다.', 'error');
                    }
                });
            } else {
                alert("테이블 이름은 비어 있을 수 없습니다.");
            }
        }
    </script>
@endsection
