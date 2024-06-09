@extends('business_page.layout.main')
@section('content')
    <div style="background-color: var(--cream); padding-top:100px;">
        <div class="container-sm" style="padding:50px 0px 50px 0px;">
            <div class="row m-0 text-center text-md-start" style="row-gap: 20px;">
                <div class="col-lg-3 col-md-4 col-12 my-auto">
                    <p class="font-bold m-0 pb-gs" style="font-size:32px; color: var(--dark-blue); line-height:30px;">셀윙</p>
                    <p class="font-medium m-0 pb-3 px-5 px-md-0"
                        style="font-size:20px; color: var(--dark-blue); line-height:30px;">
                        주문부터 송장입력까지 모두 자동화<br>
                        다양한 채널에 상품등록하여 매출을 극대화하세요
                    </p>
                    <a href="{{ route('partner.login') }}" type="button" class="btn btn-sellwing py-2 px-5">
                        <p class="font-bold m-0" style="font-size:16px; color: var(--dark-blue); line-height:23px;">로그인
                        </p>
                    </a>
                </div>
                <div class="col-lg-9 col-md-8 col-12 my-auto">
                    <img src="{{ asset('images/business/01_Landing.svg') }}" class="img img-fluid">
                </div>
            </div>
        </div>
    </div>
    </div>
    <div class="nk-content py-0" style="background-color: var(--white);">
        <div class="container-sm">
            <section id="sectionAboutUs" class="py-5">
                <div class="row m-0 align-items-center d-flex">
                    <div class="col-12 col-md-6 pb-5 pb-md-0">
                        <img src="{{ asset('images/business/02_AboutUs.svg') }}" class="img img-fluid">
                    </div>
                    <div class="col-12 col-md-6 text-center px-0 px-lg-4">
                        <p class="font-bold m-0 pb-gs" style="font-size:32px; color: var(--dark-blue); line-height:30px;">
                            프로그램 소개</p>
                        <p class="font-medium px-4 px-md-0 text-start"
                            style="font-size:18px; color: var(--dark-blue); line-height:30px;">
                            <i class="fa-solid fa-truck-fast"></i> 셀윙은 주문 수집부터 품절 관리, 송장 입력에 이르기까지 모든 과정을 자동화한 프로그램입니다.
                        </p>
                        <p class="font-medium px-4 px-md-0 text-start"
                            style="font-size:18px; color: var(--dark-blue); line-height:30px;">
                            <i class="fa-solid fa-arrow-up-right-dots"></i> 오픈 마켓부터 종합몰에 이르는 다양한 유통 채널에 상품을 등록해 매출을 증대시킬 수
                            있습니다.
                        </p>
                        <p class="font-medium px-4 px-md-0 text-start"
                            style="font-size:18px; color: var(--dark-blue); line-height:30px;">
                            <i class="fa-solid fa-users"></i> 고객 지원만 관리하면서 판매에 집중함으로써 사업주님들이 매출 효율성을 극대화할 수 있도록 돕습니다.
                        </p>
                    </div>
                </div>
            </section>
            <section id="sectionFeatures" class="py-5">
                <p class="font-bold m-0 text-center d-block d-md-none pb-5"
                    style="font-size:32px; color: var(--dark-blue); line-height:30px;">프로그램 특징
                </p>
                <div class="row m-0 d-flex justify-content-between px-4 px-md-0" style="column-gap: 44px;">
                    <div class="col-md p-0 col-sm-12">
                        <div class="text-center" style="border: 2px solid var(--dark-blue);">
                            <img src="{{ asset('images/business/03_Product_Collection.svg') }}" class="img img-fluid p-4">
                            <hr class="m-0" style="opacity:1; border:1px solid var(--dark-blue);">
                            <p class="font-bold m-0 px-2 py-gs text-start"
                                style="font-size:24px; color: var(--dark-blue); line-height:28px;">상품수집
                            </p>
                            <p class="font-medium m-0 px-2 pb-2 text-start"
                                style="font-size:20px; color: var(--dark-blue); line-height:30px;">
                                - 연동되어있는 B2B 사이트에서 원하는 상품을 수집할 수 있습니다.
                            </p>
                        </div>
                        <div class="my-5 text-center" style="border: 2px solid var(--dark-blue);">
                            <img src="{{ asset('images/business/04_Private_Invoice.svg') }}" class="img img-fluid p-4">
                            <hr class="m-0" style="opacity:1; border:1px solid var(--dark-blue);">
                            <p class="font-bold m-0 px-2 py-gs text-start"
                                style="font-size:24px; color: var(--dark-blue); line-height:28px;">자동송장
                            </p>
                            <p class="font-medium m-0 px-2 pb-2 text-start"
                                style="font-size:20px; color: var(--dark-blue); line-height:30px;">
                                - 주문된 상품이 출고처리가 되면 자동으로 송장번호가 프로그램으로 통하여 마켓에 등록할 수 있습니다.
                            </p>
                        </div>
                    </div>
                    <div class="col-md p-0 col-sm-12">
                        <p class="font-bold m-0 text-center d-none d-md-block"
                            style="font-size:32px; color: var(--dark-blue); line-height:30px; padding: 80px 0px;">프로그램 특징
                        </p>
                        <div class="text-center" style="border: 2px solid var(--dark-blue);">
                            <img src="{{ asset('images/business/05_Product_Delivery.svg') }}" class="img img-fluid p-4">
                            <hr class="m-0" style="opacity:1; border:1px solid var(--dark-blue);">
                            <p class="font-bold m-0 px-2 py-gs text-start"
                                style="font-size:24px; color: var(--dark-blue); line-height:28px;">상품관리
                            </p>
                            <p class="font-medium m-0 px-2 pb-2 text-start"
                                style="font-size:20px; color: var(--dark-blue); line-height:30px;">
                                - 품절,가격변동,재입고처리등 모든 기능이 자동화가 되어있습니다.
                            </p>
                        </div>
                        <div class="my-5 mb-md-0 text-center" style="border: 2px solid var(--dark-blue);">
                            <img src="{{ asset('images/business/06_Product_Processing.svg') }}" class="img img-fluid p-4">
                            <hr class="m-0" style="opacity:1; border:1px solid var(--dark-blue);">
                            <p class="font-bold m-0 px-2 py-gs text-start"
                                style="font-size:24px; color: var(--dark-blue); line-height:28px;">상품가공
                            </p>
                            <p class="font-medium m-0 px-2 pb-2 text-start"
                                style="font-size:20px; color: var(--dark-blue); line-height:30px;">
                                - 원하는 방식대로 상품을 직접 가공하여 연동되어있는 마켓에 등록할 수 있도록 되어있습니다.
                            </p>
                        </div>
                    </div>
                    <div class="col-md p-0 col-sm-12">
                        <div class="text-center" style="border: 2px solid var(--dark-blue);">
                            <img src="{{ asset('images/business/07_Product_Registration.svg') }}" class="img img-fluid p-4">
                            <hr class="m-0" style="opacity:1; border:1px solid var(--dark-blue);">
                            <p class="font-bold m-0 px-2 py-gs text-start"
                                style="font-size:24px; color: var(--dark-blue); line-height:28px;">상품등록
                            </p>
                            <p class="font-medium m-0 px-2 pb-2 text-start"
                                style="font-size:20px; color: var(--dark-blue); line-height:30px;">
                                - 연동되어있는 마켓들에 손쉽게 상품을 등록할 수 있는 기능으로 구현되어있습니다.
                            </p>
                        </div>
                        <div class="mt-5 text-center" style="border: 2px solid var(--dark-blue);">
                            <img src="{{ asset('images/business/08_Automatic_Order.svg') }}" class="img img-fluid p-4">
                            <hr class="m-0" style="opacity:1; border:1px solid var(--dark-blue);">
                            <p class="font-bold m-0 px-2 py-gs text-start"
                                style="font-size:24px; color: var(--dark-blue); line-height:28px;">자동주문
                            </p>
                            <p class="font-medium m-0 px-2 pb-2 text-start"
                                style="font-size:20px; color: var(--dark-blue); line-height:30px;">
                                - 주문이 들어오면 매시간마다 자동으로 주문수집을하여 B2B사이트에 주문서가 등록이되며, 충전되어있는 예치금이 자동차감되어집니다.
                            </p>
                        </div>
                    </div>
                </div>
            </section>
            <section id="sectionPartnerships" class="py-5">
                <p class="font-bold m-0 text-center pb-5"
                    style="font-size:32px; color: var(--dark-blue); line-height:30px;">
                    연동 오픈마켓
                </p>

                <div class="row pb-2 pb-lg-4 px-2 justify-content-center align-items-center"
                    style="column-gap: clamp(var(--min-column-gap-vw), 1vw, var(--max-column-gap-vw)); row-gap:30px;">
                    @foreach ($partners as $index => $partner)
                        <div class="col-3 col-md-3 col-lg-2 text-center p-0">
                            <img src="{{ asset('images/business/partnership/' . $partner->image) }}" class="img img-fluid"
                                style="object-fit: contain; min-height:10vh; min-width:10vw; max-height:150px;">
                        </div>
                    @endforeach
                </div>
            </section>
        </div>
    </div>
    <div class="nk-content pt-0" style="background-color: var(--white);">
        <div class="container-sm">
            <section class="py-5">
                <p class="font-bold m-0 text-center pb-5"
                    style="font-size:32px; color: var(--dark-blue); line-height:30px;">
                    프로그램 이용료
                </p>
                <div class="row">
                    <div class="col">
                        <div class="table-responsive">
                            <table class="styled-table text-nowrap">
                                <thead>
                                    <tr>
                                        <th>종류</th>
                                        <th>무료</th>
                                        <th>플러스</th>
                                        <th>프리미엄</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>오픈마켓</td>
                                        <td>오픈마켓 10곳 연동</td>
                                        <td>오픈마켓 10곳 연동</td>
                                        <td>오픈마켓 10곳 연동</td>
                                    </tr>
                                    <tr>
                                        <td>종합몰</td>
                                        <td>-</td>
                                        <td>종합몰 10곳 연동</td>
                                        <td>종합몰 30곳 연동</td>
                                    </tr>
                                    <tr>
                                        <td>자사몰</td>
                                        <td>희망시 월 99,000원</td>
                                        <td>자사몰 1개 연동</td>
                                        <td>자사몰 1개 연동</td>
                                    </tr>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td>가격</td>
                                        <td>무료</td>
                                        <td><a href="#sectionContactUs" class="btn btn-primary">문의하기</a></td>
                                        <td>
                                            <a href="#sectionContactUs" class="btn btn-primary">문의하기</a>
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>
    <div class="nk-content pt-0" style="background-image: linear-gradient(var(--white), var(--cream));">
        <div class="container-sm">
            <section id="sectionTestimonials" class="py-5">
                <p class="font-bold m-0 text-center pb-5"
                    style="font-size:32px; color: var(--dark-blue); line-height:30px;">
                    제품후기
                </p>
                <div class="row flex-nowrap overflow-auto pb-4 testimonial-scroll" style="row-gap: 30px;">


                    @foreach ($testimonials as $record)
                        <div class="col-11 col-md-6 col-lg-5 col-xl-3 testimonial-row">
                            <div class="testimonial-card d-flex flex-column justify-content-between">
                                <?php
                                echo $record->message;
                                ?>

                                <p class="font-medium m-0 pb-3"
                                    style="color: var(--dark-blue); font-size:20px; line-height: 23px;">
                                    {{ $record->message_by }}</p>
                                <p class="font-medium m-0"
                                    style="color: var(--light-blue); font-size:16px; line-height: 19px;">
                                    {{ $record->formatted_date }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>
        </div>
    </div>
    <div class="nk-content pt-0" id="contactUsBg">
        <div class="container-sm">
            <section id="sectionContactUs" class="py-5">
                <div class="row m-0">
                    <div class="col col-lg-6">
                        <p class="font-bold m-0 text-center pb-5"
                            style="font-size:32px; color: var(--dark-blue); line-height:30px;">
                            문의하기
                        </p>

                        <form onsubmit="submitContactUs(event)">
                            <div class="form-group mb-gs">
                                <label class="form-label font-medium m-0 mb-1" for="name"
                                    style="font-size: 15px; line-height:17px; color: var(--blue);">이름<span
                                        style="color: var(--red);">*</span></label>
                                <div class="form-control-wrap" style="height:37px;">
                                    <input type="text" class="form-control font-medium contact-us-textbox"
                                        id="name" name="name" placeholder="홍길동" value="{{ old('name') }}">
                                    <span id="nameError" class="invalid font-medium"
                                        style="display: none; color: var(--red);"></span>
                                </div>
                            </div>

                            <div class="form-group mb-gs">
                                <label class="form-label font-medium m-0 mb-1" for="email"
                                    style="font-size: 15px; line-height:17px; color: var(--blue);">이메일<span
                                        style="color: var(--red);">*</span></label>
                                <div class="form-control-wrap" style="height:37px;">
                                    <input type="text" class="form-control font-medium contact-us-textbox"
                                        id="email" name="email" placeholder="janedoe@gmail.com"
                                        value="{{ old('email') }}">
                                    <span id="emailError" class="invalid font-medium"
                                        style="display: none; color: var(--red);"></span>
                                </div>
                            </div>

                            <div class="form-group mb-gs">
                                <label class="form-label font-medium m-0 mb-1" for="contact"
                                    style="font-size: 15px; line-height:17px; color: var(--blue);">연락처<span
                                        style="color: var(--red);">*</span></label>
                                <input type="hidden" id="countryId" name="countryId" value="">
                                <div class="form-control-wrap">
                                    <div class="form-control font-medium contact-us-textbox p-0">
                                        <div class="input-group-prepend">
                                            <input type="number" class="form-control form-control-lg py-2"
                                                style="border:none; font-size: 20px; line-height: 23px; color: var(--dark-blue);"
                                                id="phoneNumber" placeholder="01012345678"
                                                value="{{ old('phoneNumber') }}" name="phoneNumber">
                                        </div>

                                    </div>
                                    <span id="phoneCodeError" class="invalid ff-italic font-medium"
                                        style="color: var(--red); display:none"></span>
                                    <span id="phoneNumberError" class="invalid ff-italic font-medium"
                                        style="color: var(--red); display:none"></span>
                                </div>
                            </div>

                            <div class="form-group mb-gs">
                                <label class="form-label font-medium m-0 mb-1" for="message"
                                    style="font-size: 15px; line-height:17px; color: var(--blue);">내용<span
                                        style="color: var(--red);">*</span></label>
                                <div class="form-control-wrap">
                                    <textarea type="text" class="form-control font-medium contact-us-textbox" id="message" name="message"
                                        placeholder="내용을 기입해주세요." value="{{ old('message') }}"></textarea>
                                    <span id="messageError" class="invalid font-medium"
                                        style="display: none; color: var(--red);"></span>
                                </div>
                            </div>

                            <div class="text-left text-lg-end pb-5 pb-xl-0">
                                <button id="btnSubmit" type="submit" class="btn btn-sellwing py-2 px-5">
                                    <span class="spinner-border spinner-border-sm"
                                        style="display:none; font-size:16px; color: var(--dark-blue);" id="spinnerSubmit"
                                        role="status" aria-hidden="true"></span>
                                    <p class="font-bold m-0"
                                        style="font-size:16px; color: var(--dark-blue); line-height:23px;">
                                        보내기</p>
                                </button>
                            </div>
                        </form>

                        <div class="py-0 py-md-5 py-lg-0"></div>
                        <div class="py-5 py-lg-0"></div>

                    </div>
                </div>

            </section>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        function submitContactUs(event) {
            event.preventDefault();
            $('#spinnerSubmit').show();
            $('#btnSubmit').prop('disabled', true);

            var name = $('#name').val().trim();
            var email = $('#email').val().trim();
            var phoneNumber = $('#phoneNumber').val().trim();
            var message = $('#message').val().trim();

            const errorIds = [
                'nameError',
                'emailError',
                'phoneNumberError',
                'messageError'
            ];

            // Clear all validation
            errorIds.forEach((errorId) => {
                const errorElement = document.getElementById(errorId);
                if (errorElement) {
                    errorElement.textContent = '';
                    errorElement.style.display = 'none';
                }
            });

            $.ajax({
                url: '/api/submit-contact-us',
                method: 'POST',
                data: {
                    name: name,
                    email: email,
                    phoneNumber: phoneNumber,
                    message: message
                },
                success: function(response) {
                    const status = parseInt(response.status);
                    if (status == 1) {
                        toastr.clear();
                        NioApp.Toast(
                            '<h5 class="font-medium m-0">귀하의 문의가 성공적으로 전송되었습니다</h5>',
                            'success');

                        $('#name').val('');
                        $('#email').val('');
                        $('#phoneNumber').val('');
                        $('#message').val('');
                    } else {
                        toastr.clear();
                        NioApp.Toast(
                            '<h5 class="font-medium m-0">문의사항을 보내지 못했습니다. 나중에 다시 시도 해주십시오.</h5>',
                            'error');
                    }
                },
                error: function(response) {
                    if (response.status === 422) {
                        // Validation failed, handle the errors
                        const errors = response.responseJSON.errors;

                        // Display errors to the user
                        for (let fieldName in errors) {
                            if (errors.hasOwnProperty(fieldName)) {
                                const errorMessage = errors[fieldName][0];
                                const errorElement = document.getElementById(
                                    `${fieldName}Error`);

                                if (errorElement) {
                                    errorElement.textContent = errorMessage;
                                    errorElement.style.display = 'block';
                                }
                            }
                        }
                    } else {
                        toastr.clear();
                        NioApp.Toast(
                            '<h5 class="font-medium m-0">이런, 문제가 발생했습니다. 나중에 다시 시도 해주십시오.</h5>',
                            'error');
                        setTimeout(function() {
                            location.reload();
                        }, 3000);
                    }
                },
                complete: function() {
                    $('#spinnerSubmit').hide();
                    $('#btnSubmit').prop('disabled', false);
                }
            });
        }
    </script>
@endsection
