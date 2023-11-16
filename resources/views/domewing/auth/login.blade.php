@extends('domewing.layouts.main')
@section('content')
    <div class="auth-container">
        <img class="bg-img" src={{ asset('media\Asset_Bg_Whale.svg') }} alt="Background">
        <div class="container-sm wide-sm p-5">
            <h2 class="pb-5" style="color:var(--dark-blue);">Log In Here</h2>
            <form action="#" class="form-validate is-alter">
                <div class="row g-gs">
                    <div class="col-12 py-1">
                        <div class="form-group">
                            <label class="form-label" style="color:var(--light-blue)" for="user">Phone Number/Email
                                Address/User ID</label>
                            <div class="form-control-wrap">
                                <input type="text" class="form-control" id="user" placeholder="example@gmail.com"
                                    required>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 py-1">
                        <div class="form-group">
                            <label class="form-label" style="color:var(--light-blue)" for="password">Password</label>
                            <div class="input-group">
                                <input type="password" class="form-control" placeholder="password" id="passwordInput"
                                    style="border-right: none;">
                                <span class="input-group-text">
                                    <a onclick="togglePasswordVisibility()" id="show_hide_password">
                                        <img type="button" class="icon-size clickable"
                                            src="{{ asset('media/Asset_Control_Hide.svg') }}" alt="Toggle Password">
                                    </a>
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="d-grid col-12">
                        <div class="form-group mx-auto">
                            <button type="submit" class="btn btn-lg btn-primary">Log In</button>
                        </div>
                    </div>
                </div>
            </form>


            {{-- <div class="text-sm pb-2" style="color: var(--light-blue)">Phone Number/Email Address/User ID
            </div>
            <div class="form-group">
                <input type="text" class="form-control custom-auth-textbox text-md text-dark-blue text-regular"
                    placeholder="example@gmail.com">
            </div>
            <div style="padding-bottom:30px;"></div>
            <div class="text-regular text-sm pb-2" style="color: var(--light-blue)">Password</div>
            <div class="input-group">
                <input type="password" class="form-control custom-auth-textbox text-md text-dark-blue text-regular"
                    placeholder="password" aria-label="password" aria-describedby="input_password" id="passwordInput"
                    style="border-right: none;">
                <span class="input-group-text custom-auth-textbox">
                    <a onclick="togglePasswordVisibility()" id="show_hide_password">
                        <img type="button" class="icon-size clickable" src="{{ asset('media/Asset_Control_Hide.svg') }}"
                            alt="Toggle Password">
                    </a>
                </span>
            </div>
            <div style="padding-bottom:30px;"></div>
            <a href="#" class="d-inline-block">
                <div class="text-regular text-sm pb-3" style="color: var(--pink)">Forget Password?</div>
            </a>
            <div style="padding-bottom:80px;"></div>
            <div class="d-grid col-4 mx-auto">
                <button type="button" class="btn btn-primary auth-button text-regular text-md p-3">Log In</button>
            </div> --}}
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        function togglePasswordVisibility() {
            const passwordInput = document.getElementById('passwordInput');
            const showHidePasswordIcon = document.getElementById('show_hide_password').querySelector('img');

            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                showHidePasswordIcon.src = '{{ asset('media/Asset_Control_View.svg') }}';
            } else {
                passwordInput.type = 'password';
                showHidePasswordIcon.src = '{{ asset('media/Asset_Control_Hide.svg') }}';
            }
        }
    </script>
@endsection
