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
                    <div class="form-group">
                        <label class="form-label">B2B 업체 리스트</label>
                        <div>
                            @foreach ($b2bs as $b2B)
                                <a href="{{ $b2B->vendor_href }}" target="_blank">{{ $b2B->name }}</a> /
                            @endforeach
                        </div>
                        <button class="btn btn-primary" onclick="initNaviwing('b2b');">B2B 네비윙</button>
                    </div>
                    <div class="form-group">
                        <label class="form-label">원청사 리스트</label>
                        <div>
                            @foreach ($vendors as $vendor)
                                <a href="{{ $vendor->vendor_href }}" target="_blank">{{ $vendor->name }}</a> /
                            @endforeach
                        </div>
                        <button class="btn btn-primary" onclick="initNaviwing('seller');">원청사 네비윙</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('scripts')
    <script>
        var sellers = @json($vendors);
        var b2bs = @json($b2bs)

        function initNaviwing(type) {
            let hrefs = sellers;
            if (type === 'b2b') {
                hrefs = b2bs;
            }
            for (var href of hrefs) {
                window.open(href.vendor_href, '_blank');
            }
        }
    </script>
@endsection
