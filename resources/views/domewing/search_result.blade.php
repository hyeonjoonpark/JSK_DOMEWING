@extends('domewing.layouts.main')
@section('content')
    <div style="background: var(--thin-blue); height:500px;">
        <div class="p-5">
            <h4 style="color: var(--dark-blue);">Search Result for</h4>
            <div class="d-flex">
                <h3 class="m-0 mt-auto pe-1" style="color: var(--dark-blue);">Searched Content / Category Name</h3>
                <h5 class="my-auto" style="color: var(--dark-blue);">(531)</h5>
            </div>
            <div class="pt-5">

            </div>
        </div>
    </div>


    @include('domewing.partials.modal')
@endsection

@section('scripts')
    <script></script>
@endsection
