@extends('layouts.main')
@section('title')
    Content Management System - Dashboard
@endsection
@section('subtitle')
    <p>
        Manage Customer's Webpage Content</p>
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
                                                <a
                                                    href="/admin/cms_dashboard/content_management_system/{{ $domain->domain_id }}">
                                                    <span class="tb-lead">{{ $domain->company }}</span>
                                                </a>
                                                <span>domewing/{{ $domain->domain_name }}</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="nk-tb-col tb-col-md">
                                        <div class="user-info">
                                            <span class="tb-lead">{{ $domain->formatted_created_at }}</span>
                                            @if ($domain->updated_at)
                                                <span>Updated: {{ $domain->formatted_updated_at }}</span>
                                            @endif
                                        </div>

                                    </td>
                                    <td class="nk-tb-col">
                                        <span class="tb-status text-success">{{ $domain->is_active }}</span>
                                    </td>
                                    <td class="nk-tb-col nk-tb-col-tools">
                                        <ul class="nk-tb-actions gx-1">
                                            <li>
                                                <div class="drodown">
                                                    <a class="dropdown-toggle btn btn-icon btn-trigger"
                                                        data-bs-toggle="dropdown"><em class="icon ni ni-more-h"></em></a>
                                                    <div class="dropdown-menu dropdown-menu-end">
                                                        <ul class="link-list-opt no-bdr">
                                                            <li>
                                                                <a onclick="editDomainInit({{ $domain->domain_id }});">
                                                                    <em
                                                                        class="icon fa-solid fa-pen-to-square"></em><span>Edit</span>
                                                                </a>
                                                            </li>
                                                            <li>
                                                                <a onclick="removeDomainInit({{ $domain->domain_id }});">
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

    <div class="modal fade" id="modalEdit">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Domain</h5>
                    <a class="close" data-bs-dismiss="modal" aria-label="Close">
                        <em class="icon ni ni-cross"></em>
                    </a>
                </div>
                <div class="modal-body">
                    <form class="form-validate is-alter">
                        <div class="form-group">
                            <label class="form-label" for="edit-domain-name">Domain Name</label>
                            <div class="form-control-wrap">
                                <div class="input-group input-group-lg">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text" id="basic-addon3">domewing/</span>
                                    </div>
                                    <input type="text" class="form-control" aria-label="Large" id="edit-domain-name"
                                        onchange="domainNameFormat(this);" required>
                                </div>
                            </div>
                        </div>
                        <div class="form-group text-center">
                            <button id="confirmEdit" class="btn btn-lg btn-primary">Save Changes</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalRemove">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <a class="close" data-bs-dismiss="modal"><em class="icon ni ni-cross"></em></a>
                <div class="modal-body modal-body-lg text-center">
                    <div class="nk-modal">
                        <em class="nk-modal-icon icon icon-circle icon-circle-xxl ni ni-property-remove bg-danger"></em>
                        <h4 class="nk-modal-title">Are you sure to remove this domain?</h4>
                    </div>
                    <div class="text-center pt-5 d-flex justify-content-around">
                        <button data-bs-dismiss="modal" class="btn btn-lg btn-primary">Cancel</button>
                        <button id="confirmRemove" class="btn btn-lg btn-danger">Confirm</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('scripts')
    <script>
        //edit domain
        function editDomainInit(domainId) {
            $("#confirmEdit").attr("onclick", 'editDomain(' + domainId + ')');
            $.ajax({
                url: '/api/admin/get-domain',
                type: 'get',
                dataType: 'json',
                data: {
                    domainId: domainId
                },
                success: function(response) {
                    const status = parseInt(response.status);
                    if (status == 1) {
                        $('#edit-domain-name').val(response.domainName);
                        $('#modalEdit').modal('show');
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Opps',
                            text: response.return
                        });
                    }
                },
                error: function(response) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Opps',
                        text: response
                    });
                }
            });
        }

        function editDomain(domainId) {
            var domainName = $('#edit-domain-name').val();
            var remember_token = "{{ Auth::user()->remember_token }}";

            if (domainName != "") {
                $.ajax({
                    url: '/api/admin/edit-domain',
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        domainName: domainName,
                        domainId: domainId,
                        remember_token: remember_token,
                    },
                    success: function(response) {
                        const status = parseInt(response.status);
                        if (status == 1) {
                            $('.modal').modal('hide');
                            Swal.fire({
                                icon: 'success',
                                title: response.return,
                            }).then((result) => {
                                location.reload();
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Opps',
                                text: response.return,
                            });
                        }
                    },
                    error: function(error) {
                        // Handle error response
                        Swal.fire({
                            icon: 'error',
                            title: 'Unable to process',
                            text: error
                        });
                    }
                });
            }


        }

        //remove domain
        function removeDomainInit(domainId) {
            $("#confirmRemove").attr("onclick", 'removeDomain(' + domainId + ')');
            $('#modalRemove').modal('show');
        }

        function removeDomain(domainId) {
            var remember_token = "{{ Auth::user()->remember_token }}";

            $.ajax({
                url: '/api/admin/remove-domain',
                type: 'post',
                dataType: 'json',
                data: {
                    domainId: domainId,
                    remember_token: remember_token,
                },
                success: function(response) {
                    const status = parseInt(response.status);
                    $('.modal').modal('hide');
                    if (status == 1) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Domain Remove Successfully',
                        }).then((result) => {
                            location.reload();
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Unable to process',
                            text: response.return
                        });
                    }
                },
                error: function(response) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Unable to process',
                        text: response
                    });
                }
            });
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
