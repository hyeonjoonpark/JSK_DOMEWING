@extends('layouts.main')
@section('title')
    대시보드
@endsection
@section('subtitle')
    <p>페이즈 2 개발 과정을 위한 소통 공간입니다</p>
@endsection
@section('content')
    <iframe class="w-100" style="aspect-ratio:1.5;"
        src="https://docs.google.com/spreadsheets/d/e/2PACX-1vTfct0NdiYThD-o4OzzT9eAO1BaDzr1YYmecIYP6V0XHBEBeE3-sPNGFbwmGSq5FD8fZpk_hMh9BdHn/pubhtml?gid=1115838130&single=true"></iframe>
    <div class='row mt-5'>
        <div class="col">
            <div class="card card-bordered">
                <div class="card-inner">
                    <div class="card-title">
                        <h5 class="card-title">개발 소통 창구</h5>
                        <h6 class="card-subtitle mb-2">메시지 입력을 통해 개발 일지 건의사항을 남깁니다.</h6>
                        @foreach ($posts as $post)
                            <div class="card card-bordered mb-2">
                                <div class="card-inner">
                                    <div class="row">
                                        <div class="col">
                                            <p class="card-text">{!! $post->content !!}</p>
                                        </div>
                                        <div class="col-auto">
                                            <p class="card-text">{{ $post->created_at }}</p>
                                            <a class="btn btn-outline-danger"
                                                onclick="deletePost({{ $post->id }});">삭제</a>
                                            @if ($post->is_confirmed === 'Y')
                                                <button class="btn btn-secondary" disabled>확인</button>
                                            @endif
                                            @if ($post->is_confirmed !== 'Y')
                                                <button class="btn btn-success"
                                                    onclick="setConfirmed({{ $post->id }});">확인</button>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                        <form class="d-flex align-items-center mt-5" action="admin/submit-post" method="post">
                            @csrf
                            <textarea class="form-control" name="feedbackContent" id="feedbackContent" cols="30" rows="10"></textarea>
                            <div class="col-auto">
                                <button class="btn btn-primary">등록</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('scripts')
    <script>
        function deletePost(id) {
            $.ajax({
                url: "/api/delete-post",
                type: 'post',
                data: {
                    id: id
                },
                success: function(response) {
                    location.reload();
                },
                error: function(response) {
                    console.log(response);
                    location.reload();
                }
            });
        }

        function setConfirmed(id) {
            $.ajax({
                url: '/api/set-post-confirmed',
                type: 'post',
                data: {
                    id: id
                },
                success: function(response) {
                    location.reload();
                },
                error: function(response) {
                    console.log(response);
                    location.reload();
                }
            });
        }
    </script>
@endsection
