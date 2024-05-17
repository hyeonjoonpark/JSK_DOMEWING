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
    </style>
@endsection
@section('title')
    오더윙
@endsection

@section('subtitle')
    <p>오더윙을 통해 오픈마켓 업체로부터의 주문 내용을 정리하고 정산합니다. 또한, 송장 번호 자동 추출 기능이 포함되어 있어 효율적인 주문 관리가 가능합니다.</p>
@endsection

@section('content')
    <div class="row g-gs mb-3">
        <div class="col">
            <div class="card card-bordered">
                <div class="card-inner">
                    <div class="form-group">
                        <label class="form-label">오픈마켓 리스트 ({{ count($openMarkets) }})</label>
                        <div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="select-all">
                                <label class="form-check-label" for="select-all">
                                    전체 선택
                                </label>
                            </div>
                            @foreach ($openMarkets as $openMarket)
                                <div class="form-check">
                                    <input class="form-check-input market-checkbox" type="checkbox"
                                        value="{{ $openMarket->id }}" id="market-{{ $openMarket->id }}">
                                    <label class="form-check-label" for="market-{{ $openMarket->id }}">
                                        {{ $openMarket->name }}
                                    </label>
                                </div>
                            @endforeach
                        </div>
                        <div class="form-group">
                            <label for="startDate">시작 날짜:</label>
                            <input type="date" id="startDate" name="startDate" class="form-control">
                            <label for="endDate">종료 날짜:</label>
                            <input type="date" id="endDate" name="endDate" class="form-control">
                            <p>날짜 미 선택시 현재일 기준 7일 동안의 내역이 보여집니다.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row g-gs">
        <div class="col">
            <div class="card card-bordered">
                <div class="card-inner">
                    <h6 class="title">주문 내역</h6>
                    <p>상품 정보를 클릭하면 해당 상품의 상세 페이지로 이동합니다.</p>
                    <button class="btn btn-primary mb-5" onclick="initOrderwing();">조회하기</button>
                    <div class="table-responsive">
                        <table class="table align-middle custom-table">
                            <thead>
                                <tr>
                                    <th>도매윙 닉네임</th>
                                    <th>주문마켓</th>
                                    <th>마켓주문번호</th>
                                    <th>주문자</th>
                                    <th>상품명</th>
                                    <th>총 상품 판매가</th>
                                    <th>마켓배송비</th>
                                    <th>마켓주문상태</th>
                                    <th>마켓주문일</th>
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
    <!-- 상품 상세 정보 모달 -->
    <div class="modal fade" id="productDetailModal" tabindex="-1" role="dialog" aria-labelledby="productDetailModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="productDetailModalLabel">상품 상세 정보</h5>
                    <button type="button" class="close" aria-label="Close" onclick="closeModal()">
                        <span aria-hidden="true">&times;</span>
                    </button>

                </div>
                <div class="modal-body">
                    <div class="info-section"><strong>도매윙 닉네임:</strong> <span id="modalUserName"></span></div>
                    <div class="info-section"><strong>오픈 마켓:</strong> <span id="modalMarket"></span></div>
                    <div class="info-section"><strong>마켓 주문 번호:</strong> <span id="modalOrderId"></span></div>
                    <div class="info-section"><strong>마켓 상품 주문번호:</strong> <span id="modalProductOrderId"></span>
                    </div>
                    <div class="info-section"><strong>주문자:</strong> <span id="modalOrderName"></span></div>
                    <div class="info-section"><strong>상품명:</strong> <span id="modalProductName"></span></div>
                    <div class="info-section"><strong>수량:</strong> <span id="modalQuantity"></span></div>
                    <div class="info-section"><strong>마켓 판매가:</strong> <span id="modalUnitPrice"></span></div>
                    <div class="info-section"><strong>총 상품 판매가:</strong> <span id="modalTotalPayment"></span></div>
                    <div class="info-section"><strong>마켓 배송비:</strong> <span id="modalDeliveryFee"></span></div>
                    <div class="info-section"><strong>마켓 주문상태:</strong> <span id="modalOrderStatus"></span></div>
                    <div class="info-section"><strong>마켓 주문일:</strong> <span id="modalOrderDate"></span></div>
                    <div class="info-section"><strong>수신자:</strong> <span id="modalReceiverName"></span></div>
                    <div class="info-section"><strong>수신자 전화번호:</strong> <span id="modalReceiverPhone"></span></div>
                    <div class="info-section"><strong>우편번호:</strong> <span id="modalPostCode"></span></div>
                    <div class="info-section"><strong>주소지명:</strong> <span id="modalAddressName"></span></div>
                    <div class="info-section"><strong>주소:</strong> <span id="modalAddress"></span></div>
                    <div class="info-section"><strong>상품코드:</strong> <span id="modalProductCode"></span></div>
                </div>
            </div>
        </div>
    </div>
    </div>
@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const selectAllCheckbox = document.getElementById('select-all');
            const marketCheckboxes = document.querySelectorAll('.market-checkbox');

            selectAllCheckbox.addEventListener('change', function() {
                marketCheckboxes.forEach(checkbox => {
                    checkbox.checked = selectAllCheckbox.checked;
                });
            });

            marketCheckboxes.forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    if ([...marketCheckboxes].every(mark => mark.checked)) {
                        selectAllCheckbox.checked = true;
                    } else {
                        selectAllCheckbox.checked = false;
                    }
                });
            });
        });

        function initOrderwing() {
            var openMarketIds = [];
            document.querySelectorAll('.market-checkbox:checked').forEach(function(checkbox) {
                openMarketIds.push(checkbox.value);
            });
            if (openMarketIds.length < 1) {
                return Swal.fire({
                    icon: 'warning',
                    text: '최소 하나의 오픈마켓을 선택해야 합니다.'
                });
            }

            var startDate = document.getElementById('startDate').value;
            var endDate = document.getElementById('endDate').value;

            if (new Date(startDate) > new Date(endDate)) {
                return Swal.fire({
                    icon: 'warning',
                    text: '시작날짜는 종료날짜보다 전이어야 합니다.'
                });
            }
            popupLoader(0, '신규 주문 내역을 오픈마켓으로부터 추출하겠습니다.');
            $.ajax({
                url: '/api/partner/open-market-orders',
                type: 'POST',
                dataType: 'JSON',
                data: {
                    openMarketIds,
                    startDate,
                    endDate,
                    apiToken
                },
                success: function(response) {
                    closePopup();
                    if (!response.status) {
                        // 서버로부터 반환된 오류 메시지와 부족한 금액을 표시
                        Swal.fire({
                            icon: 'error',
                            title: response.message,
                            text: '추가 필요 금액: ' + formatCurrency(response.data)
                        });
                    } else {
                        updateOrderTable(response);
                        console.log(response);
                    }
                },
                error: function(xhr, status, error) {
                    closePopup();
                    console.error('Error:', error);
                    Swal.fire({
                        icon: 'error',
                        title: '네트워크 오류',
                        text: '요청 처리 중 문제가 발생했습니다.'
                    });
                }
            });
        }


        function updateOrderTable(response) {
            let tableBody = document.getElementById('orderwingResult');
            tableBody.innerHTML = '';

            response.data.forEach(dataItem => {
                if (dataItem.api_result) {
                    dataItem.api_result.forEach(order => {
                        let augmentedOrder = {
                            ...order,
                            domewing_user_name: dataItem.domewing_user_name
                        };
                        let row = document.createElement('tr');
                        let orderDetailJson = encodeURIComponent(JSON.stringify(augmentedOrder));
                        row.innerHTML = `
                    <td>${escapeHTML(dataItem.domewing_user_name) || 'N/A'}</td>
                    <td>${escapeHTML(order.market) || 'N/A'}</td>
                    <td>${escapeHTML(order.orderId) || 'N/A'}</td>
                    <td>${escapeHTML(order.orderName) || 'N/A'}</td>
                    <td><a href="javascript:showProductDetail('${orderDetailJson}');">${escapeHTML(order.productName) || 'N/A'}</a></td>
                    <td>${formatCurrency(escapeHTML(order.totalPaymentAmount)) || 'N/A'}</td>
                    <td>${formatCurrency(escapeHTML(order.deliveryFeeAmount)) || 'N/A'}</td>
                    <td>${escapeHTML(order.productOrderStatus) || 'N/A'}</td>
                    <td>${escapeHTML(order.orderDate) || 'N/A'}</td>
                `;
                        tableBody.appendChild(row);
                    });
                }
            });
        }

        function showProductDetail(encodedOrder) {
            var order = JSON.parse(decodeURIComponent(encodedOrder));
            $('#modalUserName').text(order.domewing_user_name || 'N/A');
            $('#modalMarket').text(order.market || 'N/A');
            $('#modalOrderId').text(order.orderId || 'N/A');
            $('#modalProductOrderId').text(order.productOrderId || 'N/A');
            $('#modalOrderName').text(order.orderName || 'N/A');
            $('#modalProductName').text(order.productName || 'N/A');
            $('#modalUnitPrice').text(formatCurrency(order.unitPrice) || 'N/A');
            $('#modalQuantity').text(order.quantity || 'N/A');
            $('#modalTotalPayment').text(formatCurrency(order.totalPaymentAmount) || 'N/A');
            $('#modalDeliveryFee').text(formatCurrency(order.deliveryFeeAmount) || '0');
            $('#modalOrderStatus').text(order.productOrderStatus || 'N/A');
            $('#modalOrderDate').text(order.orderDate || 'N/A');
            $('#modalReceiverName').text(order.receiverName || 'N/A');
            $('#modalReceiverPhone').text(order.receiverPhone || 'N/A');
            $('#modalPostCode').text(order.postCode || 'N/A');
            $('#modalAddress').text(order.address || 'N/A');
            $('#modalAddressName').text(order.addressName || 'N/A');
            $('#modalProductCode').text(order.productCode || 'N/A');
            $('#productDetailModal').modal('show');
        }

        function formatCurrency(value) {
            // 숫자로 강제 변환
            const numericValue = Number(value);
            // 숫자 변환이 제대로 되지 않은 경우를 대비하여 검사
            if (isNaN(numericValue)) {
                return 'Invalid number'; // 숫자가 아니면 에러 메시지 반환
            }
            return numericValue.toLocaleString('ko-KR') + '원';
        }





        function closeModal() {
            var modal = document.getElementById('productDetailModal');
            $('#productDetailModal').modal('hide');
            modal.style.display = 'none';
        }

        function escapeHTML(text) {
            var element = document.createElement('div');
            element.textContent = text;
            return element.innerHTML;
        }
    </script>
@endsection
