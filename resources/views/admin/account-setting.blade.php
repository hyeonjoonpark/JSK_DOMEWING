@extends('layouts.main')
@section('title')
    환경설정
@endsection
@section('subtitle')
    <p>도매윙 엔진의 설정을 변경합니다.</p>
@endsection
@section('content')
    <div class="row g-gs">
        <div class="col-6">
            <div class="card card-bordered">
                <div class="card-inner">
                    @foreach ($marginRates as $marginRate)
                        <div class="form-group">
                            <label for="" class="form-label">{{ $marginRate->name }}</label>
                            <div class="d-flex text-nowrap">
                                <div class="form-control-wrap w-100">
                                    <div class="form-text-hint">
                                        <span class="overline-title">%</span>
                                    </div>
                                    <input type="number" class="form-control" id="marginRate{{ $marginRate->mrID }}"
                                        value="{{ $marginRate->rate }}" placeholder="마진율(%)를 기입해주세요.">
                                </div>
                                <button class="btn btn-primary"
                                    onclick="changeMarginRate({{ $marginRate->mrID }});">변경</button>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
@endsection
@section('scripts')
    <script>
        var csrfToken = document.head.querySelector('meta[name="csrf-token"]').content;

        function changeMarginRate(mrID) {
            $('.btn').prop('disabled', true);
            const marginRate = $('#marginRate' + mrID).val();
            $.ajax({
                url: "/api/account-setting/margin-rate",
                type: "POST",
                dataType: "JSON",
                data: {
                    marginRate: marginRate,
                    remember_token: '{{ Auth::user()->remember_token }}',
                    mrID: mrID
                },
                success: function(response) {
                    $('.btn').prop('disabled', false);
                    if (response.status) {
                        Swal.fire({
                            icon: "success",
                            title: "진행 성공",
                            text: response.return
                        }).then((result) => {
                            location.reload();
                        });
                    } else {
                        Swal.fire({
                            icon: "error",
                            title: "진행 실패",
                            text: response.return
                        });
                    }
                },
                error: function(response) {
                    $('.btn').prop('disabled', false);
                    console.log(response);
                }
            });
        }
    </script>
@endsection
