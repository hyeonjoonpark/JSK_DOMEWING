@extends('domewing.layouts.main')
@section('content')
    <style>
        .rounded-checkbox .custom-control-input:checked~.custom-control-label::before {
            border-radius: 50% !important;
        }

        .rounded-checkbox .custom-control-label::before {
            border-radius: 50% !important;
        }
    </style>

    <div class="px-lg-5 px-2" style="background: var(--thin-blue); padding-top: 30px; padding-bottom:50px;">
        <div class="px-lg-5 px-md-2 px-0">
            <div class="p-4 rounded"style="background: var(--white);">
                <div class="d-block">
                    <div class="p-md-2 p-1">
                        <div class="row">
                            <div class="col-10">
                                <h3 class="fw-bold text-break text-truncate" style="color: var(--dark-blue);">Supplier 1</h3>
                            </div>
                            <div class="col-2 text-end">
                                <a class="px-lg-0 px-md-2" href="#">
                                    <img src={{ asset('media\Asset_Control_Next.svg') }} alt="Next Page"
                                        style="width:20px; height:20px;">
                                </a>
                            </div>
                        </div>
                        <div class="py-1" style="border-bottom: 1px solid var(--dark-blue)"></div>

                        {{-- Example 1 --}}
                        <div class="row py-2 align-items-center">
                            <div class="col-1 text-end">
                                <div class="custom-control custom-checkbox rounded-checkbox">
                                    <input type="checkbox" class="custom-control-input" id="customCheck1">
                                    <label class="custom-control-label" style="padding:0%;" for="customCheck1"></label>
                                </div>
                            </div>
                            <div class="col-lg-3 col-md-4 col-6">
                                <img src="https://cdn0.rubylane.com/_podl/item/1518436/TVC3364A/Goodyear-Salesman-Sample-Miniature-Airfoam-Couch-pic-1A-2048%3A10.10-93eee4a0-f.webp"
                                    class="border border-secondary" />
                            </div>
                            <div class="col-lg-8 col-md-7 col-12 p-lg-1 p-2">
                                <div class="d-flex">
                                    <div class="flex-grow-1">
                                        <h3 class="fw-bold pb-3 text-truncate" style="color: var(--dark-blue);">
                                            Product Name
                                        </h3>
                                        <ul class="pricing-features pb-3 fs-18px" style="color: var(--dark-blue);">
                                            <li><span class="w-50 align-self-center">Delivery Fees</span><span
                                                    class=" ms-auto text-end">MYR
                                                    39.00</span></li>
                                            <li><span class="w-50 align-self-center">Promotions
                                                    Applied</span><span class=" ms-auto text-end">Wholesales
                                                    Discount</span>
                                            </li>
                                        </ul>
                                        <div class="d-flex">
                                            <div class="form-group">
                                                <div class="form-control-wrap number-spinner-wrap">
                                                    <button class="btn btn-icon btn-primary number-spinner-btn number-minus"
                                                        data-number="minus"><em class="icon ni ni-minus"></em></button>
                                                    <input type="number" class="form-control number-spinner"
                                                        placeholder="number" value="20" step="10">
                                                    <button class="btn btn-icon btn-primary number-spinner-btn number-plus"
                                                        data-number="plus"><em class="icon ni ni-plus"></em></button>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="d-flex pb-2 pt-2">
                                            <h3 class ="fw-bold" style="color: var(--pink);">RM 22.00 <span
                                                    class ="fs-18px text-decoration-line-through"
                                                    style="color: var(--pink);">RM
                                                    11.00</span></h3>
                                        </div>
                                    </div>

                                    <a class="flex-grow-1 ms-auto text-end align-self-center px-3" href="#">
                                        <em class="icon fa-solid fa-trash fa-2x"></em>
                                    </a>

                                </div>
                            </div>
                        </div>
                        {{-- Example 1 End --}}

                        {{-- Example 2 --}}
                        <div class="row py-2 align-items-center">
                            <div class="col-1 text-end">
                                <div class="custom-control custom-checkbox rounded-checkbox">
                                    <input type="checkbox" class="custom-control-input" id="customCheck2">
                                    <label class="custom-control-label" style="padding:0%;" for="customCheck2"></label>
                                </div>
                            </div>
                            <div class="col-lg-3 col-md-4 col-6">
                                <img src="https://images.squarespace-cdn.com/content/v1/5ca3410da09a7e6e7ed285ea/1653477235891-25RV6FR3GT1EZILWOBX4/ArrowCoffeeTable2.jpg?format=750w"
                                    class="border border-secondary" />
                            </div>
                            <div class="col-lg-8 col-md-7 col-12 p-lg-1 p-2">
                                <div class="d-flex">
                                    <div class="flex-grow-1">
                                        <h3 class="fw-bold pb-3 text-truncate" style="color: var(--dark-blue);">
                                            Product Name
                                        </h3>
                                        <ul class="pricing-features pb-3 fs-18px" style="color: var(--dark-blue);">
                                            <li><span class="w-50 align-self-center">Delivery Fees</span><span
                                                    class=" ms-auto text-end">MYR
                                                    39.00</span></li>
                                            <li><span class="w-50 align-self-center">Promotions
                                                    Applied</span><span class=" ms-auto text-end">Wholesales
                                                    Discount</span>
                                            </li>
                                        </ul>
                                        <div class="d-flex">
                                            <div class="form-group">
                                                <div class="form-control-wrap number-spinner-wrap">
                                                    <button class="btn btn-icon btn-primary number-spinner-btn number-minus"
                                                        data-number="minus"><em class="icon ni ni-minus"></em></button>
                                                    <input type="number" class="form-control number-spinner"
                                                        placeholder="number" value="20" step="10">
                                                    <button class="btn btn-icon btn-primary number-spinner-btn number-plus"
                                                        data-number="plus"><em class="icon ni ni-plus"></em></button>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="d-flex pb-2 pt-2">
                                            <h3 class ="fw-bold" style="color: var(--pink);">RM 22.00 <span
                                                    class ="fs-18px text-decoration-line-through"
                                                    style="color: var(--pink);">RM
                                                    11.00</span></h3>
                                        </div>
                                    </div>

                                    <a class="flex-grow-1 ms-auto text-end align-self-center px-3" href="#">
                                        <em class="icon fa-solid fa-trash fa-2x"></em>
                                    </a>

                                </div>
                            </div>
                        </div>
                        {{-- Example 2 End --}}
                    </div>
                </div>
            </div>

            {{-- Grand Total --}}
            <div class="pt-5" style="border-bottom: 2px solid var(--dark-blue)"></div>

            <div class="d-flex flex-wrap align-items-center justify-content-center">
                <div class="px-md-2 p-1 py-2 me-auto flex-grow-1">
                    <div class="custom-control custom-checkbox rounded-checkbox ">
                        <input type="checkbox" class="custom-control-input mt-3" id="all">
                        <label class="custom-control-label" for="all">
                            <h3 style="color: var(--dark-blue);">All</h3>
                        </label>
                    </div>
                </div>
                <ul class="pricing-features fs-18px px-4 flex-grow-1 text-nowrap text-lg-end text-md-start py-2"
                    style="color: var(--dark-blue);">
                    <li><span class="w-50 align-self-center">Total</span>
                        <h3 class="ms-auto fw-bold">MYR
                            4000.00</h3>
                    </li>
                    <li><span class="w-50 align-self-center">Saved</span><span class="ms-auto">MYR 120.00</span>
                    </li>
                </ul>
                <button class="btn btn-secondary justify-content-center py-2" type="button"
                    style="background: var(--dark-blue);">
                    <a href="#">
                        <p class="text-nowrap text-white px-2">Check Out</p>
                    </a>
                </button>
            </div>


            {{-- <div class="hstack align-items-center" style="padding-top:30px;">
                <div class="form-check d-inline-block" style="padding-left:0px;">
                    <input class="form-check-input text-xl" style="margin:0px; border-radius:50%;" type="checkbox"
                        value="" id="all">
                </div>
                <div class="text-xl text-regular text-dark-blue" for="all">All</div>
                <div class="ms-auto">
                    <table class="table table-sm table-borderless align-middle" style="--bs-table-bg: var(--thin-blue);">
                        <tbody>
                            <tr>
                                <td>
                                    <p class="text-regular text-dark-blue text-md pe-2">Total</p>
                                </td>
                                <td>
                                    <p class="text-bold text-dark-blue text-xl">MYR 4758.00</p>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <p class="text-regular text-dark-blue text-md pe-2">Saved</p>
                                </td>
                                <td>
                                    <p class="text-regular text-dark-blue text-md">MYR 120.00</p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <button class="btn btn-secondary" style="background: var(--dark-blue);">
                    <a href="#">
                        <p class="text-regular text-xl text-white px-2">Check Out</p>
                    </a>
                </button>
            </div> --}}
            {{-- Grand Total End --}}
        </div>
    </div>
@endsection

@section('scripts')
@endsection
