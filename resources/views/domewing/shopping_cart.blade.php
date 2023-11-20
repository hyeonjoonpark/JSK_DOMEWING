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
                <div class="d-flex">

                    <div class="p-md-2 p-1">
                        <div class="custom-control custom-control-md custom-checkbox rounded-checkbox ">
                            <input type="checkbox" class="custom-control-input " id="customCheck2">
                            <label class="custom-control-label my-auto col-9" style="color: var(--dark-blue);"
                                for="customCheck2">
                                <p class="fw-bold text-break text-truncate ">Supplier 1 lkfnaf aflknasfa slakmf
                                    lkasdmfa flam flksmfl afml
                                </p>
                            </label>
                            <a class="px-2" href="#">
                                <img src={{ asset('media\Asset_Control_Next.svg') }} alt="Next Page"
                                    style="object-fit: fill; min-width:20px; min-height:20px;">
                            </a>
                        </div>

                    </div>

                </div>
            </div>
            {{-- <div style="background: var(--white); padding: 50px; border-radius:20px;">
                <div class="hstack gap-3 align-items-center pb-2">
                    <div class="form-check d-inline-block" style="padding-left:0px;">
                        <input class="form-check-input text-xl" style="margin:0px; border-radius:50%;" type="checkbox"
                            value="" id="supplier1">

                    </div>
                    <a href="#" class="d-inline-flex align-items-center">
                        <div class="text-xl text-bold text-dark-blue" for="supplier1">Supplier 1</div>
                        <img src="media\Asset_Control_Next.svg" alt="Next Page" class="icon-size" style="margin-left: 10px">
                    </a>
                    <a href="#" class="d-inline-flex align-items-center ms-auto">
                        <div class="text-xl text-regular text-dark-blue">Edit</div>
                    </a>
                </div>

                <div style="border-bottom: 2px solid var(--cyan-blue)"></div>

                <div style="padding-top:30px"></div>

                <div class="hstack gap-3 align-items-start pb-3">
                    <div class="form-check d-inline-block" style="padding-left:0px;">
                        <input class="form-check-input text-xl" style="margin:0px; border-radius:50%;" type="checkbox"
                            value="" id="product1">
                    </div>
                    <img src="https://image.invaluable.com/housePhotos/clars/88/752188/H0054-L341207066.jpg"
                        class="shopping-cart-img" />
                    <div>
                        <div class="text-xl text-bold text-dark-blue pb-3 text-truncate">
                            Product Name
                        </div>
                        <table class="table table-sm table-borderless align-middle" style="--bs-table-bg: var(--white);">
                            <tbody>
                                <tr>
                                    <td>
                                        <p class="text-regular text-dark-blue text-md pe-5">Deliveries Fees</p>
                                    </td>
                                    <td>
                                        <p class="text-regular text-dark-blue text-md">MYR 39.00</p>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <p class="text-regular text-dark-blue text-md pe-5">Promotions Applied</p>
                                    </td>
                                    <td>
                                        <p class="text-regular text-dark-blue text-md">Wholesales Discount</p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        <div style="padding-top:30px"></div>
                        <div class="d-flex">
                            <button class="btn"
                                style="--bs-btn-border-radius: 5px !important; background-color: var(--cyan-blue); min-width:50px;">
                                <img src="media\Asset_Control_Minus.svg" class="icon-size">
                            </button>
                            <div class="text-xl px-3 text-dark-blue w-50">
                                <input class="text-center w-100" style="border-radius:5px;" value=200 />
                            </div>
                            <button class="btn"
                                style="--bs-btn-border-radius: 5px !important; background-color: var(--cyan-blue); min-width:50px;">
                                <img src="media\Asset_Control_Add.svg" class="icon-size">
                            </button>
                        </div>
                        <div class="d-flex pb-2 pt-2">
                            <div class ="text-xl text-pink text-bold">RM 22.00 <span
                                    class ="text-md text-regular text-pink text-decoration-line-through">RM 11.00</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="hstack gap-3 align-items-start pb-3">
                    <div class="form-check d-inline-block" style="padding-left:0px;">
                        <input class="form-check-input text-xl" style="margin:0px; border-radius:50%;" type="checkbox"
                            value="" id="product2">
                    </div>
                    <img src="https://image.made-in-china.com/202f0j00ecNhLPGbfilF/32mm-S-Speed-Sample-Provided-Furniture-Adjustable-Office-Desk-with-Low-Price.webp"
                        class="shopping-cart-img" />
                    <div>
                        <div class="text-xl text-bold text-dark-blue pb-3 text-truncate">
                            Product Name
                        </div>
                        <table class="table table-sm table-borderless align-middle" style="--bs-table-bg: var(--white);">
                            <tbody>
                                <tr>
                                    <td>
                                        <p class="text-regular text-dark-blue text-md pe-5">Deliveries Fees</p>
                                    </td>
                                    <td>
                                        <p class="text-regular text-dark-blue text-md">MYR 39.00</p>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <p class="text-regular text-dark-blue text-md pe-5">Promotions Applied</p>
                                    </td>
                                    <td>
                                        <p class="text-regular text-dark-blue text-md">Wholesales Discount</p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        <div style="padding-top:30px"></div>
                        <div class="d-flex">
                            <button class="btn"
                                style="--bs-btn-border-radius: 5px !important; background-color: var(--cyan-blue); min-width:50px;">
                                <img src="media\Asset_Control_Minus.svg" class="icon-size">
                            </button>
                            <div class="text-xl px-3 text-dark-blue w-50">
                                <input class="text-center w-100" style="border-radius:5px;" value=200 />
                            </div>
                            <button class="btn"
                                style="--bs-btn-border-radius: 5px !important; background-color: var(--cyan-blue); min-width:50px;">
                                <img src="media\Asset_Control_Add.svg" class="icon-size">
                            </button>
                        </div>
                        <div class="d-flex pb-2 pt-2">
                            <div class ="text-xl text-pink text-bold">RM 22.00 <span
                                    class ="text-md text-regular text-pink text-decoration-line-through">RM 11.00</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div> --}}

            <div style="padding-bottom:50px">
            </div>
            {{-- Example 1 End --}}

            {{-- Example 2 --}}
            {{-- <div style="background: var(--white); padding: 50px; border-radius:20px;">
                    <div class="hstack gap-3 align-items-center pb-2">
                        <div class="form-check d-inline-block" style="padding-left:0px;">
                            <input class="form-check-input text-xl" style="margin:0px; border-radius:50%;" type="checkbox"
                                value="" id="supplier2">

                        </div>
                        <a href="#" class="d-inline-flex align-items-center">
                            <div class="text-xl text-bold text-dark-blue" for="supplier2">Supplier 2</div>
                            <img src="media\Asset_Control_Next.svg" alt="Next Page" class="icon-size"
                                style="margin-left: 10px">
                        </a>
                        <a href="#" class="d-inline-flex align-items-center ms-auto">
                            <div class="text-xl text-regular text-dark-blue">Edit</div>
                        </a>
                    </div>

                    <div style="border-bottom: 2px solid var(--cyan-blue)"></div>

                    <div style="padding-top:30px"></div>

                    <div class="hstack gap-3 align-items-start pb-3">
                        <div class="form-check d-inline-block" style="padding-left:0px;">
                            <input class="form-check-input text-xl" style="margin:0px; border-radius:50%;"
                                type="checkbox" value="" id="product1">
                        </div>
                        <img src="https://cdn0.rubylane.com/_podl/item/1518436/TVC3364A/Goodyear-Salesman-Sample-Miniature-Airfoam-Couch-pic-1A-2048%3A10.10-93eee4a0-f.webp"
                            class="shopping-cart-img" />
                        <div>
                            <div class="text-xl text-bold text-dark-blue pb-3 text-truncate">
                                Product Name
                            </div>
                            <table class="table table-sm table-borderless align-middle"
                                style="--bs-table-bg: var(--white);">
                                <tbody>
                                    <tr>
                                        <td>
                                            <p class="text-regular text-dark-blue text-md pe-5">Deliveries Fees</p>
                                        </td>
                                        <td>
                                            <p class="text-regular text-dark-blue text-md">MYR 39.00</p>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <p class="text-regular text-dark-blue text-md pe-5">Promotions Applied</p>
                                        </td>
                                        <td>
                                            <p class="text-regular text-dark-blue text-md">Wholesales Discount</p>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                            <div style="padding-top:30px"></div>
                            <div class="d-flex">
                                <button class="btn"
                                    style="--bs-btn-border-radius: 5px !important; background-color: var(--cyan-blue); min-width:50px;">
                                    <img src="media\Asset_Control_Minus.svg" class="icon-size">
                                </button>
                                <div class="text-xl px-3 text-dark-blue w-50">
                                    <input class="text-center w-100" style="border-radius:5px;" value=200 />
                                </div>
                                <button class="btn"
                                    style="--bs-btn-border-radius: 5px !important; background-color: var(--cyan-blue); min-width:50px;">
                                    <img src="media\Asset_Control_Add.svg" class="icon-size">
                                </button>
                            </div>
                            <div class="d-flex pb-2 pt-2">
                                <div class ="text-xl text-pink text-bold">RM 22.00 <span
                                        class ="text-md text-regular text-pink text-decoration-line-through">RM
                                        11.00</span></div>
                            </div>
                        </div>
                    </div>

                    <div class="hstack gap-3 align-items-start pb-3">
                        <div class="form-check d-inline-block" style="padding-left:0px;">
                            <input class="form-check-input text-xl" style="margin:0px; border-radius:50%;"
                                type="checkbox" value="" id="product2">
                        </div>
                        <img src="https://images.squarespace-cdn.com/content/v1/5ca3410da09a7e6e7ed285ea/1653477235891-25RV6FR3GT1EZILWOBX4/ArrowCoffeeTable2.jpg?format=750w"
                            class="shopping-cart-img" />
                        <div>
                            <div class="text-xl text-bold text-dark-blue pb-3 text-truncate">
                                Product Name
                            </div>
                            <table class="table table-sm table-borderless align-middle"
                                style="--bs-table-bg: var(--white);">
                                <tbody>
                                    <tr>
                                        <td>
                                            <p class="text-regular text-dark-blue text-md pe-5">Deliveries Fees</p>
                                        </td>
                                        <td>
                                            <p class="text-regular text-dark-blue text-md">MYR 39.00</p>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <p class="text-regular text-dark-blue text-md pe-5">Promotions Applied</p>
                                        </td>
                                        <td>
                                            <p class="text-regular text-dark-blue text-md">Wholesales Discount</p>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                            <div style="padding-top:30px"></div>
                            <div class="d-flex">
                                <button class="btn"
                                    style="--bs-btn-border-radius: 5px !important; background-color: var(--cyan-blue); min-width:50px;">
                                    <img src="media\Asset_Control_Minus.svg" class="icon-size">
                                </button>
                                <div class="text-xl px-3 text-dark-blue w-50">
                                    <input class="text-center w-100" style="border-radius:5px;" value=200 />
                                </div>
                                <button class="btn"
                                    style="--bs-btn-border-radius: 5px !important; background-color: var(--cyan-blue); min-width:50px;">
                                    <img src="media\Asset_Control_Add.svg" class="icon-size">
                                </button>
                            </div>
                            <div class="d-flex pb-2 pt-2">
                                <div class ="text-xl text-pink text-bold">RM 22.00 <span
                                        class ="text-md text-regular text-pink text-decoration-line-through">RM
                                        11.00</span></div>
                            </div>
                        </div>
                    </div>
                </div> --}}

            <div style="padding-bottom:50px"></div>
            {{-- Example 2 End --}}

            {{-- Grand Total --}}
            <div style="border-bottom: 2px solid var(--cyan-blue)"></div>

            {{-- <div class="hstack gap-3 align-items-center" style="padding-top:30px;">
                    <div class="form-check d-inline-block" style="padding-left:0px;">
                        <input class="form-check-input text-xl" style="margin:0px; border-radius:50%;" type="checkbox"
                            value="" id="all">
                    </div>
                    <div class="text-xl text-regular text-dark-blue" for="all">All</div>
                    <div class="ms-auto">
                        <table class="table table-sm table-borderless align-middle"
                            style="--bs-table-bg: var(--thin-blue);">
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
