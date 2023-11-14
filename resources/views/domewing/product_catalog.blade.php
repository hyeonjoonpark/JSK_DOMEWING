@extends('domewing.layouts.main')
@section('content')
    <div style="background: var(--thin-blue)">
        @if (count($image_banners) > 0)
            <div class="card-inner" style="padding:0%">
                <div id="carouselExCap" class="carousel slide" data-bs-ride="carousel">
                    <ol class="carousel-indicators">
                        @foreach ($image_banners as $index => $image_banner)
                            <li data-bs-target="#carouselExCap" data-bs-slide-to="{{ $index }}"
                                class="{{ $index === 0 ? 'active' : '' }}"></li>
                        @endforeach
                    </ol>
                    <div class="carousel-inner">
                        @foreach ($image_banners as $index => $image_banner)
                            <div class="carousel-item{{ $index === 0 ? ' active' : '' }}">
                                <img src="{{ asset('library/' . $image_banner->source) }}" class="d-block w-100">
                            </div>
                        @endforeach
                    </div>
                    <a class="carousel-control-prev" href="#carouselExCap" role="button" data-bs-slide="prev">
                        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                        <span class="visually-hidden">Previous</span>
                    </a>
                    <a class="carousel-control-next" href="#carouselExCap" role="button" data-bs-slide="next">
                        <span class="carousel-control-next-icon" aria-hidden="true"></span>
                        <span class="visually-hidden">Next</span>
                    </a>
                </div>
            </div>
        @endif

        <div class="nk-content nk-content-fluid">
            <div class="card card-bordered">
                <div class="mx-4" style="background: var(--white);">
                    <div class="nk-content-body">
                        <div class="nk-block nk-block-lg p-4">
                            <div class="nk-block-head">
                                <div class="nk-block-head-content">
                                    <h1 class="title nk-block-title fw-bold"
                                        style="color: {{ $theme_color->color_code ?? 'var(--dark-blue)' }} !important;">
                                        Category Top
                                    </h1>
                                </div>
                            </div>

                            <div class="nk-block">
                                <div class="nk-block-content">
                                    <div class="row pb-3" style="border-bottom: 2px solid var(--cyan-blue)">
                                        @foreach ($categoriesTop as $key => $categoryTop)
                                            <div class="col-xl-3 col-lg-6">
                                                <button type="button" class="btn category-button"
                                                    id="categoryButton{{ $key }}">
                                                    <img src="{{ asset($categoryTop['image']) }}"
                                                        alt="{{ $categoryTop['title'] }}">
                                                    <span
                                                        class="fs-22px text-nowrap text-truncate">{{ $categoryTop['title'] }}</span>
                                                </button>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>

                            <div class="nk-block">
                                <div class="row pb-3">
                                    @foreach ($product_items as $key => $product_item)
                                        <div class="col-xl-3 col-lg-6 pb-3 px-3">
                                            <img src="{{ $product_item['image'] }}" alt="{{ $product_item['title'] }}"
                                                href="#" style="height:80%; width:100%;" class="img-fluid">
                                            <div class="pt-3"></div>
                                            <p class="fs-22px text-nowrap text-truncate" href="#"
                                                style="color: var(--dark-blue)">
                                                {{ $product_item['title'] }}</p>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="nk-content nk-content-fluid">
            <div class="card card-bordered">
                <div class="mx-4" style="background: var(--white);">
                    <div class="nk-content-body">
                        <div class="nk-block nk-block-lg p-4">
                            <div class="nk-block-head">
                                <div class="nk-block-head-content">
                                    <h1 class="title nk-block-title fw-bold"
                                        style="color: {{ $theme_color->color_code ?? 'var(--dark-blue)' }} !important;">
                                        Lastest Goods
                                    </h1>
                                </div>
                            </div>

                            <div class="nk-block">
                                <div class="nk-block-content">
                                    <div class="row pb-3" style="border-bottom: 2px solid var(--cyan-blue)">
                                        @foreach ($categoriesTop as $key => $categoryTop)
                                            <div class="col-xl-3 col-lg-6">
                                                <button type="button" class="btn category-button"
                                                    id="categoryButton{{ $key }}">
                                                    <img src="{{ asset($categoryTop['image']) }}"
                                                        alt="{{ $categoryTop['title'] }}">
                                                    <span
                                                        class="fs-22px text-nowrap text-truncate">{{ $categoryTop['title'] }}</span>
                                                </button>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>

                            <div class="nk-block">
                                <div class="row pb-3">
                                    @foreach ($product_items as $key => $product_item)
                                        <div class="col-xl-3 col-lg-6 pb-3 px-3">
                                            <img src="{{ $product_item['image'] }}" alt="{{ $product_item['title'] }}"
                                                href="#" style="height:80%; width:100%;" class="img-fluid">
                                            <div class="pt-3"></div>
                                            <p class="fs-22px text-nowrap text-truncate" href="#"
                                                style="color: var(--dark-blue)">
                                                {{ $product_item['title'] }}</p>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="nk-content nk-content-fluid"
            style="background: {{ $theme_color->color_code ?? 'var(--dark-blue)' }} !important;">
            <div class="card card-bordered">
                <div class="mx-4">
                    <div class="nk-content-body">
                        <div class="nk-block nk-block-lg p-4">
                            <div class="nk-block-head">
                                <div class="nk-block-head-content">
                                    <h1 class="title nk-block-title fw-bold"
                                        style="color: {{ $theme_color->color_code ?? 'var(dark-blue)' }} !important;">
                                        Recommended For You
                                    </h1>
                                </div>
                            </div>

                            <div class="nk-block">
                                <div class="custom-inner-content">

                                    <div id="recommendation" class="carousel slide partnership-padding category-padding">

                                        <div class="carousel-inner">
                                            <div class="carousel-item active">
                                                <div class="row text-center">

                                                    <div class="col-2 d-block m-auto"><img
                                                            src="https://images.unsplash.com/photo-1559563458-527698bf5295?auto=format&fit=crop&q=80&w=1000&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxzZWFyY2h8M3x8ZmFzaGlvbiUyMGFjY2Vzc29yaWVzfGVufDB8fDB8fHww"
                                                            alt="Image 1">
                                                    </div>
                                                    <div class="col-2 d-block m-auto"><img
                                                            src="https://upload.wikimedia.org/wikipedia/commons/thumb/2/29/Burberry_handbag.jpg/1200px-Burberry_handbag.jpg"
                                                            alt="Image 1">
                                                    </div>
                                                    <div class="col-2 d-block m-auto"><img
                                                            src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcTiGaiAFyNhRleh_kzCB_ZE72neWex8C9hslg&usqp=CAU"
                                                            alt="Image 1">
                                                    </div>
                                                    <div class="col-2 d-block m-auto"><img
                                                            src="https://upload.wikimedia.org/wikipedia/commons/thumb/d/d7/2023_Okulary_korekcyjne.jpg/800px-2023_Okulary_korekcyjne.jpg"
                                                            alt="Image 1">
                                                    </div>
                                                    <div class="col-2 d-block m-auto"><img
                                                            src="https://e0.pxfuel.com/wallpapers/554/53/desktop-wallpaper-ladies-shoes-fashion-luxury-accessories-for-section-%D1%81%D1%82%D0%B8%D0%BB%D1%8C-fashion-accessories-thumbnail.jpg"
                                                            alt="Image 1">
                                                    </div>
                                                </div>

                                            </div>
                                            <div class="carousel-item">
                                                <div class="row text-center">

                                                    <div class="col-2 d-block m-auto"><img
                                                            src="https://images.pexels.com/photos/33129/popcorn-movie-party-entertainment.jpg?auto=compress&cs=tinysrgb&dpr=1&w=500"
                                                            alt="Image 1">
                                                    </div>
                                                    <div class="col-2 d-block m-auto"><img
                                                            src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcTfDh2paUVRprS1KsOjkYJdFHYu3T-FW1Dy2w&usqp=CAU"
                                                            alt="Image 1">
                                                    </div>
                                                    <div class="col-2 d-block m-auto"><img
                                                            src="https://image.made-in-china.com/202f0j00KJAWzrPaaTup/Free-Sample-Energy-Drink-HACCP-250ml-Energy-Drink-Halal-Energy-Drinks-Manufacturer-in-Vietnam-Wholesale-Price.webp"
                                                            alt="Image 1">
                                                    </div>
                                                    <div class="col-2 d-block m-auto"><img
                                                            src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcR_K4riWIa8KESHSDyLSxogb8UFLJMmCKe5Ug&usqp=CAU"
                                                            alt="Image 1">
                                                    </div>
                                                    <div class="col-2 d-block m-auto"><img
                                                            src="https://media.cnn.com/api/v1/images/stellar/prod/220510125009-10-fried-foods-churros.jpg?c=original"
                                                            alt="Image 1">
                                                    </div>
                                                </div>

                                            </div>
                                            <div class="carousel-item">
                                                <div class="row text-center">
                                                    <div class="col-2 d-block m-auto"><img
                                                            src="data:image/jpeg;base64,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"
                                                            alt="Image 1">
                                                    </div>
                                                    <div class="col-2 d-block m-auto"><img
                                                            src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcSlsC3rDRZhFNebGNXqFYL-LiXlE2U6tf2l5g&usqp=CAU"
                                                            alt="Image 1">
                                                    </div>
                                                    <div class="col-2 d-block m-auto"><img
                                                            src="https://www.shutterstock.com/image-illustration/water-bottle-white-background-one-260nw-1889171032.jpg"
                                                            alt="Image 1">
                                                    </div>
                                                    <div class="col-2 d-block m-auto"><img
                                                            src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRMtv-kpS7lrZn1rygAD4Vtc7CRIUnv8SIWsQ&usqp=CAU"
                                                            alt="Image 1">
                                                    </div>
                                                    <div class="col-2 d-block m-auto"><img
                                                            src="https://api.totallypromotional.com/Data/Media/Catalog/6/800/021433ee-c8ec-4c30-90f5-062c8d658aba27-oz-Translucent-Snap-Lid-Water-Bottle-TSB119-orange.jpg"
                                                            alt="Image 1">
                                                    </div>
                                                </div>

                                            </div>
                                        </div>
                                        <div class="carousel-control-prev"
                                            style="justify-content: flex-start; opacity:1;">
                                            <button type="button" data-bs-target="#recommendation" data-bs-slide="prev"
                                                class="partnership-left-button">
                                                <img src={{ asset('media\Asset_Control_SmallDropdown.svg') }} />
                                            </button>

                                        </div>
                                        <div class="carousel-control-next" style="justify-content: flex-end; opacity:1;">
                                            <button type="button" data-bs-target="#recommendation" data-bs-slide="next"
                                                class="partnership-right-button">
                                                <img src={{ asset('media\Asset_Control_SmallDropdown.svg') }} />
                                            </button>
                                        </div>

                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


    {{-- <div style="background: var(--thin-blue)">
        <div style="padding-top:100px"></div>
        <div class="custom-inner-content">
            <div class="category-padding" style="background: var(--white);">
                <p class="text-bold text-xxl text-dark-blue">Category Top</p>
                <div style="padding-top:50px"></div>
                <div class="row pb-3 d-flex flex-wrap" style="border-bottom: 2px solid var(--cyan-blue)">
                    @foreach ($categoriesTop as $key => $categoryTop)
                        <div class="col-xl-3 col-lg-6">
                            <button type="button" class="btn category-button" id="categoryButton{{ $key }}">
                                <img src="{{ $categoryTop['image'] }}" alt="{{ $categoryTop['title'] }}">
                                <span
                                    class="text-regular text-cyan-blue text-lg text-nowrap text-truncate">{{ $categoryTop['title'] }}</span>
                            </button>
                        </div>
                    @endforeach
                </div>
                <div style="padding-top:50px"></div>
                <div class="row pb-3">
                    @foreach ($product_items as $key => $product_item)
                        <div class="col-xl-3 col-lg-6 pb-3 px-3">
                            <a href="{{ route('product_detail') }}">
                                <img src="{{ $product_item['image'] }}" alt="{{ $product_item['title'] }}"
                                    style="height:80%; width:100%;" class="img-fluid">
                            </a>
                            <div style="padding-top:10px"></div>
                            <a href="{{ route('product_detail') }}">
                                <p class="text-regular text-dark-blue text-lg text-nowrap text-truncate d-inline-block">
                                    {{ $product_item['title'] }}</p>
                            </a>

                        </div>
                    @endforeach
                </div>
            </div>
        </div>
        <div style="padding-top:100px"></div>
        <div class="custom-inner-content">
            <div class="category-padding" style="background: var(--white);">
                <p class="text-bold text-xxl text-dark-blue">Lastest Goods</p>
                <div style="padding-top:50px"></div>
                <div class="row pb-3" style="border-bottom: 2px solid var(--cyan-blue)">
                    @foreach ($categoriesTop as $key => $categoryTop)
                        <div class="col-xl-3 col-lg-6">
                            <button type="button" class="btn category-button" id="categoryButton{{ $key }}">
                                <img src="{{ $categoryTop['image'] }}" alt="{{ $categoryTop['title'] }}">
                                <span
                                    class="text-regular text-cyan-blue text-lg text-nowrap text-truncate">{{ $categoryTop['title'] }}</span>
                            </button>
                        </div>
                    @endforeach
                </div>
                <div style="padding-top:50px"></div>
                <div class="row pb-3">
                    @foreach ($product_items as $key => $product_item)
                        <div class="col-xl-3 col-lg-6 pb-3 px-3">
                            <img src="{{ $product_item['image'] }}" alt="{{ $product_item['title'] }}"
                                href="{{ route('search_result') }}" style="height:80%; width:100%;" class="img-fluid">
                            <div style="padding-top:10px"></div>
                            <p class="text-regular text-dark-blue text-lg text-nowrap text-truncate"
                                href="{{ route('search_result') }}">
                                {{ $product_item['title'] }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
        <div style="padding-top:100px"></div>
        <div style="background: var(--dark-blue)">
            <div class="custom-inner-content">
                <div style="padding-top:100px"></div>
                <div class="partnership-padding" style="padding-bottom: 0px !important;">
                    <p class="text-bold text-xxl text-dark-blue">Recommended For You</p>
                </div>
                <div id="recommendation" class="carousel slide partnership-padding category-padding">

                    <div class="carousel-inner">
                        <div class="carousel-item active">
                            <div class="row text-center">
                                <!-- First Row -->
                                <div class="col-2 d-block mx-auto"><img src="media\Asset_About_Product_Search.svg"
                                        alt="Image 1">
                                </div>
                                <div class="col-2 d-block mx-auto"><img src="media\Asset_About_Product_Search.svg"
                                        alt="Image 2">
                                </div>
                                <div class="col-2 d-block mx-auto"><img src="media\Asset_About_Product_Search.svg"
                                        alt="Image 3">
                                </div>
                                <div class="col-2 d-block mx-auto"><img src="media\Asset_About_Product_Search.svg"
                                        alt="Image 4">
                                </div>
                                <div class="col-2 d-block mx-auto"><img src="media\Asset_About_Product_Search.svg"
                                        alt="Image 5">
                                </div>
                            </div>

                        </div>
                        <div class="carousel-item">
                            <div class="row text-center">
                                <!-- First Row -->
                                <div class="col-2 d-block mx-auto"><img src="media\Asset_About_Product_Search.svg"
                                        alt="Image 1">
                                </div>
                                <div class="col-2 d-block mx-auto"><img src="media\Asset_About_Product_Search.svg"
                                        alt="Image 2">
                                </div>
                                <div class="col-2 d-block mx-auto"><img src="media\Asset_About_Product_Search.svg"
                                        alt="Image 3">
                                </div>
                                <div class="col-2 d-block mx-auto"><img src="media\Asset_About_Product_Search.svg"
                                        alt="Image 4">
                                </div>
                                <div class="col-2 d-block mx-auto"><img src="media\Asset_About_Product_Search.svg"
                                        alt="Image 5">
                                </div>
                            </div>

                        </div>
                        <div class="carousel-item">
                            <div class="row text-center">
                                <!-- First Row -->
                                <div class="col-2 d-block mx-auto"><img src="media\Asset_About_Product_Listing.svg"
                                        alt="Image 1">
                                </div>
                                <div class="col-2 d-block mx-auto"><img src="media\Asset_About_Product_Listing.svg"
                                        alt="Image 2">
                                </div>
                                <div class="col-2 d-block mx-auto"><img src="media\Asset_About_Product_Listing.svg"
                                        alt="Image 3">
                                </div>
                                <div class="col-2 d-block mx-auto"><img src="media\Asset_About_Product_Listing.svg"
                                        alt="Image 4">
                                </div>
                                <div class="col-2 d-block mx-auto"><img src="media\Asset_About_Product_Listing.svg"
                                        alt="Image 5">
                                </div>
                            </div>

                        </div>
                        <div style="padding-top:45px"></div>
                    </div>
                    <div class="carousel-control-prev" style="justify-content: flex-start; opacity:1;">
                        <button type="button" data-bs-target="#recommendation" data-bs-slide="prev"
                            class="partnership-left-button">
                            <img src="media\Asset_Control_SmallDropdown.svg" />
                        </button>
                    </div>
                    <div class="carousel-control-next" style="justify-content: flex-end; opacity:1;">
                        <button type="button" data-bs-target="#recommendation" data-bs-slide="next"
                            class="partnership-right-button">
                            <img src="media\Asset_Control_SmallDropdown.svg" />
                        </button>
                    </div>
                </div>
                <div style="padding-top:100px"></div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const buttons = document.querySelectorAll('.category-button');

            buttons.forEach(function(button, index) {
                button.addEventListener('click', function() {
                    buttons.forEach(function(btn) {
                        btn.classList.remove('active');
                    });
                    this.classList.add('active');
                });
            });
        });
    </script> --}}
@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const buttons = document.querySelectorAll('.category-button');

            buttons.forEach(function(button, index) {
                button.addEventListener('click', function() {
                    buttons.forEach(function(btn) {
                        btn.classList.remove('active');
                    });
                    this.classList.add('active');
                });
            });
        });
    </script>
@endsection
