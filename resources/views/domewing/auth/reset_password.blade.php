@extends('domewing.layouts.main')
@section('content')
    <div class="auth-container">
        <img class="bg-img" src={{ asset('media\Asset_Bg_Whale.svg') }} alt="Background">
        <div class="container-sm wide-sm p-5">
            <h2 class="pb-5" style="color:var(--dark-blue);">Reset Password</h2>
            <form action="reset-password" method="post">
                @csrf
                <div class="row g-gs">
                    <input type="hidden" id="resetKey" name="resetKey" value="">
                    <div class="col-12 py-1">
                        <div class="form-group">
                            <label class="form-label" style="color:var(--light-blue);" for="passwordInput">New
                                Password</label>
                            <div class="form-control-wrap">
                                <div class="form-icon form-icon-right my-auto mx-2" style="height: 45px;">
                                    <a onclick="togglePasswordVisibility('password', 'show_hide_password')"
                                        id="show_hide_password">
                                        <img type="button" class="icon-size clickable"
                                            src="{{ asset('media/Asset_Control_Hide.svg') }}" alt="Toggle Password">
                                    </a>
                                </div>
                                <input type="password" class="form-control fs-18px" id="password" placeholder="password"
                                    value="{{ old('passwordInput') }}" name="passwordInput"
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
                                Password</label>
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


                    <div class="d-grid col-12 py-1">
                        <div type="submit" class="form-group mx-auto">
                            <button class="btn btn-lg btn-primary">Confirm</button>
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
        const urlParams = new URLSearchParams(window.location.search);
        const rememberToken = urlParams.get('remember_token');
        document.getElementById('resetKey').value = rememberToken;

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

        function showModal(option, text) {
            if (option == 1) {
                $('#modalSuccessTitle').text("SUCCESS");
                $('#modalSuccessMessage').text(text);
                jQuery(document).ready(function($) {

                    $('#modalSuccess').modal('show');
                    $('#modalSuccess').on('hidden.bs.modal', function() {
                        location.href = '/domewing/auth/login';
                    });

                });
            } else if (option == 2) {
                $('#modalFailTitle').text("ERROR");
                $('#modalFailMessage').text(text);
                jQuery(document).ready(function($) {
                    $('#modalFail').modal('show');
                });
            }
        }
    </script>

    @if (session('success'))
        <script>
            showModal(1, '{{ session('success') }}');
        </script>
    @endif

    @if (session('error'))
        <script>
            showModal(2, '{{ session('error') }}');
        </script>
    @endif
@endsection
