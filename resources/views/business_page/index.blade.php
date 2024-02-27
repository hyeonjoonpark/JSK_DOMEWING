@extends('business_page.layout.main')
@section('content')
    <div style="background-color: var(--cream); padding-top:100px;">
        <div class="container-sm" style="padding:50px 0px 50px 0px;">
            <div class="row m-0 text-center text-md-start" style="row-gap: 20px;">
                <div class="col-lg-3 col-md-4 col-12 my-auto">
                    <p class="font-bold m-0 pb-gs" style="font-size:32px; color: var(--dark-blue); line-height:30px;">셀윙</p>
                    <p class="font-medium m-0 pb-3 px-5 px-md-0"
                        style="font-size:20px; color: var(--dark-blue); line-height:30px;">이익은
                        극대화하고
                        노력은 최소화하세요: 기업가의 이점
                    </p>
                    <a href="{{ route('auth.login') }}" type="button" class="btn btn-sellwing py-2 px-5">
                        <p class="font-bold m-0" style="font-size:16px; color: var(--dark-blue); line-height:23px;">로그인</p>
                    </a>
                </div>
                <div class="col-lg-9 col-md-8 col-12 my-auto">
                    <img src="{{ asset('images/business/01_Landing.svg') }}" class="img img-fluid">
                </div>
            </div>
        </div>
    </div>
    </div>
    <div class="nk-content pt-0" style="background-color: var(--white);">
        <div class="container-sm">
            <section id="sectionAboutUs" class="py-5">
                <div class="row m-0 align-items-center d-flex">
                    <div class="col-12 col-md-6 pb-5 pb-md-0">
                        <img src="{{ asset('images/business/02_AboutUs.svg') }}" class="img img-fluid">
                    </div>
                    <div class="col-12 col-md-6 text-center px-0 px-lg-4">
                        <p class="font-bold m-0 pb-gs" style="font-size:32px; color: var(--dark-blue); line-height:30px;">
                            회사소개</p>
                        <p class="font-medium m-0 px-4 px-md-0"
                            style="font-size:20px; color: var(--dark-blue); line-height:30px;">다양한 도매
                            웹사이트에서 제품 데이터를 효율적으로 수집하고 이를 전자상거래 사이트나 온라인 소매점으로 원활하게 전송하여 유연성과 확장성을 보장하는 엔진입니다.
                        </p>
                    </div>
                </div>
            </section>
            <section id="sectionFeatures" class="py-5">
                <p class="font-bold m-0 text-center d-block d-md-none pb-5"
                    style="font-size:32px; color: var(--dark-blue); line-height:30px;">특색
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
                                style="font-size:20px; color: var(--dark-blue); line-height:30px;">제품 업로드를 처리하는 보다 효율적인 방법을
                                찾고 계십니까? 우리 엔진은 URL만으로 모든 데이터를 데이터베이스로 자동 전송하므로 시간과 노력이 절약됩니다.
                            </p>
                        </div>
                        <div class="my-5 text-center" style="border: 2px solid var(--dark-blue);">
                            <img src="{{ asset('images/business/04_Private_Invoice.svg') }}" class="img img-fluid p-4">
                            <hr class="m-0" style="opacity:1; border:1px solid var(--dark-blue);">
                            <p class="font-bold m-0 px-2 py-gs text-start"
                                style="font-size:24px; color: var(--dark-blue); line-height:28px;">자동송장
                            </p>
                            <p class="font-medium m-0 px-2 pb-2 text-start"
                                style="font-size:20px; color: var(--dark-blue); line-height:30px;">모든 제품 세부정보를 수동으로 컴파일하고
                                구성하는 데 지치셨나요? “Excel Wing”으로 쉽게 문서화하세요. 한 번만 클릭하면 제품 목록이 Excel 시트로 생성됩니다.
                            </p>
                        </div>
                    </div>
                    <div class="col-md p-0 col-sm-12">
                        <p class="font-bold m-0 text-center d-none d-md-block"
                            style="font-size:32px; color: var(--dark-blue); line-height:30px; padding: 80px 0px;">특색
                        </p>
                        <div class="text-center" style="border: 2px solid var(--dark-blue);">
                            <img src="{{ asset('images/business/05_Product_Delivery.svg') }}" class="img img-fluid p-4">
                            <hr class="m-0" style="opacity:1; border:1px solid var(--dark-blue);">
                            <p class="font-bold m-0 px-2 py-gs text-start"
                                style="font-size:24px; color: var(--dark-blue); line-height:28px;">상품관리
                            </p>
                            <p class="font-medium m-0 px-2 pb-2 text-start"
                                style="font-size:20px; color: var(--dark-blue); line-height:30px;">주문윙에서는 주문처리부터 상품배송현황 확인까지
                                쉽게 접근할 수 있습니다. 이 기능을 사용하면 모든 주문에 신속하게 대응하고 배송 문제를 처리할 수 있습니다.
                            </p>
                        </div>
                        <div class="my-5 mb-md-0 text-center" style="border: 2px solid var(--dark-blue);">
                            <img src="{{ asset('images/business/06_Product_Processing.svg') }}" class="img img-fluid p-4">
                            <hr class="m-0" style="opacity:1; border:1px solid var(--dark-blue);">
                            <p class="font-bold m-0 px-2 py-gs text-start"
                                style="font-size:24px; color: var(--dark-blue); line-height:28px;">상품가공
                            </p>
                            <p class="font-medium m-0 px-2 pb-2 text-start"
                                style="font-size:20px; color: var(--dark-blue); line-height:30px;">여기에서 업로드된 제품 상태를 관리하세요.
                                품절, 마지막 몇 개 단위까지 또는 일시적으로 사용할 수 없는 경우 고객에게 제품에 대한 최신 정보를 제공하십시오.
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
                                style="font-size:20px; color: var(--dark-blue); line-height:30px;">제품의 바다에 빠져들고 있나요? 매핑윙이
                                처리해 드리겠습니다. 귀하의 제품은 귀하가 지정한 기준에 따라 자동으로 분류되므로 늘어나는 재고 관리가 간소화됩니다.
                            </p>
                        </div>
                        <div class="mt-5 text-center" style="border: 2px solid var(--dark-blue);">
                            <img src="{{ asset('images/business/08_Automatic_Order.svg') }}" class="img img-fluid p-4">
                            <hr class="m-0" style="opacity:1; border:1px solid var(--dark-blue);">
                            <p class="font-bold m-0 px-2 py-gs text-start"
                                style="font-size:24px; color: var(--dark-blue); line-height:28px;">자동주문
                            </p>
                            <p class="font-medium m-0 px-2 pb-2 text-start"
                                style="font-size:20px; color: var(--dark-blue); line-height:30px;">제품에 대한 API를 생성하는 데 도움이
                                필요하십니까? API Wing을 사용하여 쉽게 생성하세요. 귀하의 비즈니스가 처리하는 모든 전자상거래 사이트로 내보내고 업로드할 수 있습니다.
                            </p>
                        </div>
                    </div>
                </div>
            </section>
            <section id="sectionPartnerships" class="py-5">
                <p class="font-bold m-0 text-center pb-5"
                    style="font-size:32px; color: var(--dark-blue); line-height:30px;">
                    우리의 파트너십
                </p>
            </section>
            <section id="sectionTestimonials" style="height:600px;">
                <p>sectionTestimonials</p>
            </section>
            <section id="sectionContactUs" style="height:600px;">
                <p>sectionContactUs</p>
            </section>
        </div>
    </div>
@endsection

@section('scripts')
    <script></script>
@endsection
