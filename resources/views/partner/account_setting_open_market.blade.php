@extends('partner.layouts.main')
@section('title')
    오픈마켓 계정 연동
@endsection
@section('subtitle')
    <p>오픈마켓 계정 연동을 추가하거나 수정하는 페이지입니다.</p>
@endsection
@section('content')
    <div class="row g-gs">
        <div class="col-12 col-lg-6">
            <div class="card card-bordered">
                <div class="card-inner">
                    <h6 class="title">쿠팡</h6>
                    <p>
                        @if ($coupangAccount === null)
                            등록된 계정 정보가 없습니다. 새로운 계정 정보를 추가해주십시오.
                        @else
                            이미 등록된 계정이 있습니다. 만료일: {{ $coupangAccount->expired_at }}
                        @endif
                    </p>
                    <div class="form-group">
                        <label for="" class="form-label">업체코드</label>
                        <input class="form-control" type="text">
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('scripts')
    <script></script>
@endsection
