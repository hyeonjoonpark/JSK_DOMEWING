@extends('domewing.layouts.main')
@section('content')
    @include('domewing.partials.user_profile_short')

    <div class="p-lg-5 p-2" style="background-color: var(--pure-white);">
        <div class="row m-0">
            <div class="col-md-4 col-12">
                @include('domewing.partials.user_navbar')
            </div>
            <div class="col-md-8 col-12">

                {{-- <h5 style="color: var(--dark-blue);">Filter</h5>

                <div style="border-top: 2px solid var(--dark-blue)"></div> --}}

                <form action="{{ route('search.wishlist') }}" method="GET">
                    <div class="d-flex justify-content-between">
                        <input type="text"
                            style="height: 45px; background-color: var(--thin-blue); color: var(--dark-blue)"
                            class="form-control fs-18px" id="search" name="search_keyword" placeholder="Search Keyword"
                            value="{{ request('search_keyword') }}">
                        <button type="submit" class="btn">
                            <img src={{ asset('media\Asset_Nav_Search.svg') }} class="icon-size">
                        </button>
                    </div>
                </form>

                @if (count($wishlist) < 1)
                    <h5 class="text-center py-5" style="color: var(--dark-blue);">
                        No Item Found</h5>
                @else
                    <div class="row">
                        @foreach ($wishlist as $item)
                            <div class="col-xxl-3 col-xl-4 col-lg-6 col-md-12 pt-4">
                                <a href="/domewing/product/{{ $item->id }}">
                                    <img src="{{ $item->image }}" class="img-fluid tracking-img mx-auto" />
                                    <h5 class="text-truncate py-2" style="color: var(--dark-blue);">
                                        {{ $item->productName }}</h5>
                                </a>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection

@section('scripts')
@endsection
