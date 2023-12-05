@extends('domewing.layouts.main')
@section('content')
    @include('domewing.partials.user_profile_short')

    <div class="p-lg-5 p-2" style="background-color: var(--pure-white);">
        <div class="row m-0">
            <div class="col-md-4 col-12">
                @include('domewing.partials.user_navbar')
            </div>
            <div class="col-md-8 col-12">
                @foreach ($groupedOrders as $orderId => $orders)
                    <div class="card-bordered p-4 mb-4" style="background: var(--thin-blue);">
                        <div class="d-flex justify-content-between align-items-center">
                            <h6 class="text-wrap text-truncate" style="color: var(--light-blue);">
                                {{ $orders->first()->supplierName }}</h6>
                            <a onclick="showDetails('{{ $orders->first()->transaction_id }}')">
                                <h6 class="text-end" style="color: var(--light-blue); cursor: pointer;">
                                    Transaction Details</h6>
                            </a>
                        </div>
                        <div class="p-1" style="border-bottom: 2px solid var(--dark-blue)"></div>
                        <div class="hstack g-gs horizontal-scrolling pt-1">
                            @foreach ($orders as $order)
                                <div>
                                    <img src="{{ $order->newImageHref }}" class="img-fluid tracking-img" />
                                    <h6 class="text-truncate py-3" style="color: var(--dark-blue); width:230px;">
                                        {{ $order->productName }}
                                    </h6>
                                </div>
                            @endforeach
                        </div>
                        <div class="p-2" style="border-bottom: 2px solid var(--cyan-blue)"></div>
                        <div class="d-flex flex-wrap justify-content-between">
                            <ul class="pricing-features fs-18px col-lg-7 col-12 pt-3" style="color: var(--dark-blue);">
                                <li>
                                    @php
                                        $grandTotal = 0;
                                        foreach ($orders as $order) {
                                            $grandTotal += $order->total_price;
                                        }
                                    @endphp
                                    <h6 class="w-50 align-self-center m-0" style="color: var(--dark-blue);">
                                        Total Payment</h6>
                                    <h6 class="w-50 align-self-center m-0" style="color: var(--dark-blue);">
                                        KRW {{ number_format($grandTotal, 2) }}
                                    </h6>
                                </li>
                                <li>
                                    <h6 class="w-50 align-self-center m-0" style="color: var(--dark-blue);">
                                        Shipping Method</h6>
                                    <h6 class="w-50 align-self-center m-0" style="color: var(--dark-blue);">
                                        Land
                                    </h6>
                                </li>
                                <li>
                                    <h6 class="w-50 align-self-center m-0" style="color: var(--dark-blue);">
                                        Delivery Status</h6>
                                    <h6 class="w-50 align-self-center m-0" style="color: var(--dark-blue);">
                                        Parcel at Kuala Lumpur Dispatch Centre
                                    </h6>
                                </li>
                                <li>
                                    <h6 class="w-50 align-self-center m-0" style="color: var(--dark-blue);">
                                        Receive By</h6>
                                    <h6 class="w-50 align-self-center m-0" style="color: var(--dark-blue);">
                                        06/01/2024</h6>
                                </li>
                            </ul>

                            {{-- <button class="btn mt-lg-auto justify-content-center col-lg-5 col-12 mt-3"
                                style="background: var(--pink);" onclick="showDelivery()">
                                <h5 class="text-white p-1 text-wrap">Check Delivery Status</h5>
                            </button> --}}
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Modal Transaction Details -->
    <div class="modal fade" tabindex="-1" id="modalDetail">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Transaction Details</h5>
                    <a href="#" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <em class="icon ni ni-cross"></em>
                    </a>
                </div>
                <div class="modal-body">
                    <div class="invoice-desc w-100 pt-0">
                        <ul class="list-plain pb-3">
                            <li><span class="w-30">Transaction ID</span>:<span id="transaction_id" class="w-70"></span>
                            </li>
                            <li><span class="w-30">Date Purchased</span>:<span id="date_purchased" class="w-70"></span>
                            </li>
                            <li><span class="w-30">Payment Method</span>:<span id="payment_method" class="w-70"></span>
                            </li>
                            <li><span class="w-30">Total Paid</span>:<span id="total_paid" class="w-70"></span>
                            </li>
                        </ul>

                        <ul class="list-plain pb-3">
                            <li><span class="w-30">Delivery Address</span>:<span id="address" class="w-70"></span>
                            </li>
                            <li><span class="w-30">Contact Name</span>:<span id="contact_name" class="w-70"></span>
                            </li>
                            <li><span class="w-30">Contact Number</span>:<span id="contact_number" class="w-70"></span>
                            </li>
                            <li><span class="w-30">Email</span>:<span id="email" class="w-70"></span>
                            </li>
                        </ul>

                        <ul class="list-plain py-2">
                            <h5 class="title">Item 1</h5>
                            <li><span class="w-30">Quantity</span>:<span id="product_quantity" class="w-70"></span>
                            </li>
                            <li><span class="w-30">Product Price</span>:<span id="product_price" class="w-70"></span>
                            </li>
                            <li><span class="w-30">Shipping Fee</span>:<span id="product_shipping" class="w-70"></span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @include('domewing.partials.modal')
@endsection

@section('scripts')
    <script>
        function showDelivery() {
            swal.fire({
                icon: 'success',
                title: 'Delivery Detail Here'
            })
        }

        function showDetails(id) {
            //to ensure loading modal doesnot interrupt
            $('#modalLoading').modal({
                backdrop: 'static',
                keyboard: false
            });
            $('#modalLoading').modal('show');

            $.ajax({
                url: '/api/member/get-transaction-details/' + id, // Replace with your endpoint to fetch details
                type: 'GET',
                success: function(response) {
                    $('#modalLoading').on('shown.bs.modal', function(e) {
                        $('#transaction_id').text(response.transaction.transaction_id);
                        const datePurchased = new Date(response.transaction.created_at);
                        const formattedDate = formatDate(datePurchased); // Function to format date
                        $('#date_purchased').text(formattedDate);
                        $('#payment_method').text(response.transaction.payment_method);
                        $('#total_paid').text(response.total);
                        $('#address').text(response.transaction.location);
                        $('#contact_name').text(response.transaction.contact_name);
                        $('#contact_number').text(response.transaction.contact_number);
                        $('#email').text(response.transaction.email);

                        // Populate items
                        populateItems(response.items);

                        $('#modalLoading').modal('hide');
                        $('#modalDetail').modal('show');
                    })
                },
                error: function(xhr, status, error) {
                    $('#modalLoading').on('shown.bs.modal', function(e) {
                        $('#modalLoading').modal('hide');
                        $('#modalFailTitle').text('ERROR');
                        $('#modalFailMessage').text('Transaction Not Found');
                        $('#modalFail').modal('show');
                    });
                }
            });
        }

        function populateItems(items) {
            const itemList = $('.invoice-desc ul:last-child');
            itemList.empty();

            items.forEach(item => {
                itemList.append(`<ul class="list-plain py-2">
                            <h5 class="title">${item.productName}</h5>
                            <li><span class="w-30">Quantity</span>:<span class="w-70">${item.quantity}</span></li>
                            <li><span class="w-30">Product Price</span>:<span class="w-70">${item.price_at}</span></li>
                            <li><span class="w-30">Shipping Fee</span>:<span class="w-70">${item.shipping_at}</span></li>
                        </ul>`);
            });
        }

        function formatDate(date) {
            const options = {
                day: '2-digit',
                month: 'numeric',
                year: 'numeric',
                hour: 'numeric',
                minute: 'numeric',
                hour12: false
            };
            const formattedDate = date.toLocaleDateString('en-UK', options);
            return formattedDate.replace(',', ''); // Remove comma between date and year
        }
    </script>
@endsection
