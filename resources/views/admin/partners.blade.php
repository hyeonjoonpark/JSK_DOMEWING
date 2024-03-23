@extends('layouts.main')
@section('style')
    <?php
    $profileColors = ['bg-dark', 'bg-warning', 'bg-success', 'bg-info', 'bg-danger', 'bg-purple', 'bg-dim-primary'];
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
                <div class="card-inner">
                    <table class="datatable-init nk-tb-list nk-tb-ulist" data-auto-responsive="false">
                        <thead>
                            <tr class="nk-tb-item nk-tb-head">
                                <th class="nk-tb-col nk-tb-col-check">
                                    <div class="custom-control custom-control-sm custom-checkbox notext">
                                        <input type="checkbox" class="custom-control-input" id="uid">
                                        <label class="custom-control-label" for="uid"></label>
                                    </div>
                                </th>
                                <th class="nk-tb-col"><span class="sub-text">파트너스</span></th>
                                <th class="nk-tb-col tb-col-mb"><span class="sub-text">사업자 정보</span></th>
                                <th class="nk-tb-col tb-col-md"><span class="sub-text">연락처</span></th>
                                <th class="nk-tb-col tb-col-lg"><span class="sub-text">Verified</span></th>
                                <th class="nk-tb-col tb-col-lg"><span class="sub-text">Last Login</span></th>
                                <th class="nk-tb-col tb-col-md"><span class="sub-text">Status</span></th>
                                <th class="nk-tb-col nk-tb-col-tools text-end">
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($partners as $partner)
                                <tr class="nk-tb-item">
                                    <td class="nk-tb-col nk-tb-col-check">
                                        <div class="custom-control custom-control-sm custom-checkbox notext">
                                            <input type="checkbox" class="custom-control-input" id="uid1">
                                            <label class="custom-control-label" for="uid1"></label>
                                        </div>
                                    </td>
                                    <td class="nk-tb-col">
                                        <div class="user-card">
                                            <div class="user-avatar <?php echo $profileColors[array_rand($profileColors)]; ?> d-none d-sm-flex">
                                                <span>{{ mb_substr($partner->name, -2, 2, 'UTF-8') }}</span>
                                            </div>
                                            <div class="user-info">
                                                <span class="tb-lead">{{ $partner->name }}<span
                                                        class="dot dot-success d-md-none ms-1"></span></span>
                                                <span>{{ $partner->email }}</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="nk-tb-col tb-col-mb" data-order="35040.34">
                                        <span class="tb-amount">{{ $partner->business_name }}</span>
                                        <span class="tb-amount">{{ $partner->business_number }}</span>
                                        <span class="tb-amount">{{ $partner->business_address }}</span>
                                        <a href="{{ asset('images/business-license/' . $partner->business_image) }}"
                                            target="_blank">사업자 등록증</a>
                                    </td>
                                    <td class="nk-tb-col tb-col-md">
                                        <span>{{ $partner->phone }}</span>
                                    </td>
                                    <td class="nk-tb-col tb-col-lg" data-order="Email Verified - Kyc Unverified">
                                        <ul class="list-status">
                                            <li><em class="icon text-success ni ni-check-circle"></em> <span>Email</span>
                                            </li>
                                            <li><em class="icon ni ni-alert-circle"></em> <span>KYC</span></li>
                                        </ul>
                                    </td>
                                    <td class="nk-tb-col tb-col-lg">
                                        <span>05 Oct 2019</span>
                                    </td>
                                    <td class="nk-tb-col tb-col-md">
                                        <span class="tb-status text-success">Active</span>
                                    </td>
                                    <td class="nk-tb-col nk-tb-col-tools">
                                        <ul class="nk-tb-actions gx-1">
                                            <li class="nk-tb-action-hidden">
                                                <a href="#" class="btn btn-trigger btn-icon" data-bs-toggle="tooltip"
                                                    data-bs-placement="top" title="Wallet">
                                                    <em class="icon ni ni-wallet-fill"></em>
                                                </a>
                                            </li>
                                            <li class="nk-tb-action-hidden">
                                                <a href="#" class="btn btn-trigger btn-icon" data-bs-toggle="tooltip"
                                                    data-bs-placement="top" title="Send Email">
                                                    <em class="icon ni ni-mail-fill"></em>
                                                </a>
                                            </li>
                                            <li class="nk-tb-action-hidden">
                                                <a href="#" class="btn btn-trigger btn-icon" data-bs-toggle="tooltip"
                                                    data-bs-placement="top" title="Suspend">
                                                    <em class="icon ni ni-user-cross-fill"></em>
                                                </a>
                                            </li>
                                            <li>
                                                <div class="drodown">
                                                    <a href="#" class="dropdown-toggle btn btn-icon btn-trigger"
                                                        data-bs-toggle="dropdown"><em class="icon ni ni-more-h"></em></a>
                                                    <div class="dropdown-menu dropdown-menu-end">
                                                        <ul class="link-list-opt no-bdr">
                                                            <li><a href="#"><em
                                                                        class="icon ni ni-focus"></em><span>Quick
                                                                        View</span></a></li>
                                                            <li><a href="#"><em class="icon ni ni-eye"></em><span>View
                                                                        Details</span></a></li>
                                                            <li><a href="#"><em
                                                                        class="icon ni ni-repeat"></em><span>Transaction</span></a>
                                                            </li>
                                                            <li><a href="#"><em
                                                                        class="icon ni ni-activity-round"></em><span>Activities</span></a>
                                                            </li>
                                                            <li class="divider"></li>
                                                            <li><a href="#"><em
                                                                        class="icon ni ni-shield-star"></em><span>Reset
                                                                        Pass</span></a></li>
                                                            <li><a href="#"><em
                                                                        class="icon ni ni-shield-off"></em><span>Reset
                                                                        2FA</span></a></li>
                                                            <li><a href="#"><em
                                                                        class="icon ni ni-na"></em><span>Suspend
                                                                        User</span></a></li>
                                                        </ul>
                                                    </div>
                                                </div>
                                            </li>
                                        </ul>
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
    <script></script>
@endsection
