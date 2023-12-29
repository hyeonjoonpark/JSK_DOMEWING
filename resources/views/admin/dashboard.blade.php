@extends('layouts.main')
@section('title')
    대시보드
@endsection
@section('subtitle')
    <p>B2B 및 도매윙 판매 실적을 위한 각종 유형의 차트를 준비 중입니다. (페이즈 3)</p>
@endsection
@section('content')
    <div class="row g-gs">
        <div class="col">
            <div class="card card-bordered">
                <div class="card-inner">
                    <h6 class="title">네비윙</h6>
                    <p>
                        네비윙 버튼을 클릭하시면 셀윙과 연동된 모든 B2B 업체의 페이지가 열립니다.
                    </p>
                    @foreach ($b2Bs as $b2B)
                        <a href="{{ $b2B->vendor_href }}" target="_blank">{{ $b2B->name }}</a> /
                    @endforeach
                    <div class="d-flex mt-3">
                        <button class="btn btn-primary" onclick="initNaviwing();">네비윙</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('scripts')
    <script>
        var b2BHrefs = @json($b2BHrefs);

        function initNaviwing() {
            for (var b2BHref of b2BHrefs) {
                window.open(b2BHref, '_blank');
            }
        }
    </script>
@endsection
