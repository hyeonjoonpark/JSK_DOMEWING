@extends('domewing.layouts.main')
@section('content')
    <div class="px-lg-5 px-2" style="background: var(--thin-blue); padding-top: 30px; padding-bottom:50px;">
        <div class="px-lg-5 px-md-2 px-0">
            {{-- Delivery Details --}}
            <div class="p-4 rounded"style="background: var(--white);">
                <div class="d-block">
                    <h3 class="fw-bold" style="color: var(--dark-blue);">Delivery Details</h3>
                    <div class="row pt-5">
                        <div class="col-lg-6 col-md-12">
                            <div class="form-group">
                                <label class="form-label" style="color:var(--dark-blue);" for="contactName">Contact
                                    Name*</label>
                                <div class="form-control-wrap">
                                    <input type="text" class="form-control fs-18px"
                                        style="height: 45px; background-color: var(--thin-blue); color: var(--dark-blue)"
                                        id="contactName" placeholder="Jane Doe" value="{{ old('contact_number') }}"
                                        name="contactName">
                                    @error('contactName')
                                        <span id="contactNameError" class="invalid"
                                            style="display: inline-block;">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="form-label" style="color:var(--dark-blue);" for="lname">Contact
                                    Number*</label>
                                <div class="form-control-wrap">
                                    <div class="input-group">
                                        <input type="hidden" id="phoneCodeHidden" name="phoneCodeHidden"
                                            value="{{ old('phoneCode') }}">
                                        <button id="phoneCodeButton" class="input-group-text fs-18px" type="button"
                                            data-bs-toggle="dropdown" aria-expanded="false" name="phoneCode"
                                            style="border-right: none; height: 45px; background-color: var(--thin-blue); color: var(--dark-blue)">
                                            {{ old('phoneCodeHidden') ? old('phoneCodeHidden') : 'Select' }}</button>
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
                                        <input type="number" value="{{ old('phoneNumber') }}" name="phoneNumber"
                                            style="border-left:none; height: 45px; background-color: var(--thin-blue); color: var(--dark-blue)"
                                            class="form-control fs-18px" placeholder="0123456789">

                                    </div>
                                    @if ($errors->has('phoneCodeHidden'))
                                        <span id="phoneCodeError" class="invalid"
                                            style="display: inline-block;">{{ $errors->first('phoneCodeHidden') }}</span>
                                    @elseif ($errors->has('phoneNumber'))
                                        <span id="phoneNumberError" class="invalid"
                                            style="display: inline-block;">{{ $errors->first('phoneNumber') }}</span>
                                    @endif
                                </div>
                            </div>

                            <div class="form-group pb-4">
                                <label class="form-label" style="color:var(--dark-blue);" for="email">Email
                                    Address*</label>
                                <div class="form-control-wrap">
                                    <input type="text" class="form-control fs-18px"
                                        style="height: 45px; background-color: var(--thin-blue); color: var(--dark-blue)"
                                        id="email" placeholder="example@gmail.com" value="{{ old('email') }}"
                                        name="email">
                                    @error('email')
                                        <span id="emailError" class="invalid"
                                            style="display: inline-block;">{{ $message }}</span>
                                    @enderror
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
                                    @error('street')
                                        <span id="streetError" class="invalid"
                                            style="display: inline-block;">{{ $message }}</span>
                                    @enderror
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
                                            @error('city')
                                                <span id="cityError" class="invalid"
                                                    style="display: inline-block;">{{ $message }}</span>
                                            @enderror
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
                                            @error('state')
                                                <span id="stateError" class="invalid"
                                                    style="display: inline-block;">{{ $message }}</span>
                                            @enderror
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
                                            @error('zipCode')
                                                <span id="zipCodeError" class="invalid"
                                                    style="display: inline-block;">{{ $message }}</span>
                                            @enderror
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
                                            @error('country')
                                                <span id="countryError" class="invalid"
                                                    style="display: inline-block;">{{ $message }}</span>
                                            @enderror
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
                    <div class="rounded p-5" style="background: var(--white);">

                        <div>
                            <h4 class="fw-bold" style="color: var(--dark-blue);">Supplier 1</h4>
                            <div class="p-1" style="border-bottom: 2px solid var(--cyan-blue)"></div>

                            {{-- Example 1 --}}
                            <div class="row pt-3">
                                <div class="col-lg-4 col-md-4">
                                    <div class="d-block">
                                        <img src="https://image.invaluable.com/housePhotos/clars/88/752188/H0054-L341207066.jpg"
                                            class="img" />
                                    </div>

                                </div>
                                <div class="col-lg-8 col-md-12 my-auto">
                                    <div class="">
                                        <h4 class="fw-bold py-3 text-truncate" style="color: var(--dark-blue);">
                                            Product Name
                                        </h4>
                                        <ul class="pricing-features pb-3 fs-18px" style="color: var(--dark-blue);">
                                            <li>
                                                <h6 class="w-40 align-self-center m-0" style="color: var(--dark-blue);">
                                                    {{ $translation['shipping_cost'] }}</h6>
                                                <h6 class="w-50 align-self-center m-0" style="color: var(--dark-blue);">
                                                    3000
                                                </h6>
                                            </li>
                                            <li>
                                                <h6 class="w-40 align-self-center m-0" style="color: var(--dark-blue);">
                                                    Promotion Applied</h6>
                                                <h6 class="w-50 align-self-center m-0" style="color: var(--dark-blue);">
                                                    Wholesale Discount
                                                </h6>
                                            </li>
                                            <li>
                                                <h6 class="w-40 align-self-center m-0" style="color: var(--dark-blue);">
                                                    Units</h6>
                                                <h6 class="w-50 align-self-center m-0" style="color: var(--dark-blue);">
                                                    200
                                                </h6>
                                            </li>
                                            <li>
                                                <h6 class="w-40 align-self-center m-0" style="color: var(--dark-blue);">
                                                    Product Payment</h6>
                                                <h6 class="w-50 align-self-center m-0" style="color: var(--dark-blue);">
                                                    MYR 2340.00</h6>
                                            </li>
                                            <li>
                                                <h6 class="w-40 align-self-center m-0" style="color: var(--dark-blue);">
                                                    Shipping Method</h6>
                                                <h6 class="w-50 align-self-center m-0" style="color: var(--dark-blue);">
                                                    Sea</h6>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            {{-- Example 1 End --}}

                            {{-- Example 2 --}}
                            <div class="row pt-3">
                                <div class="col-lg-4 col-md-4">
                                    <div class="d-block">
                                        <img src="https://image.invaluable.com/housePhotos/clars/88/752188/H0054-L341207066.jpg"
                                            class="img" />
                                    </div>

                                </div>
                                <div class="col-lg-8 col-md-12 my-auto">
                                    <div class="">
                                        <h4 class="fw-bold py-3 text-truncate" style="color: var(--dark-blue);">
                                            Product Name
                                        </h4>
                                        <ul class="pricing-features pb-3 fs-18px" style="color: var(--dark-blue);">
                                            <li>
                                                <h6 class="w-40 align-self-center m-0" style="color: var(--dark-blue);">
                                                    {{ $translation['shipping_cost'] }}</h6>
                                                <h6 class="w-50 align-self-center m-0" style="color: var(--dark-blue);">
                                                    3000
                                                </h6>
                                            </li>
                                            <li>
                                                <h6 class="w-40 align-self-center m-0" style="color: var(--dark-blue);">
                                                    Promotion Applied</h6>
                                                <h6 class="w-50 align-self-center m-0" style="color: var(--dark-blue);">
                                                    Wholesale Discount
                                                </h6>
                                            </li>
                                            <li>
                                                <h6 class="w-40 align-self-center m-0" style="color: var(--dark-blue);">
                                                    Units</h6>
                                                <h6 class="w-50 align-self-center m-0" style="color: var(--dark-blue);">
                                                    200
                                                </h6>
                                            </li>
                                            <li>
                                                <h6 class="w-40 align-self-center m-0" style="color: var(--dark-blue);">
                                                    Product Payment</h6>
                                                <h6 class="w-50 align-self-center m-0" style="color: var(--dark-blue);">
                                                    MYR 2340.00</h6>
                                            </li>
                                            <li>
                                                <h6 class="w-40 align-self-center m-0" style="color: var(--dark-blue);">
                                                    Shipping Method</h6>
                                                <h6 class="w-50 align-self-center m-0" style="color: var(--dark-blue);">
                                                    Sea</h6>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            {{-- Example 2 End --}}
                        </div>



                    </div>
                </div>
                {{-- Checkout Item End --}}
            </div>
        </div>
    @endsection

    @section('scripts')
        <script>
            function changePhoneCode(code) {
                document.getElementById('phoneCodeHidden').value = code;
                document.getElementById('phoneCodeButton').textContent = code;
                console.log(document.getElementById('phoneCodeHidden').value);
            }
        </script>
    @endsection
