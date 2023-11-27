@extends('domewing.layouts.main')
@section('content')
    <div class="px-lg-5 px-3" style="background: var(--thin-blue)">
        <div class="nk-content nk-content-fluid">
            <div class="card card-bordered">
                <div class="p-lg-5 p-4" style="background: var(--white);">
                    <div class="nk-content-body">
                        <div class="row ">
                            {{-- Image Slider --}}
                            <div class="col-xl-4 col-lg-12 pb-4">
                                <img id=featured src="{{ $productInfo->productImage }}">

                                <div id="slide-wrapper">
                                    <img id="slideLeft" class="arrow" src={{ asset('media/Asset_Control_Back.svg') }}>

                                    <div id="slider">
                                        <img class="thumbnail active" src="{{ $productInfo->productImage }}">
                                        {{-- <img class="thumbnail"
                                            src="https://cdn1.npcdn.net/image/1668578748821c8af71ec3b8f7d3b415bbad340051.jpg?md5id=9866b8a83d35abdd89ed76d565d71f75&new_width=1150&new_height=2500&w=-62170009200">
                                        <img class="thumbnail"
                                            src="https://image.made-in-china.com/202f0j00ecNhLPGbfilF/32mm-S-Speed-Sample-Provided-Furniture-Adjustable-Office-Desk-with-Low-Price.webp">

                                        <img class="thumbnail"
                                            src="https://cdn0.rubylane.com/_podl/item/1518436/TVC3364A/Goodyear-Salesman-Sample-Miniature-Airfoam-Couch-pic-1A-2048%3A10.10-93eee4a0-f.webp">

                                        <img class="thumbnail"
                                            src="https://cdn1.npcdn.net/image/1668578748821c8af71ec3b8f7d3b415bbad340051.jpg?md5id=9866b8a83d35abdd89ed76d565d71f75&new_width=1150&new_height=2500&w=-62170009200">
                                        <img class="thumbnail"
                                            src="https://image.made-in-china.com/202f0j00ecNhLPGbfilF/32mm-S-Speed-Sample-Provided-Furniture-Adjustable-Office-Desk-with-Low-Price.webp">

                                        <img class="thumbnail"
                                            src="https://cdn0.rubylane.com/_podl/item/1518436/TVC3364A/Goodyear-Salesman-Sample-Miniature-Airfoam-Couch-pic-1A-2048%3A10.10-93eee4a0-f.webp">
                                        <img class="thumbnail"
                                            src="https://cdn1.npcdn.net/image/1668578748821c8af71ec3b8f7d3b415bbad340051.jpg?md5id=9866b8a83d35abdd89ed76d565d71f75&new_width=1150&new_height=2500&w=-62170009200">
                                        <img class="thumbnail"
                                            src="https://image.made-in-china.com/202f0j00ecNhLPGbfilF/32mm-S-Speed-Sample-Provided-Furniture-Adjustable-Office-Desk-with-Low-Price.webp">

                                        <img class="thumbnail"
                                            src="https://cdn0.rubylane.com/_podl/item/1518436/TVC3364A/Goodyear-Salesman-Sample-Miniature-Airfoam-Couch-pic-1A-2048%3A10.10-93eee4a0-f.webp"> --}}
                                    </div>

                                    <img id="slideRight" class="arrow" src={{ asset('media/Asset_Control_Next.svg') }}>
                                </div>
                            </div>
                            {{-- Image Slider End --}}

                            {{-- Product Description --}}
                            <div class="col-xl-8 col-lg-12 pb-2">
                                <div class="d-flex align-items-center justify-content-between py-3">
                                    <h4 class="product-title my-auto" style="color:var(--dark-blue)">
                                        {{ $productInfo->productName }}
                                    </h4>

                                    {{-- <div class="dropdown">
                                        <a class="btn dropdown-toggle" href="#" type="button"
                                            data-bs-toggle="dropdown" style="color:var(--dark-blue)">MYR<img
                                                src={{ asset('media\Asset_Control_SmallDropdown.svg') }} alt="Dropdown"
                                                class="icon-size px-1"></a>
                                        <div class="dropdown-menu">
                                            <ul class="link-list-opt">
                                                <li><a href="#" style="color:var(--dark-blue)"><span>MYR</span></a>
                                                </li>
                                                <li><a href="#" style="color:var(--dark-blue)"><span>KRW</span></a>
                                                </li>
                                            </ul>
                                        </div>
                                    </div> --}}
                                </div>

                                <div class="d-flex flex-wrap align-items-center py-1">
                                    <ul class="rating" style="display: table;">
                                        <li><em class="icon ni ni-star-fill"></em></li>
                                        <li><em class="icon ni ni-star-fill"></em></li>
                                        <li><em class="icon ni ni-star-fill"></em></li>
                                        <li><em class="icon ni ni-star-half-fill"></em></li>
                                        <li><em class="icon ni ni-star"></em></li>
                                    </ul>
                                    <h6 class="p-1 text-center my-auto" style="color: var(--dark-blue);">(378)
                                        {{ $translation['sold'] }}</h6>
                                    <div class="hstack horizontal-scrolling py-1">
                                        <h6 class="product-tags px-2 py-1 mx-1 my-auto">
                                            {{ $translation['location'] }}
                                        </h6>
                                        <h6 class="product-tags px-2 py-1 mx-1 my-auto">
                                            {{ $translation['promotion_tag'] }}
                                        </h6>
                                    </div>
                                </div>

                                <div class="d-flex align-items-end py-4">
                                    <h4 class ="text-pink fw-bold m-0 pe-1">KRW
                                        {{ number_format($productInfo->productPrice, 2) }}</h4>
                                    <h6 class ="text-pink">
                                        {{ $translation['Unit'] }}
                                    </h6>
                                </div>

                                <ul class="pricing-features pb-3 fs-18px" style="color: var(--dark-blue);">
                                    <li>
                                        <h6 class="w-40 align-self-center m-0" style="color: var(--dark-blue);">
                                            {{ $translation['available_units'] }}</h6>
                                        <h6 class="w-50 align-self-center m-0" style="color: var(--dark-blue);">3000</h6>
                                    </li>
                                    <li>
                                        <h6 class="w-40 align-self-center m-0" style="color: var(--dark-blue);">
                                            {{ $translation['unit_in'] }}</h6>
                                        <h6 class="w-50 align-self-center m-0" style="color: var(--dark-blue);">Carbon</h6>
                                    </li>
                                    <li>
                                        <h6 class="w-40 align-self-center m-0" style="color: var(--dark-blue);">
                                            {{ $translation['minimum_order'] }}</h6>
                                        <h6 class="w-50 align-self-center m-0" style="color: var(--dark-blue);">20 Units
                                        </h6>
                                    </li>
                                    <li>
                                        <h6 class="w-40 align-self-center m-0" style="color: var(--dark-blue);">
                                            {{ $translation['wholesale_price'] }}</h6>
                                        <h6 class="w-50 align-self-center m-0" style="color: var(--dark-blue);">
                                            Buy 200 and more, KRW 2000.00 /Unit</h6>
                                    </li>
                                    <li>
                                        <h6 class="w-40 align-self-center m-0" style="color: var(--dark-blue);">
                                            {{ $translation['shipping_cost'] }}</h6>
                                        <h6 class="w-50 align-self-center m-0" style="color: var(--dark-blue);">
                                            KRW {{ number_format($productInfo->shippingCost, 2) }}</h6>
                                    </li>
                                </ul>

                                <div class="d-flex py-3">
                                    <div class="form-group">
                                        <div class="form-control-wrap number-spinner-wrap">
                                            <button class="btn btn-icon btn-primary number-spinner-btn number-minus"
                                                onclick="minusQuantity()">
                                                <em class="icon ni ni-minus"></em>
                                            </button>
                                            <input type="number" class="form-control number-spinner" id="quantity"
                                                placeholder="1" value="1" min="1"
                                                style="color: var(--dark-blue);">
                                            <button class="btn btn-icon btn-primary number-spinner-btn number-plus"
                                                onclick="addQuantity()">
                                                <em class="icon ni ni-plus"></em>
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <div class="d-flex align-items-end py-4">
                                    <h4 id="currentPrice" class="text-pink fw-bold m-0 pe-1"></h4>
                                    <h6 id="originalPrice" class="text-pink text-decoration-line-through"></h6>
                                </div>

                                <div class="d-flex flex-wrap">
                                    <button class="btn btn-secondary me-3 my-1"
                                        style="padding: 10px 15px; background: var(--dark-blue)">
                                        <p class="text-regular text-white text-md">{{ $translation['add_to_cart'] }}</p>
                                    </button>
                                    <button class="btn btn-secondary me-3 my-1"
                                        style="padding: 10px 15px; background: var(--pure-white); border: 1px solid var(--dark-blue)">
                                        <p class="text-regular text-md" style="color: var(--dark-blue)">
                                            {{ $translation['purchase_now'] }}</p>
                                    </button>
                                    <button class="btn btn-secondary me-3 my-1"
                                        style="padding: 10px 15px; background: var(--pink); border: none">
                                        <p class="text-regular text-white text-md">{{ $translation['add_to_wishlist'] }}
                                        </p>
                                    </button>
                                </div>
                            </div>
                            {{-- Product Description End --}}

                            {{-- Product Details --}}
                            <div class="py-4">
                                <h4 style="color: var(--dark-blue);">{{ $translation['product_detail'] }}</h4>
                                <div class="pb-1" style="border-bottom: 2px solid var(--cyan-blue)"></div>
                                <div class="py-3 text-center">
                                    {!! $productInfo->productDetail !!}
                                </div>
                            </div>
                            {{-- Product Details End --}}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="px-lg-5 px-3" style="background: var(--dark-blue)">
        <div class="nk-content nk-content-fluid">
            <div class="card card-bordered">
                <div class="p-lg-5 p-4" style="background: var(--white);">
                    <div class="py-3" style="background: var(--pure-white);">
                        <h2 class="fw-bold pb-2" style="color: var(--dark-blue);">
                            {{ $translation['product_from_same_supplier'] }}</h2>
                        <div class="row g-0 pb-3 horizontal-scrolling">
                            @foreach ($otherProducts as $key => $product_item)
                                <div class="col-xl-3 col-lg-4 col-md-6 col-sm-12 pb-3 px-3"
                                    style="display: inline-block;">
                                    <div style="background-color: var(--pure-white)">
                                        <a href="/domewing/product/{{ $product_item->upload_id }}">
                                            <img class="product-image" src="{{ $product_item->productImage }}" />
                                            <div class="pt-2 px-3">
                                                <h4 class="text-nowrap text-truncate m-auto"
                                                    style="color: var(--dark-blue);">
                                                    {{ $product_item->productName }}</h4>
                                                {{-- display rating --}}
                                                <div class="d-flex flex-wrap align-items-center py-1">
                                                    <ul class="rating" style="display: table;">
                                                        <li><em class="icon ni ni-star-fill"></em></li>
                                                        <li><em class="icon ni ni-star-fill"></em></li>
                                                        <li><em class="icon ni ni-star-fill"></em></li>
                                                        <li><em class="icon ni ni-star-half-fill"></em></li>
                                                        <li><em class="icon ni ni-star"></em></li>
                                                    </ul>
                                                    <h6 class="p-1 text-center my-auto" style="color: var(--dark-blue);">
                                                        (378)
                                                        {{ $translation['sold'] }}</h6>
                                                </div>

                                                <h6 class="m-0" style="color: var(--dark-blue);">
                                                    {{ $translation['from'] }}</h6>
                                                <div class="d-flex align-items-end">
                                                    <h4 class ="text-pink fw-bold m-0 pe-1">KRW
                                                        {{ number_format($product_item->productPrice, 2) }}</h4>
                                                    <h6 class ="text-pink">{{ $translation['Unit'] }}</h6>
                                                </div>
                                            </div>
                                        </a>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <div class="p-4"></div>

            {{-- <div class="card card-bordered">
                <div class="p-lg-5 p-4" style="background: var(--white);">
                    <div class="py-3" style="background: var(--pure-white);">
                        <h2 class="fw-bold" style="color: var(--dark-blue);">Similar Products</h2>
                        <div class="row g-0 pb-3 horizontal-scrolling">
                            @foreach ($similarProducts as $key => $product_item)
                                <div class="col-xl-3 col-lg-4 col-md-6 col-sm-12 pb-3 px-3"
                                    style="display: inline-block;">
                                    <div style="background-color: var(--pure-white)">
                                        <a href="/domewing/product/{{ $product_item->upload_id }}">
                                            <img class="product-image" src="{{ $product_item->productImage }}" />
                                            <div class="pt-2 px-3">
                                                <h4 class="text-nowrap text-truncate m-auto"
                                                    style="color: var(--dark-blue);">
                                                    {{ $product_item->productName }}</h4> --}}
            {{-- display rating --}}
            {{-- <div class="d-flex flex-wrap align-items-center py-1">
                                                    <ul class="rating" style="display: table;">
                                                        <li><em class="icon ni ni-star-fill"></em></li>
                                                        <li><em class="icon ni ni-star-fill"></em></li>
                                                        <li><em class="icon ni ni-star-fill"></em></li>
                                                        <li><em class="icon ni ni-star-half-fill"></em></li>
                                                        <li><em class="icon ni ni-star"></em></li>
                                                    </ul>
                                                    <h6 class="p-1 text-center my-auto" style="color: var(--dark-blue);">
                                                        (378)
                                                        sold</h6>
                                                </div>

                                                <h6 class="m-0" style="color: var(--dark-blue);">From</h6>
                                                <div class="d-flex align-items-end">
                                                    <h4 class ="text-pink fw-bold m-0 pe-1">KRW
                                                        {{ number_format($product_item->productPrice, 2) }}</h4>
                                                    <h6 class ="text-pink">/Unit</h6>
                                                </div>
                                            </div>
                                        </a>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div> --}}
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        function formatCurrency(amount) {
            const formatter = new Intl.NumberFormat('en-US', {
                style: 'currency',
                currency: 'KRW',
                minimumFractionDigits: 2,
            });
            const formattedAmount = formatter.format(amount);
            return formattedAmount.replace('₩', 'KRW ');
        }

        function calculatePrice() {
            const quantity = document.getElementById('quantity').value;
            const unitPrice = "{{ $productInfo->productPrice }}";
            const originalPrice = parseInt("{{ $productInfo->productPrice }}") + 500;

            const totalPrice = quantity * unitPrice;
            const totalOriginalPrice = quantity * originalPrice;
            document.getElementById('currentPrice').textContent = formatCurrency(totalPrice);
            document.getElementById('originalPrice').textContent = formatCurrency(totalOriginalPrice);
        }

        const quantityInput = document.getElementById('quantity');
        quantityInput.addEventListener('input', calculatePrice);

        function minusQuantity() {
            if (quantityInput.value > 1) {
                quantityInput.value = parseInt(quantityInput.value) - 1;
                calculatePrice();
            }
        };

        function addQuantity() {
            quantityInput.value = parseInt(quantityInput.value) + 1;
            calculatePrice();
        };

        // Calculate the initial price
        calculatePrice();

        let thumbnails = document.getElementsByClassName('thumbnail')

        let activeImages = document.getElementsByClassName('active')

        for (var i = 0; i < thumbnails.length; i++) {

            thumbnails[i].addEventListener('mouseup', function() {
                console.log(activeImages)

                if (activeImages.length > 0) {
                    activeImages[0].classList.remove('active')
                }


                this.classList.add('active')
                document.getElementById('featured').src = this.src
            })
        }

        let buttonRight = document.getElementById('slideRight');
        let buttonLeft = document.getElementById('slideLeft');

        buttonLeft.addEventListener('click', function() {
            document.getElementById('slider').scrollLeft -= 180
        })

        buttonRight.addEventListener('click', function() {
            document.getElementById('slider').scrollLeft += 180
        })

        function changeCurrency(selectedItem) {
            var selectedText = selectedItem.textContent;
            document.getElementById("lblCurrency").innerHTML = selectedText;
        }
    </script>
@endsection
