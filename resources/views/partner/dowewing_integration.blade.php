@extends('partner.layouts.main')
@section('title')
    도매윙 계정 연동
@endsection
@section('subtitle')
    <p>도매윙 계정 연동을 추가하는 페이지입니다.</p>
@endsection
@section('content')
    <div class="row g-gs">
        <div class="col-12 col-lg-6">
            <div class="card card-bordered">
                <div class="card-inner">
                    <h6 class="title">도매윙 계정 연동</h6>
                    <p>
                        도매윙 계정을 입력하여 주세요.
                    </p>
                    <div class="form-group">
                        <label for="email" class="form-label">도매윙 로그인 이메일</label>
                        <input class="form-control" type="text" id="email" placeholder="도매윙 로그인 이메일을 입력해주세요."
                            aria-describedby="emailHelp">
                        <small id="emailHelp" class="form-text text-muted">사용자의 도매윙 이메일을 입력하세요.</small>
                    </div>
                    <div class="form-group">
                        <label for="password" class="form-label">도매윙 로그인 비밀번호</label>
                        <input class="form-control" type="password" id="password" placeholder="도매윙 로그인 비밀번호를 입력해주세요."
                            aria-describedby="passwordHelp">
                        <small id="passwordHelp" class="form-text text-muted">로그인 비밀번호는 안전하게 보관됩니다.</small>
                    </div>
                    <div class="text-center">
                        <button class="btn btn-primary" onclick="addAccount();">
                            @if ($isExistPartnerAndDomewing)
                                업데이트하기
                            @else
                                신규 추가
                            @endif
                        </button>
                    </div>

                </div>
            </div>
        </div>

    </div>
@endsection
@section('scripts')
    <script>
        $(document).ready(function() {
            const isExistPartnerAndDomewing = '{{ $isExistPartnerAndDomewing }}';
            if (!isExistPartnerAndDomewing) {
                Swal.fire({
                    icon: 'warning',
                    title: '도매윙 계정을 연동해주세요'
                });
            }
        });

        function addAccount() {
            const apiToken = "{{ $apiToken }}";
            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;
            const url = 'https://domewing.com/api/sync';
            const data = {
                email,
                password,
                apiToken
            };
            $.ajax({
                url,
                type: 'POST',
                dataType: 'JSON',
                data,
                success: ajaxSuccessHandling,
                error: AjaxErrorHandling
            });
        }
    </script>
@endsection
