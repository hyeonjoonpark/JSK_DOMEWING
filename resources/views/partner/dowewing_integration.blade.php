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
                        <button class="btn btn-primary" onclick="addAccount();">추가하기</button>
                    </div>

                </div>
            </div>
        </div>

    </div>
@endsection
@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const apiToken = "{{ $apiToken }}";

            function addAccount() {
                const email = document.getElementById('email').value;
                const password = document.getElementById('password').value;
                const url = 'https://domewing.com/api/sync';
                const data = {
                    email: email,
                    password: password,
                    apiToken: apiToken
                };
                fetch(url, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify(data)
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === true) {
                            alert(data.message);
                            console.log('연동된 데이터:', data.data);
                        } else {
                            alert(data.message);
                            console.error('오류 내용:', data.error);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('계정 연동 중 오류가 발생하였습니다.');
                    });
            }
            document.querySelector('.btn-primary').addEventListener('click', addAccount);
        });
    </script>
@endsection
