@extends('domewing.layouts.main')
@section('content')
    <div style="background: var(--white);">
        <div class="p-lg-5 p-2">
            <h4 style="color: var(--dark-blue);">Search Result for</h4>
            <div class="d-flex">
                @if (empty($keyword))
                    <h3 class="m-0 mt-auto pe-1" style="color: var(--dark-blue);">
                        All Products
                    </h3>
                @else
                    <h3 class="m-0 mt-auto pe-1" style="color: var(--dark-blue);">
                        {{ $keyword }}
                    </h3>
                @endif
                <h5 class="my-auto" style="color: var(--dark-blue);">( {{ $product_items->total() }} )</h5>
            </div>
            <div class="pt-5">
                {{-- Filtering Here --}}
                <div class="pb-2" style="border-bottom: 2px solid var(--dark-blue)">
                    <div class="d-flex flex-wrap">
                        <div class="p-2">
                            <h6 style="coloe: var(--dark-blue)">Sort By</h6>
                            <div class="btn-group">
                                <button type="button" class="btn fs-22px dropdown-toggle" data-bs-toggle="dropdown"
                                    style="background-color: var(--cyan-blue);">
                                    <h4 class="my-auto" style="color: var(--dark-blue)" id="lblSortBy">
                                        {{ $displaySort }}
                                    </h4>
                                    <img class="icon-size ms-1" src={{ asset('media\Asset_Control_SmallDropdown.svg') }}>
                                </button>
                                <div class="dropdown-menu dropdown-menu-start">
                                    <ul class="link-list-opt no-bdr">
                                        @foreach ($sortBy as $key => $sortByItem)
                                            <li><a href="#" onclick="changeSortBy(this)">
                                                    <span>{{ $sortByItem['title'] }}</span>
                                                </a>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <div class="p-2">
                            <h6 style="coloe: var(--dark-blue)">Category</h6>
                            <div class="btn-group">
                                <button type="button" class="btn fs-22px dropdown-toggle" data-bs-toggle="dropdown"
                                    style="background-color: var(--cyan-blue);">
                                    <h4 class="my-auto" style="color: var(--dark-blue)" id="lblCategory">
                                        {{ $displayCategory }}
                                    </h4>
                                    <img class="icon-size ms-1" src={{ asset('media\Asset_Control_SmallDropdown.svg') }}>
                                </button>
                                <div class="dropdown-menu dropdown-menu-start">
                                    <ul class="link-list-opt no-bdr">
                                        @foreach ($categoriesTop as $key => $categoryTop)
                                            <li><a href="#"
                                                    onclick="changeCategory(this)"><span>{{ $categoryTop['title'] }}</span></a>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                {{-- Filtering End --}}

                @if (count($product_items) < 1)
                    <img class="mx-lg-5 px-lg-5 mx-0 px-o" src="{{ asset('media/Asset_Notif_Error.svg') }}">
                    <h5 class="text-center py-5" style="color: var(--dark-blue);">
                        No Item Found</h5>
                @else
                    {{-- Product Listing Here --}}
                    <div class="row g-0 pb-3 pt-5">
                        @foreach ($product_items as $key => $product_item)
                            <div class="col-xl-3 col-lg-4 col-md-6 col-sm-12 p-3" style="display: inline-block;">
                                <div style="background-color: var(--white)">
                                    <a href="/domewing/product/{{ $product_item->id }}">
                                        <img class="product-image" src="{{ $product_item->image }}" />
                                        <div class="pt-2 px-3">
                                            <h4 class="text-nowrap text-truncate m-auto" style="color: var(--dark-blue);">
                                                {{ $product_item->name }}</h4>
                                            {{-- display rating --}}
                                            <div class="d-flex flex-wrap align-items-center py-1">
                                                <ul class="rating" style="display: table;">
                                                    <li><i class="icon ni ni-star-fill"></i></li>
                                                    <li><i class="icon ni ni-star-fill"></i></li>
                                                    <li><i class="icon ni ni-star-fill"></i></li>
                                                    <li><i class="icon ni ni-star-half-fill"></i></li>
                                                    <li><i class="icon ni ni-star"></i></li>
                                                </ul>
                                                <h6 class="p-1 text-center my-auto" style="color: var(--dark-blue);">
                                                    (378)
                                                    {{ $translation['sold'] }}</h6>
                                            </div>

                                            <h6 class="m-0" style="color: var(--dark-blue);">
                                                {{ $translation['from'] }}</h6>
                                            <div class="d-flex align-items-end">
                                                <h4 class ="text-pink fw-bold m-0 pe-1">KRW
                                                    {{ number_format($product_item->price, 2) }}
                                                </h4>
                                                <h6 class ="text-pink">{{ $translation['Unit'] }}</h6>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                            </div>
                        @endforeach

                        <div class="d-flex justify-content-end py-5">
                            <ul class="pagination">
                                <!-- Previous Page Link -->
                                @if ($product_items->onFirstPage())
                                    <li class="page-item disabled" aria-disabled="true">
                                        <span class="page-link">Prev</span>
                                    </li>
                                @else
                                    <li class="page-item">
                                        <a class="page-link"
                                            href="{{ $product_items->appends(['search_keyword' => request('search_keyword'), 'sortBy' => request('sortBy'), 'category' => request('category')])->previousPageUrl() }}"
                                            rel="prev">Prev</a>
                                    </li>
                                @endif

                                <!-- Pagination Elements -->
                                @php
                                    $lastPage = $product_items->lastPage();
                                    $currentPage = $product_items->currentPage();
                                @endphp

                                @foreach ($product_items->appends(['search_keyword' => request('search_keyword'), 'sortBy' => request('sortBy'), 'category' => request('category')])->getUrlRange(1, $lastPage) as $page => $url)
                                    @if (
                                        $page === 1 ||
                                            $page === $lastPage ||
                                            abs($page - $currentPage) <= 1 ||
                                            (abs($page - $currentPage) <= 2 && ($page !== 2 && $page !== $lastPage - 1)))
                                        <li class="page-item {{ $page == $currentPage ? 'active' : '' }}">
                                            <a class="page-link" href="{{ $url }}">{{ $page }}</a>
                                        </li>
                                    @elseif ($page === 2 && $currentPage > 3)
                                        <!-- Display ellipsis after 1 if current page is greater than 4 -->
                                        <li class="page-item disabled"><span class="page-link">...</span></li>
                                    @elseif ($page === $lastPage - 1 && $currentPage < $lastPage - 2)
                                        <!-- Display ellipsis before last page if current page is lesser than (lastPage - 3) -->
                                        <li class="page-item disabled"><span class="page-link">...</span></li>
                                    @endif
                                @endforeach



                                <!-- Next Page Link -->
                                @if ($product_items->hasMorePages())
                                    <li class="page-item">
                                        <a class="page-link"
                                            href="{{ $product_items->appends(['search_keyword' => request('search_keyword'), 'sortBy' => request('sortBy'), 'category' => request('category')])->nextPageUrl() }}"
                                            rel="next">Next</a>
                                    </li>
                                @else
                                    <li class="page-item disabled" aria-disabled="true">
                                        <span class="page-link">Next</span>
                                    </li>
                                @endif
                            </ul>
                        </div>

                    </div>
                    {{-- Product Listing End --}}
                @endif
            </div>
        </div>
    </div>


    @include('domewing.partials.modal')
@endsection

@section('scripts')
    <script>
        function changeSortBy(selectedItem) {
            var selectedText = selectedItem.textContent;
            document.getElementById("lblSortBy").innerHTML = selectedText;

            updateFilters();
        }

        function changeCategory(selectedItem) {
            var selectedText = selectedItem.textContent;
            document.getElementById("lblCategory").innerHTML = selectedText;

            updateFilters();
        }

        let baseUrl = `{{ route('domewing.search') }}?search_keyword={{ request('search_keyword') }}`;

        function updateFilters() {
            // Gather all selected filter values (example: selectedSortBy, selectedCategory, etc.)
            let selectedSortBy = document.getElementById('lblSortBy').innerText;
            let selectedCategory = document.getElementById('lblCategory').innerText;

            // Create an object to store filter parameters and their corresponding values
            let filters = {
                sortBy: selectedSortBy,
                category: selectedCategory,
                // Add more filters here as needed
            };

            // Build the URL dynamically based on selected filter values
            let updatedUrl = baseUrl;
            Object.keys(filters).forEach(key => {
                if (filters[key]) {
                    updatedUrl += `&${key}=${encodeURIComponent(filters[key])}`;
                }
            });

            // Update the location with the new URL
            window.location.href = updatedUrl;
        }
    </script>
@endsection
