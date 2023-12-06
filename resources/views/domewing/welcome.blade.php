@extends('domewing.layouts.main')
@section('content')
    <div style="background-color: var(--thin-blue)">
        <div class="d-flex position-relative" style="background-color: var(--dark-blue)">
            <div class="px-5" style="z-index:2;">
                <div class="pt-5 col-xl-7 col-lg-8 col-12">
                    <h3 class="text-white pt-5">Welcome to</h3>
                    <h1 class="fw-bold text-white pb-5">DOMEWING</h1>
                    <h3 class="text-white pe-3 text-wrap">Transforming Wholesale: Your Ultimate B2B
                        ntegration Hub for Seamless Supply Chains and Efficient Transactions.</h3>
                    <div class="pt-5">
                        <button type="button" class="btn btn-primary fs-18px p-3"
                            style="border-color: var(--white); background-color: var(--dark-blue);">Start Your
                            Purchase</button>
                    </div>
                </div>
                <div class="pb-4" style="padding-top:200px;">
                    <a class="d-xl-block d-none" href="#">
                        <div class="d-flex text-nowrap align-item-center">
                            <em class="icon fa-solid fa-arrow-down-long fa-2x pe-2 " style="color: var(--cyan-blue);"></em>
                            <h4 style="color: var(--cyan-blue);">
                                Learn More About Domewing
                            </h4>
                        </div>
                    </a>
                </div>
            </div>
            <img class="about-img" src={{ asset('media\Asset_About_Whale.svg') }} alt="Logo">
        </div>
        <div class="pt-5"></div>
        <div class="px-md-5 px-2 py-5">
            <h2 style="color: var(--dark-blue);">About Domewing Engine</h2>
            <div class="row m-0 g-gs">
                @foreach ($about_items as $about_item)
                    <div class="col-lg-6 col-12">
                        <div class="p-3 h-100" style="background: var(--white);">
                            <div class="row">
                                <div class="col-4 m-auto p-auto">
                                    <img class="image-fluid" src="{{ asset($about_item['image']) }}" alt="Logo">
                                </div>
                                <div class="col-8 m-auto p-auto">
                                    <h4 class="pb-3" style="color: var(--cyan-blue);">{{ $about_item['title'] }}</h4>
                                    <h4 style="color: var(--dark-blue);">{{ $about_item['description'] }}</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="pt-5"></div>
        <div class="px-md-5 px-2 py-5">
            <h2 class="pb-4" style="color: var(--dark-blue);">About JS Korea</h2>
            <div style="height:500px; background: var(--white);"></div>
        </div>

        <div class="pt-5"></div>
        <div class="px-md-5 px-2 py-5"
            style="background: linear-gradient(to bottom, var(--thin-blue) 50%, var(--dark-blue) 50%);">
            <h2 class="pb-4" style="color: var(--dark-blue);">Partnerships and Suppliers</h2>
            <div id="partnerships" class="carousel slide p-5 partnership-padding">
                <div class="carousel-inner">
                    @php
                        $chunkedPartnerships = array_chunk($partnerships, 8);
                    @endphp
                    @foreach ($chunkedPartnerships as $key => $chunk)
                        <div class="carousel-item {{ $key === 0 ? 'active' : '' }}">
                            <div class="row text-center">
                                @foreach ($chunk as $partnership)
                                    <div class="col-lg-3 col-6 d-block py-3">
                                        <img class="partnership-image" src="{{ $partnership }}" alt="Image">
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
                <div class="carousel-control-prev" style="justify-content: flex-start; opacity:1;">
                    <button type="button" data-bs-target="#partnerships" data-bs-slide="prev"
                        class="partnership-left-button">
                        <img src={{ asset('media\Asset_Control_SmallDropdown.svg') }} />
                    </button>
                </div>
                <div class="carousel-control-next" style="justify-content: flex-end; opacity:1;">
                    <button type="button" data-bs-target="#partnerships" data-bs-slide="next"
                        class="partnership-right-button">
                        <img src={{ asset('media\Asset_Control_SmallDropdown.svg') }} />
                    </button>
                </div>
            </div>
        </div>

        <div class="pt-5" style="background-color: var(--dark-blue);"></div>
        <div class="px-md-5 px-2 py-5" style="background-color: var(--dark-blue);">
            <h2 class="pb-4" style="color: var(--white);">Testimonial</h2>
            {{-- Testimonial content here --}}
            <div class="row m-0 p-0 horizontal-scrolling">
                @for ($i = 0; $i < 3; $i++)
                    <div class="col-lg-4 col-md-6 col-12 pb-3">
                        <div class="p-5" style="background-color: var(--white);">
                            <h3 class="text-bold text-xl-2 text-dark-blue" style="color: var(--dark-blue);">"</h3>
                            <h3 class="text-wrap" style="color: var(--dark-blue);">Lorem ipsum dolor sit amet, consec-tetuer
                                adipiscing elit, sed diam nonum-my nibh euismod tincidunt ut laoreet dolore magna aliquam
                                erat volutpat. Ut wisi
                                enim ad minim veniam, quis nos-trud exerci tation ullamcorper suscipi lobortis nisl ut
                                aliquip ex ea
                                commodo</h3>
                            <div style="padding-top:200px"></div>
                            <h3 class="text-end pt-3 text-truncate" style="color: var(--dark-blue);">Name/Company Name
                            </h3>
                            <h4 class="text-end" style="color: var(--cyan-blue);">8th
                                September 2023</h4>
                        </div>
                    </div>
                @endfor
            </div>
        </div>

        <div class="pt-5"></div>
        <div class="px-md-5 px-2 py-5">
            <h2 class="pb-4" style="color: var(--dark-blue);">Contact Us</h2>
            <form action="domewing/contact-us" method="post" id="contactForm">
                @csrf
                <div class="row m-0">
                    <div class="col-xl-6 col-lg-12">
                        <div class="row">
                            <div class="col-md-4 col-12 py-2">
                                <div class="form-group">
                                    <label class="form-label" style="color:var(--light-blue);"
                                        for="title">Title</label>
                                    <div class="form-control-wrap">
                                        <input type="text" class="form-control fs-18px"
                                            style="height: 45px; background-color: var(--white); color: var(--dark-blue)"
                                            id="title" placeholder="Ms" value="{{ old('title') }}" name="title">
                                        @error('title')
                                            <span id="titleError" class="invalid"
                                                style="display: inline-block;">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 col-12 py-2">
                                <div class="form-group">
                                    <label class="form-label" style="color:var(--light-blue);" for="fname">First
                                        Name</label>
                                    <div class="form-control-wrap">
                                        <input type="text" class="form-control fs-18px"
                                            style="height: 45px; background-color: var(--white); color: var(--dark-blue)"
                                            id="fname" placeholder="Jane" value="{{ old('fname') }}"
                                            name="fname">
                                        @error('fname')
                                            <span id="fnameError" class="invalid"
                                                style="display: inline-block;">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 col-12 py-2">
                                <div class="form-group">
                                    <label class="form-label" style="color:var(--light-blue);" for="lname">Last
                                        Name</label>
                                    <div class="form-control-wrap">
                                        <input type="text" class="form-control fs-18px"
                                            style="height: 45px; background-color: var(--white); color: var(--dark-blue)"
                                            id="lname" placeholder="Doe" value="{{ old('lname') }}"
                                            name="lname">
                                        @error('lname')
                                            <span id="lnameError" class="invalid"
                                                style="display: inline-block;">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 py-2">
                                <div class="form-group">
                                    <label class="form-label" style="color:var(--light-blue);" for="lname">Phone
                                        Number</label>
                                    <div class="form-control-wrap">
                                        <div class="input-group">
                                            <input type="hidden" id="phoneCodeHidden" name="phoneCodeHidden"
                                                value="{{ old('phoneCode') }}">
                                            <button id="phoneCodeButton" class="input-group-text fs-18px" type="button"
                                                data-bs-toggle="dropdown" aria-expanded="false" name="phoneCode"
                                                style="border-right: none; height: 45px; background-color: var(--white); color: var(--dark-blue)">
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

                                            <div class="vr my-2" style="color:var(--dark-blue); width:2px; opacity:1;">
                                            </div>
                                            <input type="number" value="{{ old('phoneNumber') }}" name="phoneNumber"
                                                style="border-left:none; height: 45px; background-color: var(--white); color: var(--dark-blue)"
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
                            <div class="col-12 py-2">
                                <div class="form-group">
                                    <label class="form-label" style="color:var(--light-blue);" for="email">Email
                                        Address</label>
                                    <div class="form-control-wrap">
                                        <input type="text" class="form-control fs-18px"
                                            style="height: 45px; background-color: var(--white); color: var(--dark-blue)"
                                            id="email" placeholder="example@gmail.com" value="{{ old('email') }}"
                                            name="email">
                                        @error('email')
                                            <span id="emailError" class="invalid"
                                                style="display: inline-block;">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-6 col-lg-12">
                        <div class="row">
                            <div class="col-12 py-2">
                                <div class="form-group">
                                    <label class="form-label" style="color:var(--light-blue);" for="textMessage">Your
                                        Message</label>
                                    <div class="form-control-wrap">
                                        <textarea type="text" class="form-control fs-18px"
                                            style="height: 45px; background-color: var(--white); color: var(--dark-blue)" id="textMessage"
                                            placeholder="Type your message here." value="{{ old('textMessage') }}" name="textMessage"></textarea>
                                        @error('textMessage')
                                            <span id="textMessageError" class="invalid"
                                                style="display: inline-block;">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            <div class="p-4 text-center">
                                <button type="submit"
                                    class="btn btn-primary p-3 w-50 justify-content-center fs-18px">Submit</button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
        <div class="pt-5"></div>
    </div>

    @include('domewing.partials.modal')
@endsection

@section('scripts')
    <script>
        function expandTextarea(id) {
            document.getElementById(id).addEventListener('keyup', function() {
                this.style.overflow = 'hidden';
                this.style.height = 0;
                this.style.height = this.scrollHeight + 'px';
            }, false);
        }

        expandTextarea('textMessage');

        function changePhoneCode(code) {
            document.getElementById('phoneCodeHidden').value = code;
            document.getElementById('phoneCodeButton').textContent = code;
            console.log(document.getElementById('phoneCodeHidden').value);
        }

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

    @if (session('errorsOccurred'))
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Scroll to the form
                document.getElementById('contactForm').scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });

                // Set focus on the first input element
                const firstInput = document.querySelector('#contactForm input, #contactForm textarea');
                if (firstInput) {
                    firstInput.focus();
                }
            });
        </script>
    @endif

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
