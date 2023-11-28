@extends('domewing.layouts.main')
@section('content')
    <div class="px-lg-5 px-2" style="background: var(--thin-blue); padding-top: 30px; padding-bottom:50px;">
        <div class="px-lg-5 px-md-2 px-0">
            <div class="p-4 rounded"style="background: var(--white);">
                <div class="d-block">
                    <div class="p-md-2 p-1">
                        <div class="row">
                            <div class="col-10">
                                <a href="/domewing/{{ $shopping_cart->first()->domain_name }}">
                                    <h3 class="fw-bold text-break text-truncate" style="color: var(--dark-blue);">
                                        {{ $shopping_cart->first()->supplier_name }}</h3>
                                </a>
                            </div>
                            <div class="col-2 text-end">
                                <a class="px-lg-0 px-md-2" href="/domewing/{{ $shopping_cart->first()->domain_name }}">
                                    <img src={{ asset('media\Asset_Control_Next.svg') }} alt="Next Page"
                                        style="width:20px; height:20px;">
                                </a>
                            </div>
                        </div>
                        <div class="py-1" style="border-bottom: 1px solid var(--dark-blue)"></div>

                        <div class="product-list">
                            @foreach ($shopping_cart as $item)
                                <div class="product">
                                    <div class="row py-2 align-items-center">
                                        <div class="col-1 text-end">
                                            <div class="custom-control custom-checkbox rounded-checkbox">
                                                <input type="checkbox" class="custom-control-input selectable-item"
                                                    id="item{{ $item->id }}" data-price="{{ $item->price }}"
                                                    data-shipping="{{ $item->shippingCost }}">
                                                <label class="custom-control-label" style="padding:0%;"
                                                    for="item{{ $item->id }}"></label>
                                            </div>
                                        </div>
                                        <div class="col-lg-3 col-md-4 col-6">
                                            <img src="{{ $item->image }}" class="border border-secondary" />
                                        </div>
                                        <div class="col-lg-8 col-md-7 col-12 p-lg-1 p-2">
                                            <div class="d-flex">
                                                <div class="flex-grow-1">
                                                    <h3 class="fw-bold pb-3 text-elipsis" style="color: var(--dark-blue);">
                                                        {{ $item->productName }}
                                                    </h3>
                                                    <ul class="pricing-features pb-3 fs-18px"
                                                        style="color: var(--dark-blue);">
                                                        <li><span class="w-50 align-self-center">Delivery Fees</span><span
                                                                class="ms-auto text-end">KRW
                                                                {{ number_format($item->shippingCost, 2) }}</span></li>
                                                        <li><span class="w-50 align-self-center">Promotions
                                                                Applied</span><span class="ms-auto text-end">Wholesales
                                                                Discount</span>
                                                        </li>
                                                    </ul>

                                                    <div class="d-flex">
                                                        <div class="form-group">
                                                            <div class="form-control-wrap number-spinner-wrap">
                                                                <button
                                                                    class="btn btn-icon btn-primary number-spinner-btn number-minus">
                                                                    <em class="icon ni ni-minus"></em>
                                                                </button>
                                                                <input type="number" class="form-control number-spinner"
                                                                    id="quantity{{ $item->id }}" placeholder="1"
                                                                    value="{{ $item->quantity }}" min="1"
                                                                    style="color: var(--dark-blue);">
                                                                <button
                                                                    class="btn btn-icon btn-primary number-spinner-btn number-plus">
                                                                    <em class="icon ni ni-plus"></em>
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="d-flex pb-2 pt-2">
                                                        @php
                                                            $adjustedPrice = $item->price + 500;
                                                        @endphp
                                                        <h3 class="fw-bold" style="color: var(--pink);">
                                                            KRW {{ number_format($item->price, 2) }} <span
                                                                class="fs-18px text-decoration-line-through"
                                                                style="color: var(--pink);">
                                                                KRW {{ number_format($adjustedPrice, 2) }}</span>
                                                        </h3>

                                                    </div>
                                                </div>

                                                <a class="flex-grow-1 ms-auto text-end align-self-top px-3" href="#"
                                                    onclick="removeInit({{ $item->id }})">
                                                    <em class="icon fa-solid fa-trash fa-2x"></em>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            {{-- Grand Total --}}
            <div class="pt-5" style="border-bottom: 2px solid var(--dark-blue)"></div>

            <div class="d-flex flex-wrap align-items-center justify-content-center">
                <div class="px-md-2 p-1 py-2 me-auto flex-grow-1">
                    <div class="custom-control custom-checkbox rounded-checkbox">
                        <input type="checkbox" class="custom-control-input mt-3" id="selectAll">
                        <label class="custom-control-label" for="selectAll">
                            <h3 style="color: var(--dark-blue);">All</h3>
                        </label>
                    </div>
                </div>
                <ul class="pricing-features fs-18px px-4 flex-grow-1 text-nowrap text-lg-end text-md-start py-2"
                    style="color: var(--dark-blue);">
                    <li><span class="w-50 align-self-center">Total</span>
                        <h4 id="grandTotal" class="ms-auto fw-bold" style="color: var(--dark-blue);">KRW 0.00</h4>
                    </li>
                    <li><span class="w-50 align-self-center">Product Price</span>
                        <span id="totalProductPrice" class="ms-auto" style="color: var(--dark-blue);">KRW 0.00</span>
                    </li>
                    <li><span class="w-50 align-self-center">Shipping Cost</span>
                        <span id="totalShippingCost" class="ms-auto" style="color: var(--dark-blue);">KRW 0.00</span>
                    </li>
                    <li><span class="w-50 align-self-center">Saved</span><span id="totalSaved" class="ms-auto">KRW
                            0.00</span>
                    </li>
                </ul>
                <button class="btn btn-secondary justify-content-center py-2" type="button"
                    style="background: var(--dark-blue);">
                    <a href="/domewing/checkout">
                        <p class="text-nowrap text-white px-1 fs-22px">Check Out</p>
                    </a>
                </button>
            </div>

            {{-- Grand Total End --}}
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        function removeInit(id) {
            const cart_id = id;

            Swal.fire({
                icon: 'warning',
                title: 'Remove Item From Shopping Cart',
                text: 'Are you sure to perform this action?',
                showCancelButton: true,
                confirmButtonText: 'Yes, proceed!',
                cancelButtonText: 'No, cancel!',
            }).then((result) => {
                if (result.isConfirmed) {
                    removeCartItem(cart_id);
                }
            });
        }

        function removeCartItem(id) {
            const remember_token = '{{ Auth::guard('member')->user()->remember_token }}';
            const cart_id = id;

            $.ajax({
                url: '/api/member/remove-cart-item',
                type: 'post',
                dataType: 'json',
                data: {
                    remember_token: remember_token,
                    cart_id: cart_id,
                },
                success: function(response) {
                    const status = parseInt(response.status);

                    if (status == 1) {
                        Swal.fire({
                            icon: response.icon,
                            title: response.return,
                        }).then((result) => {
                            location.reload();
                        });
                    } else if (status == -2) {
                        Swal.fire({
                            icon: response.icon,
                            title: response.title,
                            text: response.return
                        }).then((result) => {
                            location.href = '/domewing/auth/login';
                        });
                    } else {
                        Swal.fire({
                            icon: response.icon,
                            title: response.title,
                            text: response.return
                        });
                    }
                },
                error: function(response) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Unable to process',
                        text: response,
                    });
                }
            });
        }


        function addQuantity(itemId) {
            let quantity = parseInt(document.getElementById(`quantity${itemId}`).value);
            quantity += 1;
            document.getElementById(`quantity${itemId}`).value = quantity;
        }

        function minusQuantity(itemId) {
            let quantity = parseInt(document.getElementById(`quantity${itemId}`).value);
            if (quantity > 1) {
                quantity -= 1;
                document.getElementById(`quantity${itemId}`).value = quantity;
            }
        }

        $(document).ready(function() {
            // Select All Checkbox Functionality
            $('#selectAll').change(function() {
                $('.selectable-item').prop('checked', $(this).prop('checked'));
                calculateTotal();
            });

            // Individual Checkbox Functionality
            $('.selectable-item').change(function() {
                if (!$(this).prop('checked')) {
                    $('#selectAll').prop('checked', false);
                    calculateTotal();
                } else {
                    if ($('.selectable-item:checked').length === $('.selectable-item').length) {
                        $('#selectAll').prop('checked', true);
                        calculateTotal();
                    }
                }
            });

            $('.selectable-item, .number-spinner').change(function() {
                calculateTotal();
            });

            function formatCurrency(amount) {
                const formatter = new Intl.NumberFormat('en-US', {
                    style: 'currency',
                    currency: 'KRW',
                    minimumFractionDigits: 2,
                });
                const formattedAmount = formatter.format(amount);
                return formattedAmount.replace('₩', 'KRW ');
            }

            $('.number-minus').click(function() {
                const input = $(this).siblings('.number-spinner');
                let val = parseInt(input.val());
                if (val > 1) {
                    val--;
                    input.val(val);
                    input.trigger('change'); // Trigger change event for recalculation
                }
            });

            // Event listener for plus button
            $('.number-plus').click(function() {
                const input = $(this).siblings('.number-spinner');
                let val = parseInt(input.val());
                val++;
                input.val(val);
                input.trigger('change'); // Trigger change event for recalculation
            });

            function calculateTotal() {
                let grandTotal = 0;
                let totalProductPrice = 0;
                let totalShippingCost = 0;
                let totalSaved = 0;

                $('.selectable-item:checked').each(function() {
                    const $product = $(this).closest('.product');
                    const price = parseFloat($(this).data('price'));
                    const shippingCost = parseFloat($(this).data('shipping'));
                    const quantity = parseInt($product.find('.number-spinner').val());
                    const saved = 500;

                    const productPrice = price * quantity;
                    const productSaved = saved * quantity;

                    totalProductPrice += productPrice;
                    totalShippingCost += shippingCost;
                    grandTotal += productPrice + shippingCost;
                    totalSaved += productSaved;
                });

                $('#grandTotal').text(formatCurrency(grandTotal));
                $('#totalProductPrice').text(formatCurrency(totalProductPrice));
                $('#totalShippingCost').text(formatCurrency(totalShippingCost));
                $('#totalSaved').text(formatCurrency(totalSaved));
            }
        });
    </script>
@endsection
