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
                                    <th scope="col">수취인 정보</th>
                                    <th scope="col">주문 정보</th>
                                    <th scope="col">주문상태</th>
                                    <th scope="col">주문자 정보</th>
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
            var checkedIds = [];
            document.querySelectorAll('.market-checkbox:checked').forEach(function(checkbox) {
                checkedIds.push(checkbox.value);
            });
            popupLoader(0, '신규 주문 내역을 오픈마켓으로부터 추출하겠습니다.');
            $.ajax({
                url: 'open-market-orders',
                type: 'POST',
                dataType: 'JSON',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                data: {
                    'openMarketIds': checkedIds
                },
                success: function(response) {
                    updateOrderTable(response);
                    closePopup();
                },
                error: function(response) {
                    console.error('Error:', response);
                    closePopup();
                }
            });
        }
    </script>
@endsection
