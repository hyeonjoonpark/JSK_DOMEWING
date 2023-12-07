@extends('domewing.layouts.main')
@section('content')
    <div class="auth-container">
        <img class="bg-img" src={{ asset('media\Asset_Bg_Whale.svg') }} alt="Background">
        <div class="container-sm wide-sm p-5">
            <h2 class="pb-5" style="color:var(--dark-blue);">Forgot Password?</h2>
            <form action="forget-password" method="post">
                @csrf
                <div class="row g-gs">
                    <div class="col-12 py-1">
                        <div class="form-group">
                            <label class="form-label" style="color:var(--light-blue);" for="email">Enter Your Email
                                Address</label>
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

                    <div class="d-grid col-12 py-1">
                        <div class="form-group mx-auto">
                            <button class="btn btn-lg btn-primary">Submit Request</button>
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
        function showModal(option, text) {
            if (option == 1) {
                $('#modalSuccessTitle').text("SUCCESS");
                $('#modalSuccessMessage').text(text);
                jQuery(document).ready(function($) {
                    $('#modalSuccess').modal('show');
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
