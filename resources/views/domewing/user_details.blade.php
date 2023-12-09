@extends('domewing.layouts.main')
@section('content')
    @include('domewing.partials.user_profile_short')

    <div class="p-lg-5 p-2" style="background-color: var(--pure-white);">
        <div class="row m-0">
            <div class="col-md-4 col-12">
                @include('domewing.partials.user_navbar')
            </div>
            <div class="col-md-8 col-12">
                <div class="row g-gs px-2">
                    <div class="col-md-4 col-sm-12">
                        <div class="form-group">
                            <label class="form-label" style="color:var(--dark-blue);" for="title">Title</label>
                            <div class="form-control-wrap">
                                <input type="text" class="form-control fs-18px"
                                    style="height: 45px; background-color: var(--thin-blue); color: var(--dark-blue)"
                                    id="title" placeholder="Ms" value="{{ old('title', $userInfo->title) }}"
                                    name="title">
                                <span id="titleError" class="invalid" style="display: inline-block;"></span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 col-sm-12">
                        <div class="form-group">
                            <label class="form-label" style="color:var(--dark-blue);" for="fname">First
                                Name</label>
                            <div class="form-control-wrap">
                                <input type="text" class="form-control fs-18px"
                                    style="height: 45px; background-color: var(--thin-blue); color: var(--dark-blue)"
                                    id="fname" placeholder="Jane" value="{{ old('fname', $userInfo->first_name) }}"
                                    name="fname">
                                <span id="fnameError" class="invalid" style="display: inline-block;"></span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 col-sm-12">
                        <div class="form-group">
                            <label class="form-label" style="color:var(--dark-blue);" for="lname">Last Name</label>
                            <div class="form-control-wrap">
                                <input type="text" class="form-control fs-18px"
                                    style="height: 45px; background-color: var(--thin-blue); color: var(--dark-blue)"
                                    id="lname" placeholder="Doe" value="{{ old('lname', $userInfo->last_name) }}"
                                    name="lname">
                                <span id="lnameError" class="invalid" style="display: inline-block;"></span>
                            </div>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="form-group">
                            <label class="form-label" style="color:var(--dark-blue);" for="lname">Phone
                                Number</label>
                            <div class="form-control-wrap">
                                <div class="input-group">
                                    <input type="hidden" id="phoneCodeHidden" name="phoneCodeHidden"
                                        value="{{ old('phoneCodeHidden', $userInfo->phone_code) }}">
                                    <button id="phoneCodeButton" class="input-group-text fs-18px" type="button"
                                        data-bs-toggle="dropdown" aria-expanded="false" name="phoneCode"
                                        style="border-right: none; height: 45px; background-color: var(--thin-blue); color: var(--dark-blue)">
                                        {{ $userInfo->phone_code ? $userInfo->phone_code : 'Select' }}</button>
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
                                    <input type="number" value="{{ old('phoneNumber', $userInfo->phone_number) }}"
                                        name="phoneNumber" id="phoneNumber"
                                        style="border-left:none; height: 45px; background-color: var(--thin-blue); color: var(--dark-blue)"
                                        class="form-control fs-18px" placeholder="0123456789">
                                </div>
                                <span id="phoneNumberError" class="invalid" style="display: inline-block;"></span>
                            </div>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="form-group">
                            <label class="form-label" style="color:var(--dark-blue);" for="email">Email
                                Address</label>
                            <div class="form-control-wrap">
                                <input type="text" class="form-control fs-18px"
                                    style="height: 45px; background-color: var(--thin-blue); color: var(--dark-blue)"
                                    id="email" placeholder="example@gmail.com"
                                    value="{{ old('email', $userInfo->email) }}" name="email">
                                <span id="emailError" class="invalid" style="display: inline-block;"></span>
                            </div>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="form-group">
                            <label class="form-label" style="color:var(--dark-blue);" for="username">Username</label>
                            <div class="form-control-wrap">
                                <input type="text" class="form-control fs-18px"
                                    style="height: 45px; background-color: var(--thin-blue); color: var(--dark-blue)"
                                    id="username" placeholder="janedoetest"
                                    value="{{ old('username', $userInfo->username) }}" name="username">
                                <span id="usernameError" class="invalid" style="display: inline-block;"></span>
                            </div>
                        </div>
                    </div>
                    <div class="d-grid col-12">
                        <div class="form-group mx-auto">
                            <button onclick="updateProfile()" class="btn btn-lg btn-primary">Save Changes</button>
                        </div>
                    </div>
                </div>

                {{-- <div class="pt-5">
                    <h4 class="px-2 mb-4" style="color: var(--dark-blue);">Default Shipping Address</h4>
                    <div class="row g-gs px-2">
                        <div class="col-12 py-1">
                            <div class="form-group">
                                <label class="form-label" style="color:var(--dark-blue);" for="addressName">Set
                                    Address Name</label>
                                <div class="form-control-wrap">
                                    <input type="text" class="form-control fs-18px"
                                        style="height: 45px; background-color: var(--thin-blue); color: var(--dark-blue)"
                                        id="addressName" placeholder="current warehouse" value="{{ old('addressName') }}"
                                        name="addressName">
                                    @error('addressName')
                                        <span id="addressNameError" class="invalid"
                                            style="display: inline-block;">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>


                        <div class="col-12 py-1">
                            <div class="form-group">
                                <label class="form-label" style="color:var(--dark-blue);" for="street">Street</label>
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
                        </div>

                        <div class="col-md-6 col-sm-12 py-1">
                            <div class="form-group">
                                <label class="form-label" style="color:var(--dark-blue);" for="city">City</label>
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

                        <div class="col-md-6 col-sm-12 py-1">
                            <div class="form-group">
                                <label class="form-label" style="color:var(--dark-blue);" for="state">State</label>
                                <div class="form-control-wrap">
                                    <input type="text" class="form-control fs-18px"
                                        style="height: 45px; background-color: var(--thin-blue); color: var(--dark-blue)"
                                        id="state" placeholder="Wilayah Persekutuan" value="{{ old('state') }}"
                                        name="state">
                                    @error('state')
                                        <span id="stateError" class="invalid"
                                            style="display: inline-block;">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6 col-sm-12 py-1">
                            <div class="form-group">
                                <label class="form-label" style="color:var(--dark-blue);" for="zipcode">Zip
                                    Code</label>
                                <div class="form-control-wrap">
                                    <input type="text" class="form-control fs-18px"
                                        style="height: 45px; background-color: var(--thin-blue); color: var(--dark-blue)"
                                        id="zipcode" placeholder="51000" value="{{ old('zipcode') }}" name="zipcode">
                                    @error('zipcode')
                                        <span id="zipcodeError" class="invalid"
                                            style="display: inline-block;">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6 col-sm-12 py-1">
                            <div class="form-group">
                                <label class="form-label" style="color:var(--dark-blue);" for="country">Country</label>
                                <div class="form-control-wrap">
                                    <input type="text" class="form-control fs-18px"
                                        style="height: 45px; background-color: var(--thin-blue); color: var(--dark-blue)"
                                        id="country" placeholder="Malaysia" value="{{ old('country') }}"
                                        name="country">
                                    @error('country')
                                        <span id="countryError" class="invalid"
                                            style="display: inline-block;">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="d-grid col-12 py-1">
                            <div class="form-group mx-auto">
                                <button type="submit" class="btn btn-lg btn-primary">Save Changes</button>
                            </div>
                        </div>
                    </div>


                    <button type="button" class="btn d-inline-flex align-items-center">
                        <img src={{ asset('media\Asset_Control_Add.svg') }} class="icon-size">
                        <p class="text-regular text-dark-blue text-xl px-3">Add Another Address</p>
                    </button>
                </div> --}}
            </div>
        </div>
    </div>

    @include('domewing.partials.modal')
@endsection

@section('scripts')
    <script>
        function changePhoneCode(code) {
            document.getElementById('phoneCodeHidden').value = code;
            document.getElementById('phoneCodeButton').textContent = code;
            console.log(document.getElementById('phoneCodeHidden').value);
        }

        function updateProfile() {
            const title = document.getElementById('title').value;
            const fname = document.getElementById('fname').value;
            const lname = document.getElementById('lname').value;
            const phoneCodeHidden = document.getElementById('phoneCodeHidden').value;
            const phoneNumber = document.getElementsByName('phoneNumber')[0].value;;
            const email = document.getElementById('email').value;
            const username = document.getElementById('username').value;

            const remember_token = '{{ Auth::guard('member')->user()->remember_token }}';

            const errorIds = [
                'titleError',
                'fnameError',
                'lnameError',
                'phoneNumberError',
                'emailError',
                'usernameError',
            ];

            // Clear all validation
            errorIds.forEach((errorId) => {
                const errorElement = document.getElementById(errorId);
                if (errorElement) {
                    errorElement.textContent = ''; // Clear the error message text content
                }
            });

            const requestData = {
                title: title,
                fname: fname,
                lname: lname,
                phoneCodeHidden: phoneCodeHidden,
                phoneNumber: phoneNumber,
                email: email,
                username: username,
                remember_token: remember_token,
            };

            $('#modalLoading').modal({
                backdrop: 'static',
                keyboard: false
            });
            $('#modalLoading').modal('show');

            $('#modalLoading').on('shown.bs.modal', function(e) {
                $.ajax({
                    type: 'POST',
                    url: '/api/member/update-profile',
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
                            $('#modalFail').on('hidden.bs.modal', function(e) {
                                location.reload();
                            });
                        }
                    }
                });
            });
        }
    </script>
@endsection
