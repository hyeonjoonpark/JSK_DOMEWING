@extends('partner.layouts.main')
@section('title')
    오픈마켓 연동 계정 관리
@endsection
@section('subtitle')
    <p>연동된 오픈마켓 계정들을 관리하는 페이지입니다.</p>
@endsection
@section('content')
    <div class="row g-gs">
        <div class="col-6">
            <div class="card card-bordered">
                <div class="card-inner">
                    <h6 class="title">쿠팡</h6>
                    <p>연동된 쿠팡 계정 리스트</p>
                    @foreach ($coupangAccounts as $item)
                        <div class="form-group">
                            <label class="form-label">{{ $item->name }}</label>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
@endsection
@section('scripts')
    <script></script>
@endsection
