@extends('domewing.layouts.main')
@section('content')
    @include('domewing.partials.user_profile_short')

    <div class="p-lg-5 p-2" style="background-color: var(--pure-white);">
        <div class="row m-0">
            <div class="col-md-4 col-12">
                @include('domewing.partials.user_navbar')
            </div>
            <div class="col-md-8 col-12">
                @foreach ($groupedOrders as $orderId => $orders)
                    <div class="card-bordered p-4 mb-4" style="background: var(--thin-blue);">
                        <div class="d-flex justify-content-between align-items-center">
                            <h6 class="text-wrap text-truncate" style="color: var(--light-blue);">
                                {{ $orders->first()->supplierName }}</h6>
                            <a href="#" onclick="showDetails({{ $orders->first()->transaction_id }})">
                                <h6 class="text-end" style="color: var(--light-blue);">
                                    More Details</h6>
                            </a>
                        </div>
                        <div class="p-1" style="border-bottom: 2px solid var(--dark-blue)"></div>
                        <div class="hstack g-gs horizontal-scrolling pt-1">
                            @foreach ($orders as $order)
                                <div>
                                    <img src="{{ $order->newImageHref }}" class="img-fluid tracking-img" />
                                    <h6 class="text-truncate py-3" style="color: var(--dark-blue); width:230px;">
                                        {{ $order->productName }}
                                    </h6>
                                </div>
                            @endforeach
                        </div>
                        <div class="p-2" style="border-bottom: 2px solid var(--cyan-blue)"></div>
                        <div class="d-flex flex-wrap justify-content-between">
                            <ul class="pricing-features fs-18px col-lg-7 col-12 pt-3" style="color: var(--dark-blue);">
                                <li>
                                    @php
                                        $grandTotal = 0;
                                        foreach ($orders as $order) {
                                            $grandTotal += $order->total_price;
                                        }
                                    @endphp
                                    <h6 class="w-50 align-self-center m-0" style="color: var(--dark-blue);">
                                        Total Payment</h6>
                                    <h6 class="w-50 align-self-center m-0" style="color: var(--dark-blue);">
                                        KRW {{ number_format($grandTotal, 2) }}
                                    </h6>
                                </li>
                                <li>
                                    <h6 class="w-50 align-self-center m-0" style="color: var(--dark-blue);">
                                        Shipping Method</h6>
                                    <h6 class="w-50 align-self-center m-0" style="color: var(--dark-blue);">
                                        Land
                                    </h6>
                                </li>
                                <li>
                                    <h6 class="w-50 align-self-center m-0" style="color: var(--dark-blue);">
                                        Ship By</h6>
                                    <h6 class="w-50 align-self-center m-0" style="color: var(--dark-blue);">
                                        01/01/2024
                                    </h6>
                                </li>
                                <li>
                                    <h6 class="w-50 align-self-center m-0" style="color: var(--dark-blue);">
                                        Receive By</h6>
                                    <h6 class="w-50 align-self-center m-0" style="color: var(--dark-blue);">
                                        06/01/2024</h6>
                                </li>
                            </ul>

                            <button class="btn mt-lg-auto justify-content-center col-lg-5 col-12 mt-3"
                                style="background: var(--pink);" onclick="notifySupplier()">
                                <h5 class="text-white p-1 text-wrap">Remind Supplier to Ship</h5>
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        function notifySupplier() {
            swal.fire({
                icon: 'success',
                title: 'Notified Supplier'
            })
        }
    </script>
@endsection
