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
                                        Receive By</h6>
                                    <h6 class="w-50 align-self-center m-0" style="color: var(--dark-blue);">
                                        06/01/2024</h6>
                                </li>
                            </ul>
                        </div>

                        <h4 class="pt-5" style="color: var(--dark-blue);">Write A Review</h4>
                        <div class="d-flex flex-wrap">
                            <h5 class="my-auto pe-3" style="color: var(--dark-blue);">Rate</h5>
                            <form class="custom-rating">
                                @for ($i = 1; $i <= 5; $i++)
                                    <label>
                                        <input type="radio" name="stars_{{ $orders->first()->transaction_id }}"
                                            value="{{ $i }}" />
                                        @for ($j = 0; $j < $i; $j++)
                                            <span class="fa-solid fa-star icon"></span>
                                        @endfor
                                    </label>
                                @endfor
                            </form>
                        </div>
                        <div class="form-group py-2">
                            <textarea id="review_{{ $orders->first()->transaction_id }}" type="text" class="form-control fs-18px"
                                style="color: var(--dark-blue)" placeholder="Type a message (Optional)"></textarea>
                            <span id="ratingError_{{ $orders->first()->transaction_id }}" class="invalid"
                                style="display: inline-block;"></span>
                        </div>
                        <div class="d-flex justify-content-end">
                            <button class="btn mt-auto" style="background: var(--pink);">
                                <a onclick="submitReview('{{ $orders->first()->transaction_id }}')">
                                    <h5 class="text-white px-3">Submit Your Review</h5>
                                </a>
                            </button>
                        </div>
                    </div>
                @endforeach

                @if (count($groupedReviewedOrders) > 0)
                    <h3 style="color: var(--dark-blue)">Submitted Reviews</h3>
                    <div class="pb-3" style="border-top: 2px solid var(--dark-blue)"></div>
                @endif

                @foreach ($groupedReviewedOrders as $orderId => $orders)
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
                                        Receive By</h6>
                                    <h6 class="w-50 align-self-center m-0" style="color: var(--dark-blue);">
                                        06/01/2024</h6>
                                </li>
                            </ul>
                        </div>

                        <h4 class="pt-5" style="color: var(--dark-blue);">Write A Review</h4>
                        <div class="d-flex flex-wrap">
                            <h5 class="my-auto pe-3" style="color: var(--dark-blue);">Rate</h5>
                            <form class="custom-rating">
                                @for ($i = 1; $i <= 5; $i++)
                                    <label>
                                        <input type="radio" name="stars_{{ $orders->first()->transaction_id }}"
                                            value="{{ $i }}"
                                            {{ $orders->first()->rating == $i ? 'checked' : '' }} />
                                        @for ($j = 0; $j < $i; $j++)
                                            <span class="fa-solid fa-star icon"></span>
                                        @endfor
                                    </label>
                                @endfor
                            </form>
                        </div>
                        <div class="form-group py-2">
                            <textarea id="review_{{ $orders->first()->transaction_id }}" type="text" class="form-control fs-18px"
                                style="color: var(--dark-blue)" placeholder="Type a message (Optional)">{{ $orders->first()->review }}</textarea>
                            <span id="ratingError_{{ $orders->first()->transaction_id }}" class="invalid"
                                style="display: inline-block;"></span>
                        </div>
                        <div class="d-flex justify-content-end">
                            <button class="btn btn-primary mt-auto">
                                <a onclick="editReview('{{ $orders->first()->transaction_id }}')">
                                    <h5 class="text-white px-3">Edit Your Review</h5>
                                </a>
                            </button>
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
                            <li><span class="w-30">Transaction ID</span>:<span id="transaction_id"
                                    class="w-70"></span>
                            </li>
                            <li><span class="w-30">Date Purchased</span>:<span id="date_purchased"
                                    class="w-70"></span>
                            </li>
                            <li><span class="w-30">Payment Method</span>:<span id="payment_method"
                                    class="w-70"></span>
                            </li>
                            <li><span class="w-30">Total Paid</span>:<span id="total_paid" class="w-70"></span>
                            </li>
                        </ul>

                        <ul class="list-plain pb-3">
                            <li><span class="w-30">Delivery Address</span>:<span id="address" class="w-70"></span>
                            </li>
                            <li><span class="w-30">Contact Name</span>:<span id="contact_name" class="w-70"></span>
                            </li>
                            <li><span class="w-30">Contact Number</span>:<span id="contact_number"
                                    class="w-70"></span>
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
                            <li><span class="w-30">Shipping Fee</span>:<span id="product_shipping"
                                    class="w-70"></span>
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
        function submitReview(id) {
            var rating = $('input[name="stars_' + id + '"]:checked').val();
            const text = $('#review_' + id).val();
            const transaction_id = id;
            const remember_token = '{{ Auth::guard('member')->user()->remember_token }}';

            if (rating == null) {
                rating = "0";
            }

            const requestData = {
                remember_token: remember_token,
                rating: rating,
                review: text,
                transaction_id: transaction_id
            }

            const errorElement = document.getElementById(`ratingError_${transaction_id}`);
            errorElement.textContent = '';

            $.ajax({
                url: '/api/member/submit-review',
                type: 'post',
                dataType: 'json',
                data: requestData,
                success: function(response) {
                    $('#modalLoading').modal('hide');
                    const status = parseInt(response.status);

                    if (status == 1) {
                        $('#modalSuccessTitle').text(response.title);
                        $('#modalSuccessMessage').text(response.return);
                        $('#modalSuccess').modal('show');
                        $('#modalSuccess').on('hidden.bs.modal', function(e) {
                            location.reload();
                        });
                    } else if (status == -2) {
                        $('#modalFailTitle').text(response.title);
                        $('#modalFailMessage').text(response.return);
                        $('#modalFail').modal('show');
                        $('#modalFail').on('hidden.bs.modal', function(e) {
                            location.href = '/domewing/auth/login';
                        });
                    } else {
                        $('#modalFailTitle').text(response.title);
                        $('#modalFailMessage').text(response.return);
                        $('#modalFail').modal('show');
                        $('#modalFail').on('hidden.bs.modal', function(e) {
                            location.reload();
                        });
                    }
                },
                error: function(response) {
                    if (response.status === 422) {
                        // Validation failed, handle the errors
                        const rating = response.responseJSON.rating;
                        errorElement.textContent = rating;
                    } else {
                        $('#modalLoading').modal('hide');
                        $('#modalFailTitle').text('ERROR');
                        $('#modalFailMessage').text(
                            'Unexpected Error Occured. Please Try Again Later.');
                        $('#modalFail').modal('show');
                        $('#modalFail').on('hidden.bs.modal', function(e) {
                            location.reload();
                        });
                    }
                }
            });

        }

        function editReview(id) {
            var rating = $('input[name="stars_' + id + '"]:checked').val();
            const text = $('#review_' + id).val();
            const transaction_id = id;
            const remember_token = '{{ Auth::guard('member')->user()->remember_token }}';

            if (rating == null) {
                rating = "0";
            }

            const requestData = {
                remember_token: remember_token,
                rating: rating,
                review: text,
                transaction_id: transaction_id
            }

            const errorElement = document.getElementById(`ratingError_${transaction_id}`);
            errorElement.textContent = '';

            $.ajax({
                url: '/api/member/edit-review',
                type: 'post',
                dataType: 'json',
                data: requestData,
                success: function(response) {
                    $('#modalLoading').modal('hide');
                    const status = parseInt(response.status);

                    if (status == 1) {
                        $('#modalSuccessTitle').text(response.title);
                        $('#modalSuccessMessage').text(response.return);
                        $('#modalSuccess').modal('show');
                        $('#modalSuccess').on('hidden.bs.modal', function(e) {
                            location.reload();
                        });
                    } else if (status == -2) {
                        $('#modalFailTitle').text(response.title);
                        $('#modalFailMessage').text(response.return);
                        $('#modalFail').modal('show');
                        $('#modalFail').on('hidden.bs.modal', function(e) {
                            location.href = '/domewing/auth/login';
                        });
                    } else {
                        $('#modalFailTitle').text(response.title);
                        $('#modalFailMessage').text(response.return);
                        $('#modalFail').modal('show');
                        $('#modalFail').on('hidden.bs.modal', function(e) {
                            location.reload();
                        });
                    }
                },
                error: function(response) {
                    if (response.status === 422) {
                        // Validation failed, handle the errors
                        const rating = response.responseJSON.rating;
                        errorElement.textContent = rating;
                    } else {
                        $('#modalLoading').modal('hide');
                        $('#modalFailTitle').text('ERROR');
                        $('#modalFailMessage').text(
                            'Unexpected Error Occured. Please Try Again Later.');
                        $('#modalFail').modal('show');
                        $('#modalFail').on('hidden.bs.modal', function(e) {
                            location.reload();
                        });
                    }
                }
            });
        }

        $("#modalDetail").on("hidden.bs.modal", function() {
            location.reload();
        });

        function showDetails(id) {
            //to ensure loading modal doesnot interrupt
            $('#modalLoading').modal({
                backdrop: 'static',
                keyboard: false
            });
            $('#modalLoading').modal('show');

            $('#modalLoading').on('shown.bs.modal', function(e) {
                $.ajax({
                    url: '/api/member/get-transaction-details/' +
                        id, // Replace with your endpoint to fetch details
                    type: 'GET',
                    success: function(response) {

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

                    },
                    error: function(xhr, status, error) {
                        $('#modalLoading').modal('hide');
                        $('#modalFailTitle').text('ERROR');
                        $('#modalFailMessage').text('Transaction Not Found');
                        $('#modalFail').modal('show');
                    }
                });
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
