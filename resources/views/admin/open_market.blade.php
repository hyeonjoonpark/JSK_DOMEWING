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
    </style>
@endsection
@section('title')
    오더윙
@endsection
@section('subtitle')
    <p>오더윙을 통해 오픈마켓 업체로부터의 주문 내용을 정리하고 정산합니다. 또한, 송장 번호 자동 추출 기능이 포함되어 있어 효율적인 주문 관리가 가능합니다.</p>
@endsection
@section('content')
    <div class="row g-gs">
        <div class="col">
            <div class="card card-bordered">
                <div class="card-inner">
                    <h6 class="title">신규 주문</h6>
                    <p>상품 정보를 클릭하면 해당 상품의 상세 페이지로 이동합니다.</p>
                    <div class="form-group">
                        <label class="form-label">원청사 리스트 ({{ count($vendors) }})</label>
                        <div>
                            <div class="form-check">
                                <input type="checkbox" id="checkAllvendor" class="form-check-input">
                                <label class="form-check-label" for="checkAllvendor">전체 선택/해제</label>
                            </div>
                            @foreach ($vendors as $vendor)
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input vendor-checkbox" type="checkbox" name="vendor[]"
                                        value="{{ $vendor->id }}" id="vendor-{{ $vendor->id }}">
                                    <label class="form-check-label" for="vendor-{{ $vendor->id }}">
                                        {{ $vendor->name }}
                                    </label>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="orderStatus" id="newOrder"
                                value="PAID_REQUEST" checked>
                            <label class="form-check-label" for="newOrder">신규주문</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="orderStatus" id="awaitingShipment"
                                value="PAID_PROCESS">
                            <label class="form-check-label" for="awaitingShipment">배송대기중</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="orderStatus" id="shipmentComplete"
                                value="PAID_COMPLETE">
                            <label class="form-check-label" for="shipmentComplete">송장완료</label>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="orderStatus" id="exchangeProcess"
                                value="EXCHANGE_REQUEST">
                            <label class="form-check-label" for="exchangeProcess">교환요청</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="orderStatus" id="exchangeComplete"
                                value="EXCHANGE_PROCESS">
                            <label class="form-check-label" for="exchangeComplete">교환대기중</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="orderStatus" id="returnRequest"
                                value="EXCHANGE_COMPLETE">
                            <label class="form-check-label" for="returnRequest">교환완료</label>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="orderStatus" id="returnProcess"
                                value="RETURN_REQUEST">
                            <label class="form-check-label" for="returnProcess">반품요청</label>
                        </div>
                        {{-- <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="orderStatus" id="returnComplete"
                                value="RETURN_PROCESS">
                            <label class="form-check-label" for="returnComplete">반품대기중</label>
                        </div> --}}
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="orderStatus" id="exchangeRequest"
                                value="RETURN_COMPLETE">
                            <label class="form-check-label" for="exchangeRequest">반품완료</label>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="orderStatus" id="orderCancelled"
                                value="CANCEL_COMPLETE">
                            <label class="form-check-label" for="orderCancelled">취소완료</label>
                        </div>
                    </div>

                    <button class="btn btn-primary mb-5" onclick="showData();">조회하기</button>
                    <button class="btn btn-danger mb-5" style="margin-left: 633px;" onclick="initIndex();">업데이트</button>
                    <div class="form-group">
                        <h6 class="title">잔액 부족 계정 리스트</h6>
                        <ul id="lowBalanceAccountsList"></ul>
                    </div>
                    <p>총 <span id="numOrders">0</span>개의 주문이 접수되었습니다.</p>
                    <div class="form-group">
                        <label class="form-label">금일 총 매출액</label>
                        <h5 class="card-title" id="totalAmt"></h5>
                    </div>
                    <div class="table-responsive">
                        <table class="table align-middle custom-table">
                            <thead>
                                <tr>
                                    <th scope="col">수취인 정보</th>
                                    <th scope="col">주문 정보</th>
                                    <th scope="col">주문자 정보</th>
                                    <th scope="col">주문상태</th>
                                </tr>
                            </thead>
                            <tbody id="orderwingResult">
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- 교환, 환불 정보 Modal -->
    <div class="modal fade" id="orderInfoModal" tabindex="-1" aria-labelledby="orderInfoModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="orderInfoModalLabel">교환, 환불 정보</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Data will be loaded here -->
                    <div id="modalContent"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
    <!-- 주문 상태 변경 Modal -->
    <div class="modal fade" id="modalProcess">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">주문 처리</h5>
                    <a href="#" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <em class="icon ni ni-cross"></em>
                    </a>
                </div>
                <div class="modal-body">
                    <div action="#" class="form-validate is-alter">
                        <div class="form-group">
                            <label class="form-label">주문 상태</label>
                            <ul class="custom-control-group g-3 align-center">
                                <li>
                                    <div class="custom-control custom-control-sm custom-radio">
                                        <input type="radio" class="custom-control-input" name="targetStatus"
                                            id="awaiting-shipment">
                                        <label class="custom-control-label" for="awaiting-shipment">배송 대기 중</label>
                                    </div>
                                </li>
                                <li>
                                    <div class="custom-control custom-control-sm custom-radio">
                                        <input type="radio" class="custom-control-input" name="targetStatus"
                                            id="shipment-complete">
                                        <label class="custom-control-label" for="shipment-complete">송장 완료</label>
                                    </div>
                                </li>
                                <li>
                                    <div class="custom-control custom-control-sm custom-radio">
                                        <input type="radio" class="custom-control-input" name="targetStatus"
                                            id="order-cancel">
                                        <label class="custom-control-label" for="order-cancel">오픈마켓 취소</label>
                                    </div>
                                </li>
                                <li>
                                    <div class="custom-control custom-control-sm custom-radio">
                                        <input type="radio" class="custom-control-input" name="targetStatus"
                                            id="accept-cancel">
                                        <label class="custom-control-label" for="accept-cancel">도매윙 취소</label>
                                    </div>
                                </li>
                            </ul>
                        </div>
                        <div class="form-group" id="remoteAreaOption" style="display: none;">
                            <div>
                                <label>
                                    <input type="checkbox" id="confirmCheckbox" />
                                    제주/도서 산간지역
                                </label>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="full-name">택배사</label>
                            <div class="form-control-wrap">
                                <select class="form-select" id="deliveryCompanyModal" data-search="on">
                                    <option value="-1">택배사를 선택해주세요.</option>
                                    @foreach ($deliveryCompanies as $item)
                                        <option value="{{ $item->id }}">{{ $item->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="trackingNumber">송장번호</label>
                            <div class="form-control-wrap">
                                <input type="text" class="form-control" id="trackingNumber" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="remark">사유</label>
                            <div class="form-control-wrap">
                                <input type="text" class="form-control" id="remark">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button class="btn btn-primary" onclick="processOrder();">확인</button>
                    <button class="btn btn-danger" data-bs-dismiss="modal">취소</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        var rememberToken = '{{ Auth::guard('user')->user()->remember_token }}';
        var productOrderNumber;
        $(document).ready(function() {
            // 모달이 열릴 때 실행되는 이벤트 핸들러
            $('#modalProcess').on('shown.bs.modal', function() {
                // select2 초기화
                $("#deliveryCompanyModal").select2({
                    dropdownParent: $('#modalProcess')
                });

                // 모달 내부의 값을 초기화 또는 설정
                $('#deliveryCompanyModal').val(null).trigger('change'); // select2 초기화 예시
                $('input[name="targetStatus"]').prop('checked', false); // 체크박스 초기화 예시
                $('#remoteAreaOption').hide(); // 숨김 초기화 예시
                $('#confirmCheckbox').prop('checked', false);
                $('#trackingNumber').val(''); // 송장번호 초기화
                $('#remark').val(
                    '죄송합니다.\n\n주문해주신 제품 현재 최종 품절 확인이 되어 부득이 판매취소처리를 하게 되었습니다.\n\n입고일정도 확인하였으나, 입고 지연이 됨으로써 예상잡혀있던 입고일정이 다시 미정으로 잡히게 되었습니다.\n\n양해 부탁드리며, 쇼핑에 불편을 끼쳐드린점 사과의 말씀을 드립니다.'
                ); // 사유 초기화
            });

            // targetStatus 변경 시 이벤트 핸들러
            $('input[name="targetStatus"]').change(function() {
                const selectedStatus = $(this).attr('id');
                if (selectedStatus === 'shipment-complete') {
                    $('#remoteAreaOption').show();
                } else {
                    $('#remoteAreaOption').hide();
                }
            });
        });



        function processOrder() {
            popupLoader(0, '"요청사항 반영중입니다."');
            const targetStatus = $('input[name="targetStatus"]:checked').attr('id');
            const deliveryCompanyId = $('#deliveryCompanyModal').val();
            const trackingNumber = $('#trackingNumber').val();
            const remark = $('#remark').val();
            const isRemoteArea = targetStatus === 'shipment-complete' ? $('#confirmCheckbox').prop('checked') : null;
            $.ajax({
                url: '/api/process-order',
                type: 'POST',
                dataType: 'JSON',
                data: {
                    rememberToken,
                    productOrderNumber,
                    trackingNumber,
                    deliveryCompanyId,
                    remark,
                    targetStatus,
                    isRemoteArea
                },
                success: function(response) {
                    closePopup();
                    console.log(response);
                    if (response.status === false) {
                        Swal.fire({
                            icon: 'error',
                            text: response.message,
                        });
                    } else {
                        Swal.fire({
                            icon: 'success',
                            text: response.message
                        }).then(() => {
                            $('#modalProcess').modal('hide');
                        });
                    }
                },
                error: function(response) {
                    closePopup();
                    console.error('Error processing order:', response);
                    Swal.fire({
                        icon: 'error',
                        text: '주문 처리 중 오류가 발생했습니다.'
                    });
                }
            });
        }

        function getSelectedVendors() {
            let selectedVendors = [];
            $('.vendor-checkbox:checked').each(function() {
                selectedVendors.push($(this).val());
            });
            return selectedVendors;
        }

        $(document).on('click', '#checkAllvendor', function() {
            const isChecked = $(this).is(':checked');
            $('.vendor-checkbox').prop('checked', isChecked);
        });

        function initIndex() {
            popupLoader(0, '"신규 주문을 데이터베이스에 저장하고 있습니다."');
            $.ajax({
                url: '/api/get-new-orders',
                type: 'POST',
                dataType: 'JSON',
                data: {
                    rememberToken
                },
                success: function(response) {
                    closePopup();
                    console.log(response);
                    if (!response.status) {
                        Swal.fire({
                            icon: 'error',
                            text: response.message,
                        });
                    }
                    Swal.fire({
                        icon: 'success',
                        text: response.message
                    });
                },
                error: function(response) {
                    closePopup();
                    console.log(response);
                }
            });
        }

        function showData() {
            popupLoader(0, '"주문 내역을 데이터베이스로부터 추출하겠습니다."');
            const orderStatus = $('input[name="orderStatus"]:checked').val();
            const vendors = getSelectedVendors();
            $.ajax({
                url: '/api/show-data',
                type: 'POST',
                dataType: 'JSON',
                data: {
                    rememberToken,
                    vendors,
                    orderStatus
                },
                success: function(response) {
                    closePopup();
                    console.log(response);
                    if (!response.status) {
                        Swal.fire({
                            icon: 'error',
                            text: response.message,
                        });
                    }
                    updateOrderTable(response.processedOrders);
                    updateLowBalanceAccounts(response.lowBalanceAccounts);
                },
                error: function(response) {
                    closePopup();
                    console.log(response);
                }
            });
        }

        function updateLowBalanceAccounts(accounts) {
            let accountsHtml = accounts.length > 0 ?
                accounts.map(account => `<li>${account}</li>`).join('') :
                '<li>없음</li>';
            $('#lowBalanceAccountsList').html(accountsHtml);
        }

        function updateOrderTable(response) {
            $('#numOrders').html(response.length);
            $('#orderwingResult').html(generateOrdersHtml(response));
            $('#totalAmt').html(`${numberFormat(calculateTotalAmount(response))}원`);
            $('.js-select2').select2();
            closePopup();
        }

        function generateOrdersHtml(orders) {
            return orders.map(order => generateOrderRowHtml(order)).join('');
        }

        function generateOrderRowHtml(order) {
            let partnerStatusHtml = order.isPartner ? '<b><p>셀윙 발주</p></b>' : '<b><p>도매윙 발주</p></b>';
            let orderVendorHtml = order.vendorName ? `<b><p>${order.vendorName}</p></b>` : '<b><p>도매윙</p></b>';
            let isActive = order.isActive === "N" ? '<span class="text-danger"> (품절)</span>' : '';
            let trackingNumber = order.trackingNumber ? `<p>송장 번호 : ${order.trackingNumber}</p>` : '';
            let orderTypeHtml = order.orderType === '교환' || order.orderType === '환불' ?
                `<h6 class="title">
                    <a class="text-danger" href="javascript:showOrderInfoModal('${order.productOrderNumber}');">
                        ${order.orderType}신청
                    </a>
                </h6>` :
                `<h6 class="title">${order.orderType}</h6>`;

            return `
                <tr>
                    <td>
                        <div class="row">
                            <div class="col">
                                <p><b>주문번호:</b><br>${order.productOrderNumber}<br><b>이름:</b><br>${order.receiverName}<br><b>연락처:</b><br>${order.receiverPhone}<br><b>주소:</b><br>${order.receiverAddress}<br><b>배송요청사항:</b><br>${order.receiverRemark}<br>
                            </div>
                        </div>
                    </td>
                    <td>
                        ${generateProductDetailsHtml(order, isActive)}
                    </td>
                    <td class="text-nowrap">
                        <div class="row g-gs mb-3">
                            <div class="col-12">
                                <p><b>이름:</b><br>${order.senderName}<br><b>연락처:</b><br>${order.senderPhone}<br><b>이메일:</b><br>${order.senderEmail}<br></p>
                                ${partnerStatusHtml}
                            </div>
                        </div>
                    </td>
                    <td>
                        <h6 class="title">${orderVendorHtml}</h6>
                        ${orderTypeHtml}
                        <p>${order.orderDate}</p>
                        ${trackingNumber}
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalProcess" onclick="productOrderNumber='${order.productOrderNumber}';">주문 처리</button>
                        <div class="d-flex mt-3">
                            <input class="form-control" type="text" id="adminRemarkInput${order.productOrderNumber}" />
                            <button class="btn btn-success text-nowrap" onclick="setAdminRemark('${order.productOrderNumber}');">메모</button>
                        </div>
                        <p id="adminRemark${order.productOrderNumber}">${order.adminRemark ? order.adminRemark : ''}</p>
                    </td>
                </tr>
                `;
        }

        function setAdminRemark(productOrderNumber) {
            const adminRemark = $('#adminRemarkInput' + productOrderNumber).val();
            $('#adminRemark' + productOrderNumber).html(adminRemark);
            $.ajax({
                url: '/api/set-memo',
                type: 'POST',
                dataType: 'JSON',
                data: {
                    rememberToken,
                    productOrderNumber,
                    adminRemark
                },
                success: function(response) {
                    closePopup();
                    if (!response.status) {
                        Swal.fire({
                            icon: 'error',
                            text: response.message,
                        });
                    }
                    console.log(response);
                },
                error: function(response) {
                    closePopup();
                    Swal.fire({
                        icon: 'error',
                        text: response.message,
                    });
                    console.log(response);
                }
            });
        }


        function generateProductDetailsHtml(order, isActive) {
            return `
                <div class="row mb-3">
                    <div class="col">
                        <a href="${order.productHref}" target="_blank"><img src="${order.productImage}" class="product-list-image" /></a>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col">
                        <h6 class='title'><a href="${order.productHref}" target="_blank">${order.productName}</a><br>${isActive}</h6>
                        <p><a href="${order.productHref}" target="_blank">${numberFormat(order.productPrice)}원</a></p>
                        <p><a href="javascript:view('${order.productCode}');">상세보기</a></p>
                    </div>
                </div>
                <div class="row">
                    <div class="col">
                        <p><b>수량:</b> ${numberFormat(order.quantity)}개<br>
                            <b>배송비:</b> ${numberFormat(order.shippingFee)}원<br>
                        <b>총액:</b> ${numberFormat(order.amount)}원</p>
                    </div>
                </div>`;
        }

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
                    console.log(response);
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
                    console.log(error);
                    closePopup();
                    swalWithReload('API 통신 중 에러가 발생했습니다.', 'error');
                }
            });
        }

        function showOrderInfoModal(productOrderNumber) {
            $.ajax({
                url: '/api/getOrderInfo',
                method: 'POST',
                data: {
                    rememberToken,
                    productOrderNumber
                },
                success: function(data) {
                    let imageContent = data.image ?
                        `<img src="${data.image}" class="w-100" />` :
                        '이미지가 없습니다.';
                    $('#modalContent').html(`
                        <p><b>성함:</b> ${data.name}</p>
                        <p><b>전화번호:</b> ${data.phone}</p>
                        <p><b>주소:</b> ${data.address}</p>
                        <p><b>요청사항:</b> ${data.receiverRemark}</p>
                        <p><b>사유:</b> ${data.type}</p>
                        <p><b>개수:</b> ${data.quantity}</p>
                        <p><b>총 가격:</b> ${data.amount}</p>
                        <p>
                            <b>증빙 이미지:</b><br>
                            ${imageContent}
                        </p>
                    `);
                    $('#orderInfoModal').modal('show');
                },
                error: function(error) {
                    console.error('Error fetching order information:', error);
                }
            });
        }


        function calculateTotalAmount(orders) {
            return orders.reduce((accumulator, order) => accumulator + parseInt(order.amount || 0, 10), 0);
        }
    </script>
@endsection
