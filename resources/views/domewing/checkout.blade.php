@extends('domewing.layouts.main')
@section('content')
    <div class="px-lg-5 px-2" style="background: var(--thin-blue); padding-top: 30px; padding-bottom:50px;">
        <div class="px-lg-5 px-md-2 px-0">
            {{-- Delivery Details --}}
            <div class="card card-bordered p-5 h-100"style="background: var(--white);">
                <div class="d-block">
                    <h3 class="fw-bold" style="color: var(--dark-blue);">Delivery Details</h3>
                    <div class="row pt-4">
                        <div class="col-lg-6 col-md-12">
                            <div class="form-group">
                                <label class="form-label" style="color:var(--dark-blue);" for="contactName">Contact
                                    Name*</label>
                                <div class="form-control-wrap">
                                    <input type="text" class="form-control fs-18px"
                                        style="height: 45px; background-color: var(--thin-blue); color: var(--dark-blue)"
                                        id="contactName" placeholder="Jane Doe"
                                        value="{{ $getUserDetails->first_name }} {{ $getUserDetails->last_name }}"
                                        name="contactName">

                                    <span id="contactNameError" class="invalid" style="display: inline-block;"></span>

                                </div>
                            </div>

                            <div class="form-group">
                                <label class="form-label" style="color:var(--dark-blue);" for="lname">Contact
                                    Number*</label>
                                <div class="form-control-wrap">
                                    <div class="input-group">
                                        <input type="hidden" id="phoneCodeHidden" name="phoneCodeHidden"
                                            value="{{ $getUserDetails->phone_code }}">
                                        <button id="phoneCodeButton" class="input-group-text fs-18px" type="button"
                                            data-bs-toggle="dropdown" aria-expanded="false" name="phoneCode"
                                            style="border-right: none; height: 45px; background-color: var(--thin-blue); color: var(--dark-blue)">
                                            {{ $getUserDetails->phone_code ? $getUserDetails->phone_code : 'Select' }}</button>
                                        <ul class="dropdown-menu">
                                            <li><a class="dropdown-item" onclick="changePhoneCode('+60')">+60</a>
                                            </li>
                                            <li><a class="dropdown-item" onclick="changePhoneCode('+85')">+85</a>
                                            </li>
                                            <li><a class="dropdown-item" onclick="changePhoneCode('+82')">+82</a>
                                            </li>
                                            <li><a class="dropdown-item" onclick="changePhoneCode('+65')">+65</a>
                                            </li>
                                        </ul>

                                        <div class="vr my-2" style="color:var(--dark-blue); width:2px; opacity:1;"></div>
                                        <input type="number" value="{{ $getUserDetails->phone_number }}" name="phoneNumber"
                                            style="border-left:none; height: 45px; background-color: var(--thin-blue); color: var(--dark-blue)"
                                            class="form-control fs-18px" placeholder="0123456789">

                                    </div>

                                    <span id="phoneNumberError" class="invalid" style="display: inline-block;"></span>

                                </div>
                            </div>

                            <div class="form-group pb-4">
                                <label class="form-label" style="color:var(--dark-blue);" for="email">Email
                                    Address*</label>
                                <div class="form-control-wrap">
                                    <input type="text" class="form-control fs-18px"
                                        style="height: 45px; background-color: var(--thin-blue); color: var(--dark-blue)"
                                        id="email" placeholder="example@gmail.com" value="{{ $getUserDetails->email }}"
                                        name="email">

                                    <span id="emailError" class="invalid" style="display: inline-block;"></span>

                                </div>
                            </div>
                        </div>

                        <div class="col-lg-6 col-md-12 pb-4">
                            <div class="form-group">
                                <label class="form-label" style="color:var(--dark-blue);" for="street">Street*</label>
                                <div class="form-control-wrap">
                                    <input type="text" class="form-control fs-18px"
                                        style="height: 45px; background-color: var(--thin-blue); color: var(--dark-blue)"
                                        id="street" placeholder="1 Pusat Sumber 1 Jln Bukit Jalil Taman Teknologi 5"
                                        value="{{ old('street') }}" name="street">

                                    <span id="streetError" class="invalid" style="display: inline-block;"></span>

                                </div>
                            </div>

                            <div class="row pb-4">
                                <div class="col-6">
                                    <div class="form-group">
                                        <label class="form-label" style="color:var(--dark-blue);"
                                            for="city">City*</label>
                                        <div class="form-control-wrap">
                                            <input type="text" class="form-control fs-18px"
                                                style="height: 45px; background-color: var(--thin-blue); color: var(--dark-blue)"
                                                id="city" placeholder="Kuala Lumpur" value="{{ old('city') }}"
                                                name="city">

                                            <span id="cityError" class="invalid" style="display: inline-block;"></span>

                                        </div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="form-group">
                                        <label class="form-label" style="color:var(--dark-blue);"
                                            for="state">State*</label>
                                        <div class="form-control-wrap">
                                            <input type="text" class="form-control fs-18px"
                                                style="height: 45px; background-color: var(--thin-blue); color: var(--dark-blue)"
                                                id="state" placeholder="Kuala Lumpur" value="{{ old('state') }}"
                                                name="state">

                                            <span id="stateError" class="invalid" style="display: inline-block;"></span>

                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row pb-4">
                                <div class="col-6">
                                    <div class="form-group">
                                        <label class="form-label" style="color:var(--dark-blue);" for="zipCode">Zip
                                            Code*</label>
                                        <div class="form-control-wrap">
                                            <input type="text" class="form-control fs-18px"
                                                style="height: 45px; background-color: var(--thin-blue); color: var(--dark-blue)"
                                                id="zipCode" placeholder="57100" value="{{ old('zipCode') }}"
                                                name="zipCode">

                                            <span id="zipCodeError" class="invalid"
                                                style="display: inline-block;"></span>

                                        </div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="form-group">
                                        <label class="form-label" style="color:var(--dark-blue);"
                                            for="country">Country*</label>
                                        <div class="form-control-wrap">
                                            <input type="text" class="form-control fs-18px"
                                                style="height: 45px; background-color: var(--thin-blue); color: var(--dark-blue)"
                                                id="country" placeholder="Korea" value="{{ old('country') }}"
                                                name="country">

                                            <span id="countryError" class="invalid"
                                                style="display: inline-block;"></span>

                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            {{-- Delivery Detail End --}}

            <div class="p-4"></div>

            <div class="row">
                {{-- Checkout Item --}}
                <div class="col-xl-8 col-lg-12 pb-3">
                    <div class="card-bordered p-5 h-100" style="background: var(--white);">

                        <div>
                            <h4 class="fw-bold" style="color: var(--dark-blue);">{{ $getOrder->first()->supplier_name }}
                            </h4>
                            <div class="p-1" style="border-bottom: 2px solid var(--cyan-blue)"></div>

                            @foreach ($getOrder as $item)
                                {{-- Item --}}
                                <div class="row pt-3">
                                    <div class="col-lg-4 col-md-4">
                                        <div class="d-block">
                                            <img src="{{ $item->image }}" class="img" />
                                        </div>

                                    </div>
                                    <div class="col-lg-8 col-md-12 my-auto">
                                        <div>
                                            <h4 class="fw-bold py-3 text-truncate" style="color: var(--dark-blue);">
                                                {{ $item->productName }}
                                            </h4>
                                            <ul class="pricing-features pb-3 fs-18px" style="color: var(--dark-blue);">
                                                <li>
                                                    <h6 class="w-50 align-self-center m-0"
                                                        style="color: var(--dark-blue);">
                                                        {{ $translation['shipping_cost'] }}</h6>
                                                    <h6 class="w-50 align-self-center m-0"
                                                        style="color: var(--dark-blue);">
                                                        KRW {{ number_format($item->shippingCost, 2) }}
                                                    </h6>
                                                </li>
                                                <li>
                                                    <h6 class="w-50 align-self-center m-0"
                                                        style="color: var(--dark-blue);">
                                                        Promotion Applied</h6>
                                                    <h6 class="w-50 align-self-center m-0"
                                                        style="color: var(--dark-blue);">
                                                        Wholesale Discount
                                                    </h6>
                                                </li>
                                                <li>
                                                    <h6 class="w-50 align-self-center m-0"
                                                        style="color: var(--dark-blue);">
                                                        Units</h6>
                                                    <h6 class="w-50 align-self-center m-0"
                                                        style="color: var(--dark-blue);">
                                                        {{ $item->quantity }}
                                                    </h6>
                                                </li>
                                                <li>
                                                    <h6 class="w-50 align-self-center m-0"
                                                        style="color: var(--dark-blue);">
                                                        Product Payment</h6>
                                                    <h6 class="w-50 align-self-center m-0"
                                                        style="color: var(--dark-blue);">
                                                        KRW {{ number_format($item->price * $item->quantity, 2) }}</h6>
                                                </li>
                                                <li>
                                                    <h6 class="w-50 align-self-center m-0"
                                                        style="color: var(--dark-blue);">
                                                        Shipping Method</h6>
                                                    <h6 class="w-50 align-self-center m-0"
                                                        style="color: var(--dark-blue);">
                                                        Sea</h6>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                                {{-- Item End --}}
                            @endforeach
                        </div>
                    </div>
                </div>
                {{-- Checkout Item End --}}

                <div class="col-xl-4 col-lg-12">
                    <div class="row g-gs">

                        @php
                            $totalProductPrice = 0;
                            $totalShippingCost = 0;
                        @endphp

                        @foreach ($getOrder as $item)
                            {{-- Calculate total product price --}}
                            @php
                                $totalProductPrice += $item->price * $item->quantity;
                            @endphp

                            {{-- Calculate total shipping cost --}}
                            @php
                                $totalShippingCost += $item->shippingCost;
                            @endphp
                        @endforeach

                        {{-- Total Payment --}}
                        <div class="col-xl-12 col-lg-6 pb-3">
                            <div class="card-bordered p-5 h-100" style="background: var(--white);">
                                <h4 class="d-inline-block" style="color: var(--dark-blue);">Total Payment</h4>
                                <div class="p-1" style="border-bottom: 2px solid var(--cyan-blue)"></div>
                                <ul class="pricing-features pt-4 fs-18px " style="color: var(--dark-blue);">
                                    <li>
                                        <h6 class="w-50 align-self-center m-0" style="color: var(--dark-blue);">
                                            Product Subtotal</h6>
                                        <h6 class="w-50 align-self-center m-0" style="color: var(--dark-blue);">
                                            KRW {{ number_format($totalProductPrice, 2) }}
                                        </h6>
                                    </li>
                                    <li>
                                        <h6 class="w-50 align-self-center m-0" style="color: var(--dark-blue);">
                                            Delivery Subtotal</h6>
                                        <h6 class="w-50 align-self-center m-0" style="color: var(--dark-blue);">
                                            KRW {{ number_format($totalShippingCost, 2) }}
                                        </h6>
                                    </li>
                                    <li>
                                        <h6 class="w-50 align-self-center m-0" style="color: var(--dark-blue);">
                                            Total Payable</h6>
                                        <h4 class="w-50 fw-bold align-self-center m-0" style="color: var(--dark-blue);">
                                            KRW {{ number_format($totalProductPrice + $totalShippingCost, 2) }}
                                        </h4>
                                    </li>
                                </ul>

                            </div>
                        </div>
                        {{-- Total Payment End --}}

                        {{-- Payment Method --}}
                        <div class="col-xl-12 col-lg-6 pb-3">
                            <div class="card-bordered p-5 h-100" style="background: var(--white);">
                                <h4 class="d-inline-block" style="color: var(--dark-blue);">Payment Method</h4>
                                <div class="p-1" style="border-bottom: 2px solid var(--cyan-blue)"></div>
                                <ul class="custom-control-group d-block pt-4">
                                    <li>
                                        <div class="custom-control custom-radio">
                                            <input type="radio" class="custom-control-input" checked
                                                name="payment-method" id="payment1" value="Credit Card / Debit Card">
                                            <label class="custom-control-label" for="payment1">
                                                <h5 style="color: var(--dark-blue);">Credit Card / Debit Card</h5>
                                            </label>
                                        </div>
                                    </li>
                                    <li>
                                        <div class="custom-control custom-radio">
                                            <input type="radio" class="custom-control-input" name="payment-method"
                                                id="payment2" value="Apple Pay">
                                            <label class="custom-control-label" for="payment2">
                                                <h5 style="color: var(--dark-blue);">Apple Pay</h5>
                                            </label>
                                        </div>
                                    </li>
                                    <li>
                                        <div class="custom-control custom-radio">
                                            <input type="radio" class="custom-control-input" name="payment-method"
                                                id="payment3" value="Google Pay">
                                            <label class="custom-control-label" for="payment3">
                                                <h5 style="color: var(--dark-blue);">Google Pay</h5>
                                            </label>
                                        </div>
                                    </li>
                                    <li>
                                        <div class="custom-control custom-radio">
                                            <input type="radio" class="custom-control-input" name="payment-method"
                                                id="payment4" value="Ali Pay">
                                            <label class="custom-control-label" for="payment4">
                                                <h5 style="color: var(--dark-blue);">Ali Pay</h5>
                                            </label>
                                        </div>
                                    </li>
                                </ul>
                            </div>
                        </div>
                        {{-- Payment Method End --}}
                    </div>
                    <div class="d-flex justify-content-center align-items-center">
                        <button class="btn" style="background: var(--dark-blue);" onclick="confirmPayment()">
                            <h4 class="text-white p-2">Proceed to Payment</h4>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @include('domewing.partials.modal')
@endsection

@section('scripts')
    <script>
        function confirmPayment() {

            const orderId = '{{ $getOrder->first()->order_id }}';
            const paymentMethod = document.querySelector('input[name="payment-method"]:checked').value;
            const remember_token = '{{ Auth::guard('member')->user()->remember_token }}';
            const contactName = document.getElementById('contactName').value;
            const phoneCodeHidden = document.getElementById('phoneCodeHidden').value;
            const phoneNumber = document.getElementsByName('phoneNumber')[0].value; // Assuming only one element is present
            const email = document.getElementById('email').value;
            const street = document.getElementById('street').value;
            const city = document.getElementById('city').value;
            const state = document.getElementById('state').value;
            const zipCode = document.getElementById('zipCode').value;
            const country = document.getElementById('country').value;

            const errorIds = [
                'contactNameError',
                'phoneNumberError',
                'emailError',
                'streetError',
                'cityError',
                'stateError',
                'zipCodeError',
                'countryError'
            ];

            // Clear all validation
            errorIds.forEach((errorId) => {
                const errorElement = document.getElementById(errorId);
                if (errorElement) {
                    errorElement.textContent = ''; // Clear the error message text content
                }
            });

            const requestData = {
                orderId: orderId,
                paymentMethod: paymentMethod,
                remember_token: remember_token,
                contactName: contactName,
                phoneCodeHidden: phoneCodeHidden,
                phoneNumber: phoneNumber,
                email: email,
                street: street,
                city: city,
                state: state,
                zipCode: zipCode,
                country: country,
            };

            //to ensure loading modal doesnot interrupt
            $('#modalLoading').modal({
                backdrop: 'static',
                keyboard: false
            });
            $('#modalLoading').modal('show');

            $('#modalLoading').on('shown.bs.modal', function(e) {
                $.ajax({
                    url: '/api/member/checkout-order',
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
                                location.href = '/domewing';
                            });
                        } else {
                            $('#modalFailTitle').text(response.title);
                            $('#modalFailMessage').text(response.return);
                            $('#modalFail').modal('show');
                        }
                    },
                    error: function(response) {
                        $('#modalLoading').modal('hide');
                        if (response.status === 422) {
                            // Validation failed, handle the errors
                            const errors = response.responseJSON.errors;

                            // Display errors to the user
                            for (let fieldName in errors) {
                                if (errors.hasOwnProperty(fieldName)) {
                                    const errorMessage = errors[fieldName][0];
                                    const errorElement = document.getElementById(`${fieldName}Error`);

                                    if (errorElement) {
                                        errorElement.textContent = errorMessage;
                                    }
                                }
                            }
                        } else {
                            $('#modalFailTitle').text('ERROR');
                            $('#modalFailMessage').text(
                                'Unexpected Error Occured. Please Try Again Later.');
                            $('#modalFail').modal('show');
                        }
                    }
                });
            });
        }

        function changePhoneCode(code) {
            document.getElementById('phoneCodeHidden').value = code;
            document.getElementById('phoneCodeButton').textContent = code;
            console.log(document.getElementById('phoneCodeHidden').value);
        }
    </script>
@endsection
