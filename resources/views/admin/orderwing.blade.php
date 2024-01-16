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
                                    <th scope="col">B2B 업체</th>
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
        var orders = [{
                "orderedAt": "2024/01/16",
                "senderName": "갓블레스",
                "senderPhone": "010-4261-1192",
                "receiverName": "송영일(16A26)",
                "receiverPhone": "010-2024-0179",
                "postcode": "04342",
                "address": "서울 용산구 소월로44길 32 (이태원동)  미소랑빌 201호\t노크/벨X 문앞 (종2580)",
                "productName": "철주물옷걸이 2단 꽃잎",
                "quantity": "3",
                "productPrice": "2948",
                "shippingCost": "3500",
                "amount": "12344",
                "orderCode": "2024011616300503589A",
                "shippingRemark": "바로출고해주세요 ",
                "productCode": "CJYA1",
                "productCodeConditional": null,
                "productHref": "https://www.metaldiy.com/item/itemView.do?itemId=31900000427236",
                "productImage": "https://www.sellwing.kr/images/CDN/product/657f36f44b6ac.jpg",
                "orderStatus": "배송준비",
                "b2BName": "오너클랜"
            },
            {
                "orderedAt": "2024/01/16",
                "senderName": "지갑스토어",
                "senderPhone": "010-2115-1254",
                "receiverName": "지갑스토어(2DFA3)",
                "receiverPhone": "010-2115-1254",
                "postcode": "15069",
                "address": "경기 시흥시 군자로534번안길 18-9 (거모동, 녹원아파트) 101동 104호",
                "productName": "맥심 화이트골드 커피믹스117g100T 동서",
                "quantity": "1",
                "productPrice": "20198",
                "shippingCost": "3500",
                "amount": "23698",
                "orderCode": "2024011615183814407A",
                "shippingRemark": " ",
                "productCode": "JUG12",
                "productCodeConditional": null,
                "productHref": "https://www.metaldiy.com/item/itemView.do?itemId=31900001285887",
                "productImage": "https://www.sellwing.kr/images/CDN/product/6582673fc4c8c.jpg",
                "orderStatus": "배송준비",
                "b2BName": "오너클랜"
            },
            {
                "orderedAt": "2024/01/16",
                "senderName": "주식회사 저스트큐",
                "senderPhone": "070-5101-6117",
                "receiverName": "이경수(9A38)",
                "receiverPhone": "01046100667",
                "postcode": "57918 ",
                "address": "전라남도 순천시 낙안면 상송길 12 전라남도 순천시 낙안면 상송길 12",
                "productName": "말굽 무타공 티톡 TYPE C",
                "quantity": "1",
                "productPrice": "5220",
                "shippingCost": "3500",
                "amount": "8720",
                "orderCode": "2024011615132362878A",
                "shippingRemark": "jus_A09 / ",
                "productCode": "8K3Y4",
                "productCodeConditional": null,
                "productHref": "https://www.metaldiy.com/item/itemView.do?itemId=31900002090707",
                "productImage": "https://www.sellwing.kr/images/CDN/product/657dd4f60bc4d.jpg",
                "orderStatus": "배송준비",
                "b2BName": "오너클랜"
            },
            {
                "senderName": "도매의신",
                "senderPhone": "",
                "orderCode": 20240116143021612,
                "receiverName": "김주환",
                "receiverPhone": "010-8791-5609",
                "postcode": "02500",
                "address": "서울특별시 동대문구 망우로 82 (휘경동, 삼육서울병원) 물류부 .",
                "productCode": "XO1V5",
                "productName": "양변기일반부속 정면레바 WTG152 와토스 03 TYPE 1",
                "productPrice": "8982",
                "quantity": 10,
                "shippingCost": "3500",
                "orderedAt": "2024-01-16 14:36:33",
                "orderStatus": "신규주문",
                "shippingRemark": null,
                "productHref": "https://www.ds1008.com/goods/goods_view.php?goodsNo=8229",
                "productImage": "https://www.sellwing.kr/images/CDN/product/6594f4d6462f1.jpg",
                "amount": 93320,
                "b2BName": "도매의신"
            },
            {
                "senderName": "도매의신",
                "senderPhone": "",
                "orderCode": 20240116143021610,
                "receiverName": "최영수",
                "receiverPhone": "0502-3810-6229",
                "postcode": "06154",
                "address": "서울특별시 강남구 봉은사로 462 (삼성동, 세림타워) 11층 디모즈 .",
                "productCode": "MNDAN",
                "productName": "매입손잡이 원형 5515 실버도장 대",
                "productPrice": "3699",
                "quantity": 4,
                "shippingCost": "3500",
                "orderedAt": "2024-01-16 14:36:33",
                "orderStatus": "신규주문",
                "shippingRemark": null,
                "productHref": "https://www.metaldiy.com/item/itemView.do?itemId=31900000010268",
                "productImage": "https://www.sellwing.kr/images/CDN/product/657d6af2ec858.jpg",
                "amount": 18296,
                "b2BName": "도매의신"
            },
            {
                "orderStatus": "신규주문",
                "orderedAt": "2024-01-16",
                "senderName": "어니버스",
                "senderPhone": "010-4891-2033",
                "receiverName": "정지우",
                "receiverPhone": "010-2680-0018",
                "postcode": "61231",
                "address": "광주광역시 북구 자산로 27(신안동) 현진주택 ",
                "productName": "v호차 상하조절용",
                "quantity": 60,
                "productPrice": "3484",
                "shippingCost": "3500",
                "amount": "212540",
                "orderCode": "17053931011685HGP8YHK",
                "shippingRemark": "",
                "productCode": "5UTRZ",
                "productCodeConditional": "",
                "productHref": "http://babonara.co.kr/shop/goods/goods_view.php?goodsno=849&category=005003",
                "productImage": "https://www.sellwing.kr/images/product/656541d28364a.jpg",
                "b2BName": "도매아토즈"
            },
            {
                "orderStatus": "신규주문",
                "orderedAt": "2024-01-16",
                "senderName": "보리수 리어",
                "senderPhone": "010-4844-3839",
                "receiverName": "이영옥",
                "receiverPhone": "010-5425-3337",
                "postcode": "39272",
                "address": "경북 구미시 화신로10길 12 (광평동) 광평동 명품타운빌 B동 503호",
                "productName": "럭키 반짝이실리콘 은색실버펄 LC200270ml 90",
                "quantity": 10,
                "productPrice": "5440",
                "shippingCost": "3500",
                "amount": "57900",
                "orderCode": "1705392083590SH6LL12O",
                "shippingRemark": "",
                "productCode": "BY847",
                "productCodeConditional": "",
                "productHref": "https://www.ds1008.com/goods/goods_view.php?goodsNo=1000031724",
                "productImage": "https://www.sellwing.kr/images/CDN/product/6593bfc66769c.jpg",
                "b2BName": "도매아토즈"
            },
            {
                "orderStatus": "신규주문",
                "orderedAt": "2024-01-16",
                "senderName": "보리수 리어",
                "senderPhone": "010-4844-3839",
                "receiverName": "김지호",
                "receiverPhone": "010-2444-2553",
                "postcode": "10081",
                "address": "경기도 김포시 김포한강2로 362 (장기동, 청송마을중흥에스-클래스) 616동 1001호 ",
                "productName": "멀티탭 5구 1.5M 메인S W Office Factory",
                "quantity": 1,
                "productPrice": "10357",
                "shippingCost": "3500",
                "amount": "13857",
                "orderCode": "1705380345449M4T50K41",
                "shippingRemark": "",
                "productCode": "Y5JSP",
                "productCodeConditional": "",
                "productHref": "https://www.metaldiy.com/item/itemView.do?itemId=31900001287192",
                "productImage": "https://www.sellwing.kr/images/CDN/product/658281203761c.jpg",
                "b2BName": "도매아토즈"
            },
            {
                "orderCode": 53234626,
                "orderStatus": "결제완료",
                "productName": "메인멀티탭 5구 15m 2901110 TYPE 1",
                "productCode": "HW74K",
                "quantity": 2,
                "senderName": "기계천국",
                "senderPhone": "010-6633-5817",
                "receiverName": "이수이엔지 조경수부장님",
                "address": "충청북도 충주시 대소원면 첨단산업9로 36 (대소원면) 한국팜비오 ",
                "postcode": "27466",
                "receiverPhone": "010-2754-0391",
                "shippingRemark": null,
                "shippingCost": "3500",
                "orderedAt": "2024/01/16 14:04:48",
                "productPrice": "7164",
                "amount": 17828,
                "productHref": "https://www.ds1008.com/goods/goods_view.php?goodsNo=8451",
                "productImage": "https://www.sellwing.kr/images/CDN/product/658e69db45185.jpg",
                "b2BName": "도매꾹"
            }
        ];
        updateOrderTable(orders);

        function initOrderwing() {
            popupLoader(0, '"B2B 업체들로부터 신규 주문 내역들을 추출해올게요."');
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
                            <a href="${order.productHref}" target="_blank"><img src="${order.productImage}" width=100 height=100 /></a>
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
                    ${order.b2BName}
                </td>
                <td class="text-nowrap">
                    <div class="row mb-3">
                        <div class="col">
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
