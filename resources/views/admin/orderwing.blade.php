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
                    <table class="table text-nowrap align-middle custom-table">
                        <thead>
                            <tr>
                                <th scope="col">주문 넣은 사람</th>
                                <th scope="col">물건 받는 사람</th>
                                <th scope="col">상품 이미지</th>
                                <th scope="col">상품 정보</th>
                                <th scope="col">갯수</th>
                                <th scope="col">배송비</th>
                                <th scope="col">B2B 업체</th>
                                <th scope="col">ACTION</th>
                            </tr>
                        </thead>
                        <tbody id="orderwingResult">
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        var rememberToken = '{{ Auth::user()->remember_token }}';

        function initOrderwing() {
            popupLoader(0, '"B2B 업체들로부터 신규 주문 내역들을 추출해올게요."');
            $.ajax({
                url: '/api/orderwing',
                type: 'POST',
                dataType: 'JSON',
                data: {
                    rememberToken
                },
                success: function(response) {
                    console.log(response);
                    let html = "";
                    for (order of response) {
                        html += "<tr>";
                        html += "<td>" + order.senderName + "<br>" + order.senderPhone + "</td>";
                        html += "<td>" + order.receiverName + "<br>" + order.receiverPhone + "<br>" + order
                            .postcode + "<br>" + order.address + "</td>";
                        html += "<td><img src='" + order.productHref.productImage +
                            "' width=100 height=100 /></td>";
                        html += "<td>" + order.productName + "<br>" + order.productPrice + "원</td>";
                        html += "<td>" + order.quantity + "개</td>";
                        html += "<td>" + order.shippingCost + "원</td>";
                        html += "<td>" + order.b2BName + "</td>";
                        const productHref = order.productHref.productHref;
                        html += "<td><a href='" + productHref +
                            "' target='_blank' class='btn btn-primary'>상품 상세</a></td>";
                        html += "</tr>";
                    }
                    $('#orderwingResult').html(html);
                    closePopup();
                },
                error: function(response) {
                    closePopup();
                    console.log(response);
                }
            });
        }
    </script>
@endsection
