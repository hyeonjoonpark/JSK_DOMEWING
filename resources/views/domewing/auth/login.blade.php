@extends('domewing.layouts.main')
@section('content')
    <div class="auth-container">
        <img class="bg-img" src={{ asset('media\Asset_Bg_Whale.svg') }} alt="Background">
        <div class="container-sm wide-sm p-5">
            <h2 class="pb-5" style="color:var(--dark-blue);">Log In Here</h2>
            <form action="login" method="post">
                @csrf
                <div class="row g-gs">
                    <div class="col-12 py-1">
                        <div class="form-group">
                            <label class="form-label" style="color:var(--light-blue);" for="email">Email Address</label>
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
                            <label class="form-label" style="color:var(--light-blue);" for="password">Password</label>
                            <div class="form-control-wrap">
                                <div class="form-icon form-icon-right my-auto mx-2" style="height: 45px;">
                                    <a onclick="togglePasswordVisibility()" id="show_hide_password">
                                        <img type="button" class="icon-size clickable"
                                            src="{{ asset('media/Asset_Control_Hide.svg') }}" alt="Toggle Password">
                                    </a>
                                </div>
                                <input type="password" class="form-control fs-18px" id="password" placeholder="password"
                                    value="{{ old('password') }}" name="password"
                                    style="height: 45px; background-color: var(--thin-blue); color: var(--dark-blue); padding-right: calc(2rem + 24px);">
                                @error('password')
                                    <span id="passwordError" class="invalid"
                                        style="display: inline-block;">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <a href="{{ route('forget.password') }}" class="d-inline-block">
                        <p class="pb-3" style="color: var(--pink)">Forget Password?</p>
                    </a>

                    <div class="d-grid col-12 py-1">
                        <div class="form-group mx-auto">
                            <button class="btn btn-lg btn-primary">Log In</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    @include('domewing.partials.modal')
@endsection

@section('scripts')
    <script>
        function togglePasswordVisibility() {
            const passwordInput = document.getElementById('password');
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
