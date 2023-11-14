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
                    <div class="form-group">
                        <label for="" class="form-label">마진율</label>
                        <div class="d-flex text-nowrap">
                            <div class="form-control-wrap w-100">
                                <div class="form-text-hint">
                                    <span class="overline-title">%</span>
                                </div>
                                <input type="text" class="form-control" id="marginRate" value="{{ $marginRate }}"
                                    placeholder="마진율(%)를 기입해주세요.">
                            </div>
                            <button class="btn btn-primary" onclick="changeMarginRate();">변경</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('scripts')
    <script>
        var csrfToken = document.head.querySelector('meta[name="csrf-token"]').content;

        function changeMarginRate() {
            const marginRate = $('#marginRate').val();
            $.ajax({
                url: "/api/account-setting/margin-rate",
                type: "POST",
                dataType: "JSON",
                data: {
                    marginRate: marginRate,
                    remember_token: '{{ Auth::user()->remember_token }}'
                },
                success: function(response) {
                    if (response.status == 1) {
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
                    console.log(response);
                }
            });
        }
    </script>
@endsection
