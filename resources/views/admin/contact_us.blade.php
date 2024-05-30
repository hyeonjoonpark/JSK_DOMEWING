@extends('layouts.main')
@section('title')
    문희윙
@endsection
@section('subtitle')
    <p>
        고객들의 소중한 호박고구마... 아니, 문의 내용을 관리하는 페이지입니다.
    </p>
@endsection
@section('content')
    <div class="row g-gs">
        <div class="col">
            <div class="card card-bordered">
                <div class="card-inner">
                    <h6 class="title-card">문의 내역</h6>
                    <p>총 {{ number_format(count($contacts)) }}개의 문희가 조회되었습니다.</p>
                    <table class="datatable-init nk-tb-list nk-tb-ulist" data-auto-responsive="false" data-order="false">
                        <thead>
                            <tr class="nk-tb-item nk-tb-head">
                                <th class="nk-tb-col"><span class="sub-text">번호</span></th>
                                <th class="nk-tb-col"><span class="sub-text">이름</span></th>
                                <th class="nk-tb-col"><span class="sub-text">이메일</span></th>
                                <th class="nk-tb-col"><span class="sub-text">연락처</span></th>
                                <th class="nk-tb-col"><span class="sub-text">상태</span></th>
                                <th class="nk-tb-col"><span class="sub-text">문희 내용</span></th>
                                <th class="nk-tb-col"><span class="sub-text">접수/답변 일시</span></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($contacts as $i => $contact)
                                <tr class="nk-tb-item">
                                    <td class="nk-tb-col">{{ $i + 1 }}</td>
                                    <td class="nk-tb-col">
                                        <div class="user-card">
                                            <div class="user-avatar d-none d-sm-flex">
                                                <img class="w-100 h-100" src="{{ asset('assets/images/munhee.jpg') }}"
                                                    alt="문희">
                                            </div>
                                            <div class="user-info">
                                                <span class="tb-lead">{{ $contact->name }}</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="nk-tb-col">
                                        <span class="tb-amount">{{ $contact->email }}</span>
                                    </td>
                                    <td class="nk-tb-col">
                                        <span class="tb-amount">{{ $contact->phone_number }}</span>
                                    </td>
                                    <td class="nk-tb-col">
                                        <span class="tb-amount">
                                            @switch($contact->status)
                                                @case('PENDING')
                                                    <span class="text-warning">대기 중</span>
                                                @break

                                                @case('ANSWERED')
                                                    <span class="text-success">답변 완료</span>
                                                @break

                                                @default
                                            @endswitch
                                        </span>
                                    </td>
                                    <td class="nk-tb-col">
                                        <a href="javascript:showContact({{ $contact->id }});">보기</a>
                                    </td>
                                    <td class="nk-tb-col">
                                        <span class="tb-amount">{{ $contact->created_at }}</span>
                                        <span class="tb-amount">{{ $contact->updated_at }}</span>
                                    </td>
                                </tr><!-- .nk-tb-item  -->
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('scripts')
    <script>
        var contacts = @json($contacts);

        function showContact(contactId) {
            const contact = contacts.find(c => c.id === contactId);
            const message = contact ? contact.message : '텅 빈 문희입니다.';
            const answer = contact.answer ? contact.answer : '';
            const html = `
            <div class="form-group text-start">
                <label class="form-label">문의 내용</label>
                <textarea class="form-control" readonly>${message}</textarea>
            </div>
            <div class="form-group text-start">
                <label class="form-label">문의 답변</label>
                <textarea class="form-control" id="answer" placeholder="문의 답변을 입력해주세요.">${answer}</textarea>
            </div>
            `;
            Swal.fire({
                html,
                showCancelButton: true,
                cancelButtonText: '취소',
                confirmButtonText: '확인'
            }).then((result) => {
                if (result.isConfirmed) {
                    const answer = $('#answer').val();
                    update(contactId, answer);
                }
            });
        }

        function update(contactId, answer) {
            popupLoader(1, '문의 내용에 대한 답변을 이메일로 전송하는 중입니다.');
            $.ajax({
                url: '/api/contact-us/update',
                type: 'POST',
                dataType: 'JSON',
                data: {
                    contactId,
                    answer,
                    rememberToken
                },
                success: ajaxSuccessHandling,
                error: AjaxErrorHandling
            });
        }
    </script>
@endsection
