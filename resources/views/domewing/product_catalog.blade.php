@extends('domewing.layouts.main')
@section('content')
    <p>Hello World</p>
    {{-- <div style="background: var(--thin-blue)">
        <div id="promotion" class="carousel slide" data-bs-ride="carousel" data-bs-interval="5000">
            <div class="carousel-indicators custom-carousel-indicators">
                @foreach ($promotions as $key => $promotion)
                    <button type="button" data-bs-target="#promotion" data-bs-slide-to="{{ $key }}"
                        class="{{ $key == 0 ? 'active' : '' }}" aria-label="Slide {{ $key + 1 }}"></button>
                @endforeach
            </div>
            <div class="carousel-inner">
                @foreach ($promotions as $key => $promotion)
                    <div class="carousel-item {{ $key == 0 ? 'active' : '' }}">
                        <img src="{{ $promotion['image'] }}" class="d-block w-100" alt="...">
                    </div>
                @endforeach
            </div>
            <button class="carousel-control-prev" type="button" data-bs-target="#promotion" data-bs-slide="prev">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#promotion" data-bs-slide="next">
                <span class="carousel-control-next-icon" aria-hidden="true"></span>
            </button>
        </div>
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
@endsection
