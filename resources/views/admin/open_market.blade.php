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
                    <h6 class="title">신규 주문</h6>
                    <p>상품 정보를 클릭하면 해당 상품의 상세 페이지로 이동합니다.</p>
                    <button class="btn btn-primary mb-5" onclick="initOrderwing();">오더윙 가동</button>
                    <p>총 <span id="numOrders">0</span>개의 주문이 접수되었습니다.</p>
                    <div class="form-group">
                        <label class="form-label">금일 총 매출액</label>
                        <h5 class="card-title" id="totalAmt"></h5>
                    </div>
                    <div class="table-responsive">
                        <table class="table align-middle custom-table">
                            <thead>
                                <tr>
                                    <th>도매윙 닉네임</th>
                                    <th>주문마켓</th>
                                    <th>마켓주문번호</th>
                                    <th>마켓상품주문번호</th>
                                    <th>주문자</th>
                                    <th>상품명</th>
                                    <th>수량</th>
                                    <th>마켓 판매가</th>
                                    <th>총 판매가</th>
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
                url: '/api/open-market-orders',
                type: 'POST',
                dataType: 'JSON',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                data: {
                    openMarketIds,
                    startDate,
                    endDate
                },
                success: function(response) {
                    console.log(response);
                    updateOrderTable(response);
                    closePopup();
                },
                error: function(response) {
                    console.error('Error:', response);
                    closePopup();
                }
            });
        }

        function updateOrderTable(response) {
            let tableBody = document.getElementById('orderwingResult');
            tableBody.innerHTML = ''; // 기존 행을 비우기

            // 모든 data 요소를 반복
            response.data.forEach(dataItem => {
                // 각 data 요소 내의 api_result를 반복
                if (dataItem.api_result) {
                    dataItem.api_result.forEach(order => {
                        let row = `
                    <tr>
                        <td>${escapeHTML(dataItem.domewing_user_name) || 'N/A'}</td>
                        <td>${escapeHTML(order.market) || 'N/A'}</td>
                        <td>${escapeHTML(order.orderId) || 'N/A'}</td>
                        <td>${escapeHTML(order.productOrderId) || 'N/A'}</td>
                        <td>${escapeHTML(order.orderName) || 'N/A'}</td>
                        <td>${escapeHTML(order.productName) || 'N/A'}</td>
                        <td>${escapeHTML(order.quantity) || 'N/A'}</td>
                        <td>${escapeHTML(order.unitPrice) || 'N/A'}</td>
                        <td>${escapeHTML(order.totalPaymentAmount) || 'N/A'}</td>
                        <td>${escapeHTML(order.deliveryFeeAmount) || 'N/A'}</td>
                        <td>${escapeHTML(order.productOrderStatus) || 'N/A'}</td>
                        <td>${escapeHTML(order.orderDate) || 'N/A'}</td>
                    </tr>
                `;
                        tableBody.innerHTML += row; // 새로운 행 추가
                    });
                }
            });
        }

        function escapeHTML(text) {
            var element = document.createElement('div');
            element.textContent = text;
            return element.innerHTML;
        }
    </script>
@endsection
