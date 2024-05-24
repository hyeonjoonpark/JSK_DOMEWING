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
                    <button class="btn btn-primary mb-5" onclick="initIndex();">조회하기</button>
                    <button class="btn btn-primary mb-5" onclick="initOrderwing();">신규주문 저장하기</button>
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
@endsection

@section('scripts')
    <script>
        var rememberToken = '{{ Auth::guard('user')->user()->remember_token }}';

        function initIndex() {
            popupLoader(0, '"신규 주문 내역을 데이터베이스로부터 추출하겠습니다."');
            $.ajax({
                url: '/api/get-new-orders',
                type: 'POST',
                dataType: 'JSON',
                data: {
                    rememberToken
                },
                success: function(response) {
                    console.log(response);
                    updateOrderTable(response);
                    closePopup();
                },
                error: function(response) {
                    closePopup();
                    console.log(response);
                }
            });
        }

        function initOrderwing() {
            popupLoader(0, '"신규 주문 내역을 오픈마켓으로부터 저장하겠습니다."');
            $.ajax({
                url: '/api/get-new-all-orders',
                type: 'POST',
                dataType: 'JSON',
                data: {
                    rememberToken
                },
                success: function(response) {
                    if (response.status === false) {
                        console.log(response);
                        closePopup();
                        Swal.fire({
                            icon: 'error',
                            text: response.message,
                        });
                    } else {
                        console.log(response);
                        closePopup();
                        Swal.fire({
                            icon: 'success',
                            text: response.message
                        });
                    }
                },
                error: function(response) {
                    console.error(response);
                    closePopup();
                }
            });
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
            let deliveryCompanyHtml = `<option value="-1">택배사 선택</option>`;
            for (const deliveryCompany of order.deliveryCompanies) {
                deliveryCompanyHtml += `<option value="${deliveryCompany.id}">${deliveryCompany.name}</option>`;
            }
            let partnerStatusHtml = order.isPartner ? '<b><p>파트너 계정입니다.</p></b>' : '<b><p>일반 회원입니다.</p></b>';
            let orderVendorHtml = order.vendorName ? `<b><p>${order.vendorName}</p></b>` : '<b><p>도매윙</p></b>';
            let isActive = order.isActive === "N" ? '<span class="text-danger"> (품절)</span>' : '';

            return `
            <tr>
                <td>
                    <div class="row">
                        <div class="col">
                            <p><b>이름:</b><br>${order.receiverName}<br><b>연락처:</b><br>${order.receiverPhone}<br><b>주소:</b><br>${order.receiverAddress}<br><b>배송요청사항:</b><br>${order.receiverRemark}<br>
                        </div>
                    </div>
                </td>
                <td>
                    ${generateProductDetailsHtml(order, isActive)}
                </td>
                <td class="text-nowrap">
                    <div class="row g-gs mb-3">
                        <div class="col-12">
                            <p><b>닉네임:</b><br>${order.senderNickName}<br><b>이름:</b><br>${order.senderName}<br><b>연락처:</b><br>${order.senderPhone}<br><b>이메일:</b><br>${order.senderEmail}<br></p>
                            ${partnerStatusHtml}
                        </div>
                    </div>
                </td>
                <td class="text-nowrap">
                    <h6 class="title">${orderVendorHtml}</h6>
                    <h6 class="title">${order.orderStatus}</h6>
                    <div class="col-auto">
                        <select class="form-select js-select2" id="deliveryCompany` + order.productOrderNumber + `">
                            ` + deliveryCompanyHtml + `
                        </select>
                        <input type="number" class="form-control" id="trackingNumber${order.productOrderNumber}" placeholder="송장번호">
                        <button class="btn btn-primary" onclick="initCreateDelivery('` + order.productOrderNumber + `');">확인</button>
                        <button class="btn btn-danger" onclick="cancelDelivery('${order.productOrderNumber}');">취소</button>
                    </div>
                </td>
            </tr>`;
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

        function calculateTotalAmount(orders) {
            return orders.reduce((accumulator, order) => accumulator + parseInt(order.amount || 0, 10), 0);
        }

        function initCreateDelivery(productOrderNumber) {
            const trackingNumber = $('#trackingNumber' + productOrderNumber).val();
            const deliveryCompanyId = $('#deliveryCompany' + productOrderNumber).val();
            $.ajax({
                url: '/api/save-tracking-info',
                type: 'POST',
                dataType: 'JSON',
                data: {
                    rememberToken,
                    trackingNumber,
                    deliveryCompanyId,
                    productOrderNumber,
                },
                success: function(response) {
                    if (response.status === false) {
                        Swal.fire({
                            icon: 'error',
                            text: response.message,
                        });
                    } else {
                        console.log('Tracking info saved:', response);
                        Swal.fire({
                            icon: 'success',
                            text: '택배사 및 송장번호 기입에 성공하였습니다.'
                        });
                    }
                },
                error: function(response) {
                    console.error('Error saving tracking info:', response);
                }
            });
        }

        function cancelDelivery(productOrderNumber) {
            Swal.fire({
                title: '주문 취소',
                input: 'textarea',
                showCancelButton: true,
                confirmButtonText: '취소하기',
                cancelButtonText: '닫기',
                inputValidator: (value) => {
                    if (!value) {
                        return '취소 사유를 입력해야 합니다!'
                    }
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    const remark = result.value;
                    $.ajax({
                        url: '/api/cancel-order',
                        type: 'POST',
                        dataType: 'JSON',
                        data: {
                            rememberToken,
                            productOrderNumber,
                            remark
                        },
                        success: function(response) {
                            if (response.status === false) {
                                Swal.fire({
                                    icon: 'error',
                                    text: response.message,
                                });
                            } else {
                                console.log('Order cancelled:', response);
                                Swal.fire({
                                    icon: 'success',
                                    text: '주문 취소에 성공하였습니다.'
                                });
                            }
                        },
                        error: function(response) {
                            console.error('Error cancelling order:', response);
                            Swal.fire({
                                icon: 'error',
                                text: '주문 취소 중 오류가 발생했습니다.'
                            });
                        }
                    });
                }
            });
        }
    </script>
@endsection
