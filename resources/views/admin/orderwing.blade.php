@extends('layouts.main')
@section('title')
    오더윙
@endsection
@section('subtitle')
    <p>오더윙을 통해 B2B 업체로부터의 주문 내용을 정리하고 정산합니다. 또한, 송장 번호 자동 추출 기능이 포함되어 있어 효율적인 주문 관리가 가능합니다.</p>
@endsection
@section('content')
    <div class="row g-gs">
        <div class="col">
            <button class="btn btn-primary" onclick="initOrderwing();">오더윙 가동</button>
        </div>
    </div>
@endsection
@section('scripts')
    <script>
        var rememberToken = '{{ Auth::user()->remember_token }}';

        function initOrderwing() {
            $.ajax({
                url: '/api/orderwing',
                type: 'POST',
                dataType: 'JSON',
                data: {
                    rememberToken
                },
                success: function(response) {
                    console.log(response);
                },
                error: function(response) {
                    console.log(response);
                }
            });
        }
    </script>
@endsection
