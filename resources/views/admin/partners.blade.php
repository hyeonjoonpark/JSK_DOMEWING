@extends('layouts.main')
@section('style')
    <?php
    $profileColors = ['bg-dark', 'bg-warning', 'bg-success', 'bg-info', 'bg-danger', 'bg-purple', 'bg-dim-primary'];
    $emailVerifiedIcons = ['text-warning ni ni-alert-circle', 'text-success ni ni-check-circle'];
    $isActiveColors = ['ACTIVE' => 'success', 'PENDING' => 'warning', 'INACTIVE' => 'danger'];
    ?>
@endsection
@section('title')
    셀윙 파트너스
@endsection
@section('subtitle')
    <p>셀윙 파트너스 회원들을 관리하는 페이지입니다.</p>
@endsection
@section('content')
    <div class="row g-gs">
        <div class="col">
            <div class="card card-bordered">
                <div class="card-inner table-responsive">
                    <table class="datatable-init nk-tb-list nk-tb-ulist" data-auto-responsive="false" data-order="false">
                        <thead>
                            <tr class="nk-tb-item nk-tb-head">
                                <th class="nk-tb-col"><span class="sub-text">파트너스</span></th>
                                <th class="nk-tb-col"><span class="sub-text">사업자 정보</span></th>
                                <th class="nk-tb-col"><span class="sub-text">클래스</span></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($partners as $partner)
                                <tr class="nk-tb-item">
                                    <td class="nk-tb-col">
                                        <div class="user-card">
                                            <div class="user-avatar <?php echo $profileColors[array_rand($profileColors)]; ?> d-none d-sm-flex">
                                                <span>{{ mb_substr($partner->name, -2, 2, 'UTF-8') }}</span>
                                            </div>
                                            <div class="user-info">
                                                <span class="tb-lead">{{ $partner->name }}</span>
                                                <div class="d-flex align-items-center">
                                                    <span class="me-1">{{ $partner->email }}</span>
                                                    <ul class="list-status">
                                                        <li>
                                                            <em
                                                                class="icon <?= is_null($partner->email_verified_at) ? $emailVerifiedIcons[0] : $emailVerifiedIcons[1] ?>"></em>
                                                        </li>
                                                    </ul>
                                                </div>
                                                <span>{{ $partner->phone }}</span><br>
                                                <span>{{ $partner->created_at }}</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="nk-tb-col">
                                        <span class="tb-amount">{{ $partner->business_name }}</span>
                                        <span class="tb-amount">{{ $partner->business_number }}</span>
                                        <span class="tb-amount">{{ $partner->business_address }}</span>
                                        <a href="{{ asset('images/business-license/' . $partner->business_image) }}"
                                            target="_blank">사업자 등록증</a>
                                    </td>
                                    <td class="nk-tb-col">
                                        <select class="form-control js-select2"
                                            onchange="updatePartnerActive(this.value, '{{ $partner->token }}');">
                                            <option value="ACTIVE"
                                                {{ $partner->is_active === 'ACTIVE' ? 'selected' : '' }}>
                                                ACTIVE
                                            </option>
                                            <option value="INACTIVE"
                                                {{ $partner->is_active === 'INACTIVE' ? 'selected' : '' }}>
                                                INACTIVE
                                            </option>
                                        </select>
                                        <select class="form-control js-select2"
                                            onchange="setDuration('{{ $partner->token }}', this.value);">
                                            @foreach ($partnerClasses as $partnerClass)
                                                <option value="{{ $partnerClass->id }}"
                                                    {{ $partner->partner_class_id === $partnerClass->id ? 'selected' : '' }}>
                                                    {{ $partnerClass->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <span>만료: {{ $partner->expired_at }}</span>
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
        function updatePartnerActive(isActive, token) {
            $.ajax({
                url: '/api/partner/update-is-active',
                type: 'POST',
                dataType: 'JSON',
                data: {
                    isActive,
                    token,
                    rememberToken
                },
                success: function(response) {
                    const status = response.status;
                    let icon;
                    if (status === true) {
                        icon = 'success';
                    } else {
                        icon = 'error';
                    }
                    swalWithReload(response.return, icon);
                },
                error: AjaxErrorHandling
            });
        }

        function setDuration(token, partnerClassId) {
            $('#expiredAtBtn').off('click').on('click', function() {
                const expiredAt = $("#expiredAt").val();
                updatePartnerType(token, partnerClassId, expiredAt);
            });
            $("#expiredAtModal").modal("show");
        }

        function updatePartnerType(token, partnerClassId, expiredAt) {
            popupLoader(1, '파트너의 회원 등급을 업데이트 중입니다.');
            $.ajax({
                url: "/api/partner/update-type",
                type: "POST",
                dataType: "JSON",
                data: {
                    token,
                    partnerClassId,
                    expiredAt,
                    rememberToken
                },
                success: function(response) {
                    closePopup();
                    const status = response.status;
                    const data = response.return;
                    if (status === true) {
                        swalWithReload(data, 'success');
                    } else {
                        swalWithReload(data, 'error');
                    }
                },
                error: AjaxErrorHandling
            });
        }
    </script>
@endsection
