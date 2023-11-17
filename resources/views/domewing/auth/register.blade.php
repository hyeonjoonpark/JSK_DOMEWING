@extends('domewing.layouts.main')
@section('content')
    <div class="auth-container">
        <img class="bg-img" src={{ asset('media\Asset_Bg_Whale.svg') }} alt="Background">
        <div class="container-sm wide-sm p-5">
            <h2 class="pb-5" style="color:var(--dark-blue);">Sign Up Here</h2>
            <form action="register" method="post">
                @csrf
                <div class="row g-gs">
                    <div class="col-md-4 col-sm-12 py-1">
                        <div class="form-group">
                            <label class="form-label" style="color:var(--light-blue);" for="title">Title*</label>
                            <div class="form-control-wrap">
                                <input type="text" class="form-control fs-18px"
                                    style="height: 45px; background-color: var(--thin-blue); color: var(--dark-blue)"
                                    id="title" placeholder="Ms" value="{{ old('title') }}" name="title">
                                @error('title')
                                    <span id="titleError" class="invalid"
                                        style="display: inline-block;">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 col-sm-12 py-1">
                        <div class="form-group">
                            <label class="form-label" style="color:var(--light-blue);" for="fname">First Name*</label>
                            <div class="form-control-wrap">
                                <input type="text" class="form-control fs-18px"
                                    style="height: 45px; background-color: var(--thin-blue); color: var(--dark-blue)"
                                    id="fname" placeholder="Jane" value="{{ old('fname') }}" name="fname">
                                @error('fname')
                                    <span id="fnameError" class="invalid"
                                        style="display: inline-block;">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 col-sm-12 py-1">
                        <div class="form-group">
                            <label class="form-label" style="color:var(--light-blue);" for="lname">Last Name*</label>
                            <div class="form-control-wrap">
                                <input type="text" class="form-control fs-18px"
                                    style="height: 45px; background-color: var(--thin-blue); color: var(--dark-blue)"
                                    id="lname" placeholder="Doe" value="{{ old('lname') }}" name="lname">
                                @error('lname')
                                    <span id="lnameError" class="invalid"
                                        style="display: inline-block;">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <div class="col-12 py-1">
                        <div class="form-group">
                            <label class="form-label" style="color:var(--light-blue);" for="lname">Phone Number*</label>
                            <div class="form-control-wrap">
                                <div class="input-group">
                                    <input type="hidden" id="phoneCodeHidden" name="phoneCodeHidden">
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
                    </div>

                    <div class="col-12 py-1">
                        <div class="form-group">
                            <label class="form-label" style="color:var(--light-blue);" for="email">Email
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

                    <div class="col-12 py-1">
                        <div class="form-group">
                            <label class="form-label" style="color:var(--light-blue);" for="passwordInput">Set
                                Password*</label>
                            <div class="form-control-wrap">
                                <div class="form-icon form-icon-right my-auto mx-2" style="height: 45px;">
                                    <a onclick="togglePasswordVisibility('password', 'show_hide_password')"
                                        id="show_hide_password">
                                        <img type="button" class="icon-size clickable"
                                            src="{{ asset('media/Asset_Control_Hide.svg') }}" alt="Toggle Password">
                                    </a>
                                </div>
                                <input type="password" class="form-control fs-18px" id="password"
                                    placeholder="password" value="{{ old('passwordInput') }}" name="passwordInput"
                                    style="height: 45px; background-color: var(--thin-blue); color: var(--dark-blue); padding-right: calc(2rem + 24px);">
                                @error('passwordInput')
                                    <span id="passwordError" class="invalid"
                                        style="display: inline-block;">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="col-12 py-1">
                        <div class="form-group">
                            <label class="form-label" style="color:var(--light-blue);" for="confirmPassword">Confirm Your
                                Password*</label>
                            <div class="form-control-wrap">
                                <div class="form-icon form-icon-right my-auto mx-2" style="height: 45px;">
                                    <a onclick="togglePasswordVisibility('confirmPassword', 'show_hide_confirm_password')"
                                        id="show_hide_confirm_password">
                                        <img type="button" class="icon-size clickable"
                                            src="{{ asset('media/Asset_Control_Hide.svg') }}" alt="Toggle Password">
                                    </a>
                                </div>
                                <input type="password" class="form-control fs-18px" id="confirmPassword"
                                    placeholder="password" value="{{ old('confirmPassword') }}" name="confirmPassword"
                                    style="height: 45px; background-color: var(--thin-blue); color: var(--dark-blue); padding-right: calc(2rem + 24px);">
                                @error('confirmPassword')
                                    <span id="confirmPasswordError" class="invalid"
                                        style="display: inline-block;">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="col-12 py-1">
                        <div class="form-group">
                            <label class="form-label" style="color:var(--light-blue);" for="username">Set
                                Username*</label>
                            <div class="form-control-wrap">
                                <input type="text" class="form-control fs-18px"
                                    style="height: 45px; background-color: var(--thin-blue); color: var(--dark-blue)"
                                    id="username" placeholder="janedoetest" value="{{ old('username') }}"
                                    name="username">
                                @error('username')
                                    <span id="usernameError" class="invalid"
                                        style="display: inline-block;">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="d-grid col-12 py-1">
                        <div class="form-group mx-auto">
                            <button type="submit" class="btn btn-lg btn-primary">Sign Up</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        // Function to change the phone code value
        function changePhoneCode(code) {
            document.getElementById('phoneCodeHidden').value = code;
            document.getElementById('phoneCodeButton').textContent = code;
            console.log(document.getElementById('phoneCodeHidden').value);
        }

        function togglePasswordVisibility(inputId, iconId) {
            const passwordInput = document.getElementById(inputId);
            const showHidePasswordIcon = document.getElementById(iconId).querySelector('img');

            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                showHidePasswordIcon.src = '{{ asset('media/Asset_Control_View.svg') }}';
            } else {
                passwordInput.type = 'password';
                showHidePasswordIcon.src = '{{ asset('media/Asset_Control_Hide.svg') }}';
            }
        }
    </script>

    @if (session('success'))
        <script>
            Swal.fire({
                icon: 'success',
                title: '{{ session('success') }}',
            }).then((result) => {
                //location.reload();
            });
        </script>
    @endif

    @if (session('error'))
        <script>
            Swal.fire({
                icon: 'error',
                title: '{{ session('error') }}',
            });
        </script>
    @endif
@endsection
