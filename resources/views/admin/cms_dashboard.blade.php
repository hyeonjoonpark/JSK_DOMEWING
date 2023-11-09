@extends('layouts.main')
@section('title')
    Content Management System - Dashboard
@endsection
@section('subtitle')
    <p>Manage Customer's Webpage Content</p>
@endsection
@section('content')
    <div class="row g-gs">
        <div class="col">
            <div class="card card-bordered preview">
                <div class="card-inner">
                    <div class="nk-block-between pb-2">
                        <div class="nk-block-head-content">
                            <h5 class="card-title">Domains</h5>
                            <h6 class="card-subtitle mb-2">This is a list of registered domains.</h6>
                        </div>
                        <div class="nk-block-head-content">
                            <button type="button" class="btn btn-white btn-dim btn-outline-primary" data-bs-toggle="modal"
                                data-bs-target="#modalForm">
                                <em class="icon fa-solid fa-add"></em><span>Register New</span>
                            </button>
                        </div>
                    </div>

                    <table class="datatable-init nk-tb-list nk-tb-ulist" data-auto-responsive="false">
                        <thead>
                            <tr class="nk-tb-item nk-tb-head">
                                <th class="nk-tb-col"><span class="sub-text">Domains Owner</span></th>
                                <th class="nk-tb-col tb-col-md"><span class="sub-text">Date Added</span></th>
                                <th class="nk-tb-col"><span class="sub-text">Status</span></th>
                                <th class="nk-tb-col nk-tb-col-tools text-end">
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($domains as $domain)
                                <tr class="nk-tb-item">
                                    <td class="nk-tb-col">
                                        <div class="user-card">
                                            <div class="user-info">
                                                <span class="tb-lead">{{ $domain->company_name }}<span
                                                        class="dot dot-warning d-md-none ms-1"></span></span>
                                                <span>domewing/{{ $domain->domain_name }}</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="nk-tb-col tb-col-md">
                                        <span>{{ $domain->formatted_created_at }}</span>
                                    </td>
                                    <td class="nk-tb-col">
                                        <span class="tb-status text-success">{{ $domain->is_active }}</span>
                                    </td>
                                    <td class="nk-tb-col nk-tb-col-tools">
                                        <ul class="nk-tb-actions gx-1">
                                            <li>
                                                <div class="drodown">
                                                    <a href="#" class="dropdown-toggle btn btn-icon btn-trigger"
                                                        data-bs-toggle="dropdown"><em class="icon ni ni-more-h"></em></a>
                                                    <div class="dropdown-menu dropdown-menu-end">
                                                        <ul class="link-list-opt no-bdr">
                                                            <li>
                                                                <a href="#">
                                                                    <em
                                                                        class="icon fa-solid fa-pen-to-square"></em><span>Edit</span>
                                                                </a>
                                                            </li>
                                                            <li>
                                                                <a href="#">
                                                                    <em
                                                                        class="icon fa-solid fa-user-xmark"></em><span>Remove</span>
                                                                </a>
                                                            </li>
                                                        </ul>
                                                    </div>
                                                </div>
                                            </li>
                                        </ul>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalForm">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Register New Domain</h5>
                    <a href="#" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <em class="icon ni ni-cross"></em>
                    </a>
                </div>
                <div class="modal-body">
                    <form action="#" class="form-validate is-alter">
                        <div class="form-group input-group-lg">
                            <label class="form-label" for="company-name">Company Name</label>
                            <div class="form-control-wrap">
                                <div class="input-group input-group-lg">
                                    <input type="text" class="form-control" id="company-name"
                                        onchange="companyNameFormat(this);" required>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="domain-name">Domain Name</label>
                            <div class="form-control-wrap">
                                <div class="input-group input-group-lg">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text" id="basic-addon3">domewing/</span>
                                    </div>
                                    <input type="text" class="form-control" aria-label="Large" id="domain-name"
                                        onchange="domainNameFormat(this);" required>
                                </div>
                            </div>
                        </div>
                        <div class="form-group text-center">
                            <button type="submit" class="btn btn-lg btn-primary">Confirm Register</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" tabindex="-1" id="modalAlert">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <a href="#" class="close" data-bs-dismiss="modal"><em class="icon ni ni-cross"></em></a>
                <div class="modal-body modal-body-lg text-center">
                    <div class="nk-modal">
                        <em class="nk-modal-icon icon icon-circle icon-circle-xxl ni ni-check bg-success"></em>
                        <h4 class="nk-modal-title"></h4>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('scripts')
    <script>
        $(document).ready(function() {
            $('#modalForm form').submit(function(e) {
                var csrfToken = $('meta[name="csrf-token"]').attr('content');
                e.preventDefault();

                var companyName = $('#company-name').val();
                var domainName = $('#domain-name').val();

                $.ajax({
                    url: '/admin/register-domain',
                    type: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken
                    },
                    data: {
                        companyName: companyName,
                        domainName: domainName
                    },
                    success: function(response) {

                        if (response.status == 1) {
                            // Handle success response
                            console.log(response.message);
                            $('#modalAlert .nk-modal-icon')
                                .removeClass()
                                .addClass(
                                    'nk-modal-icon icon icon-circle icon-circle-xxl ni ni-check bg-success'
                                );
                            $('#modalAlert .nk-modal-title').text(response.message);
                            $('#modalAlert').modal('show');
                            // Close the modal (if needed)
                            $('#modalForm').modal('hide');
                            location.reload();
                        } else {
                            console.log(response.message);
                            $('#modalAlert .nk-modal-icon')
                                .removeClass()
                                .addClass(
                                    'nk-modal-icon icon icon-circle icon-circle-xxl ni ni-cross bg-danger'
                                );
                            $('#modalAlert .nk-modal-title').text(response.message);
                            $('#modalAlert').modal('show');
                        }
                    },
                    error: function(error) {
                        // Handle error response
                        console.error(error);
                    }
                });
            });
        });

        function companyNameFormat(inputElement) {
            // 사용자가 입력한 값을 가져옴
            var inputValue = inputElement.value;

            // 허용할 문자의 아스키 코드 범위를 정의
            var allowedRanges = [
                [48, 57], // 숫자 (0-9)
                [65, 90], // 대문자 영어 (A-Z)
                [97, 122], // 소문자 영어 (a-z)
                [44032, 55203], // 한글 범위 (가-힣)
                [32, 32], // 공백
            ];

            // 입력값을 필터링하여 허용된 문자로 대체
            var filteredValue = "";
            for (var i = 0; i < inputValue.length; i++) {
                var charCode = inputValue.charCodeAt(i);
                var isAllowed = false;

                // 허용된 범위에 속하는지 확인
                for (var j = 0; j < allowedRanges.length; j++) {
                    var range = allowedRanges[j];
                    if (charCode >= range[0] && charCode <= range[1]) {
                        isAllowed = true;
                        break;
                    }
                }

                if (isAllowed) {
                    filteredValue += inputValue[i];
                }
            }

            // 입력 필드에 필터링된 값을 설정
            inputElement.value = filteredValue;
        }

        function domainNameFormat(inputElement) {
            // 사용자가 입력한 값을 가져옴
            var inputValue = inputElement.value;

            // 허용할 문자의 아스키 코드 범위를 정의
            var allowedRanges = [
                [48, 57], // 숫자 (0-9)
                [65, 90], // 대문자 영어 (A-Z)
                [97, 122], // 소문자 영어 (a-z)
                [44032, 55203], // 한글 범위 (가-힣)
                [95, 95], // Underscore (_)
                [45, 45] // Dash (-)
            ];

            // 입력값을 필터링하여 허용된 문자로 대체
            var filteredValue = "";
            for (var i = 0; i < inputValue.length; i++) {
                var charCode = inputValue.charCodeAt(i);
                var isAllowed = false;

                // 허용된 범위에 속하는지 확인
                for (var j = 0; j < allowedRanges.length; j++) {
                    var range = allowedRanges[j];
                    if (charCode >= range[0] && charCode <= range[1]) {
                        isAllowed = true;
                        break;
                    }
                }

                if (isAllowed) {
                    filteredValue += inputValue[i];
                }
            }

            // 입력 필드에 필터링된 값을 설정
            inputElement.value = filteredValue;
        }
    </script>
@endsection
