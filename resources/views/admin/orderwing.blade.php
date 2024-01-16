@extends('layouts.main')

@section('title')
    오더윙
@endsection

@section('subtitle')
    <p>오더윙을 통해 B2B 업체로부터의 주문 내용을 정리하고 정산합니다. 또한, 송장 번호 자동 추출 기능이 포함되어 있어 효율적인 주문 관리가 가능합니다.</p>
@endsection

@section('content')
    <div class="row g-gs">
        <div class="col">
            <button class="btn btn-primary mb-5" onclick="initOrderwing();">오더윙 가동</button>
            <div class="card card-bordered">
                <div class="card-inner">
                    <h6 class="title">신규 주문</h6>
                    <p>상품 정보를 클릭하면 해당 상품의 상세 페이지로 이동합니다.</p>
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
        var rememberToken = '{{ Auth::user()->remember_token }}';

        function initOrderwing() {
            popupLoader(0, '"신규 주문 내역을 B2B 업체로부터 추출하겠습니다."');
            $.ajax({
                url: '/api/orderwing',
                type: 'POST',
                dataType: 'JSON',
                data: {
                    rememberToken
                },
                success: updateOrderTable,
                error: function(response) {
                    closePopup();
                    console.log(response);
                }
            });
        }

        function updateOrderTable(response) {
            console.log(response);
            let html = response.map(order => `
            <tr>
                <td>
                    <div class="row">
                        <div class="col">
                            <p><b>이름:</b><br>${order.receiverName}<br><b>연락처</b>:<br>${order.receiverPhone}<br><b>우편번호 | 주소:</b><br>${order.postcode} | ${order.address}<br><b>배송메시지</b>:<br>${order.shippingRemark}</p>
                        </div>
                    </div>
                </td>
                <td>
                    <div class="row mb-3">
                        <div class="col">
                            <a href="${order.productHref}" target="_blank"><img src="${order.productImage}" class="product-list-image" /></a>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col">
                            <h6 class='title'><a href="${order.productHref}" target="_blank">${order.productName}</a></h6>
                            <p><a href="${order.productHref}" target="_blank">${numberFormat(order.productPrice)}원</a></p>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col">
                            <p><b>수량:</b> ${numberFormat(order.quantity)}개<br>
                                <b>배송비:</b> ${numberFormat(order.shippingCost)}원<br>
                            <b>총액:</b> ${numberFormat(order.amount)}원</p>
                        </div>
                    </div>
                </td>
                <td class="text-nowrap">
                    <h6 class="title">${order.orderStatus}</h6>
                    <p>${order.orderedAt}</p>
                </td>
                <td class="text-nowrap">
                    <div class="row mb-3">
                        <div class="col">
                            <h6>${order.b2BName}</h6>
                            <p><b>이름:</b><br>${order.senderName}<br><b>연락처:</b><br>${order.senderPhone}</p>
                        </div>
                    </div>
                </td>
            </tr>
            `).join('');
            $('#orderwingResult').html(html);
            closePopup();
        }
    </script>
@endsection
