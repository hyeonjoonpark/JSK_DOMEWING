@extends('layouts.main')
@section('title')
    Content Management System
@endsection
@section('subtitle')
    <p>Manage Your Own Content Here.</p>
@endsection
@section('content')
    <div class="row g-gs">
        <div class="col">
            <div class="card card-bordered preview">
                <div class="card-inner">
                    <h5 class="card-title">Image Banner</h5>
                    <h6 class="card-subtitle mb-2">This is a preview of your banner.</h6>
                    <div id="carouselExCap" class="carousel slide" data-bs-ride="carousel">
                        <ol class="carousel-indicators">
                            <li data-bs-target="#carouselExCap" data-bs-slide-to="0" class="active"></li>
                            <li data-bs-target="#carouselExCap" data-bs-slide-to="1"></li>
                            <li data-bs-target="#carouselExCap" data-bs-slide-to="2"></li>
                        </ol>
                        <div class="carousel-inner text-light">
                            <div class="carousel-item active">
                                <img src={{ asset('library/example.png') }} class="d-block w-100" alt="...">
                            </div>
                            <div class="carousel-item">
                                <img src={{ asset('library/sample.avif') }} class="d-block w-100" alt="...">
                            </div>
                            <div class="carousel-item">
                                <img src={{ asset('library/example.png') }} class="d-block w-100" alt="...">
                            </div>
                        </div>
                        <a class="carousel-control-prev" href="#carouselExCap" role="button" data-bs-slide="prev">
                            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                            <span class="visually-hidden">Previous</span>
                        </a>
                        <a class="carousel-control-next" href="#carouselExCap" role="button" data-bs-slide="next">
                            <span class="carousel-control-next-icon" aria-hidden="true"></span>
                            <span class="visually-hidden">Next</span>
                        </a>
                    </div>
                </div>
            </div>
            <div class="card card-bordered preview">
                <div class="card-inner">
                    <div class="nk-block-between pb-2">
                        <div class="nk-block-head-content">
                            <h5 class="card-title">Manage Image Banner</h5>
                            <h6 class="card-subtitle mb-2">Upload your images here.</h6>
                        </div>
                        <div class="nk-block-head-content">
                            <form id="uploadForm" action="/admin/upload-image-banner" method="post"
                                enctype="multipart/form-data">
                                @csrf
                                <label class="btn btn-white btn-dim btn-outline-primary">
                                    <input type="file" name="file" style="display:none;" accept="image/*"
                                        onchange="uploadFile(this)">
                                    <em class="icon fa-solid fa-upload"></em><span>Upload</span>
                                </label>
                            </form>

                            {{-- <a class="btn btn-white btn-dim btn-outline-primary">
                                <em class="icon fa-solid fa-upload"></em><span>Upload New</span>
                            </a> --}}
                        </div>
                    </div>

                    <table class="datatable-init nk-tb-list nk-tb-ulist" data-auto-responsive="false">
                        <thead>
                            <tr class="nk-tb-item nk-tb-head">
                                <th class="nk-tb-col col-5"><span class="sub-text">Uploads</span></th>
                                <th class="nk-tb-col tb-col-md text-center"><span class="sub-text">Date Added</span></th>
                                <th class="nk-tb-col text-center"><span class="sub-text">Status</span></th>
                                <th class="nk-tb-col nk-tb-col-tools text-end">
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="nk-tb-item">
                                <td class="nk-tb-col">
                                    <img src={{ asset('library/sample.avif') }} alt>
                                </td>
                                <td class="nk-tb-col tb-col-md text-center">
                                    <span>05 Oct 2019</span>
                                </td>
                                <td class="nk-tb-col text-center">
                                    <span class="tb-status text-success">Active</span>
                                </td>
                                <td class="nk-tb-col nk-tb-col-tools">
                                    <ul class="nk-tb-actions gx-1">
                                        <li>
                                            <div class="drodown">
                                                <a href="#" class="dropdown-toggle btn btn-icon btn-trigger"
                                                    data-bs-toggle="dropdown"><em class="icon ni ni-more-h"></em></a>
                                                <div class="dropdown-menu dropdown-menu-end">
                                                    <ul class="link-list-opt no-bdr">
                                                        <li><a href="#"><em class="icon ni ni-focus"></em><span>Quick
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
                                                        <li><a href="#"><em class="icon ni ni-na"></em><span>Suspend
                                                                    User</span></a></li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </li>
                                    </ul>
                                </td>
                            </tr><!-- .nk-tb-item  -->
                            <tr class="nk-tb-item">
                                <td class="nk-tb-col">
                                    <img src={{ asset('library/example.png') }} alt>
                                </td>
                                <td class="nk-tb-col tb-col-md text-center">
                                    <span>05 Oct 2020</span>
                                </td>
                                <td class="nk-tb-col text-center">
                                    <span class="tb-status text-success">InActive</span>
                                </td>
                                <td class="nk-tb-col nk-tb-col-tools">
                                    <ul class="nk-tb-actions gx-1">
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
                                                        <li><a href="#"><em class="icon ni ni-na"></em><span>Suspend
                                                                    User</span></a></li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </li>
                                    </ul>
                                </td>
                            </tr><!-- .nk-tb-item  -->
                            {{-- <tr class="nk-tb-item">
                                <td class="nk-tb-col">
                                    <div class="user-card">
                                        <div class="user-avatar bg-success d-none d-sm-flex">
                                            <span>AL</span>
                                        </div>
                                        <div class="user-info">
                                            <span class="tb-lead">Ashley Lawson <span
                                                    class="dot dot-warning d-md-none ms-1"></span></span>
                                            <span>ashley@softnio.com</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="nk-tb-col tb-col-mb" data-order="580.00">
                                    <span class="tb-amount">580.00 <span class="currency">USD</span></span>
                                </td>
                                <td class="nk-tb-col tb-col-md">
                                    <span>+124 394-1787</span>
                                </td>
                                <td class="nk-tb-col tb-col-lg">
                                    <span>07 Feb 2020</span>
                                </td>
                                <td class="nk-tb-col tb-col-md">
                                    <span class="tb-status text-warning">Pending</span>
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
                                                        <li><a href="#"><em class="icon ni ni-na"></em><span>Suspend
                                                                    User</span></a></li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </li>
                                    </ul>
                                </td>
                            </tr><!-- .nk-tb-item  -->
                            <tr class="nk-tb-item">
                                <td class="nk-tb-col">
                                    <div class="user-card">
                                        <div class="user-avatar bg-info d-none d-sm-flex">
                                            <span>JL</span>
                                        </div>
                                        <div class="user-info">
                                            <span class="tb-lead">Joe Larson <span
                                                    class="dot dot-success d-md-none ms-1"></span></span>
                                            <span>larson@example.com</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="nk-tb-col tb-col-mb" data-order="32000.34">
                                    <span class="tb-amount">32,000.34 <span class="currency">USD</span></span>
                                </td>
                                <td class="nk-tb-col tb-col-md">
                                    <span>+168 603-2320</span>
                                </td>
                                <td class="nk-tb-col tb-col-lg">
                                    <span>04 Feb 2020</span>
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
                                                        <li><a href="#"><em class="icon ni ni-na"></em><span>Suspend
                                                                    User</span></a></li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </li>
                                    </ul>
                                </td>
                            </tr><!-- .nk-tb-item  -->
                            <tr class="nk-tb-item">
                                <td class="nk-tb-col">
                                    <div class="user-card">
                                        <div class="user-avatar bg-danger d-none d-sm-flex">
                                            <span>JM</span>
                                        </div>
                                        <div class="user-info">
                                            <span class="tb-lead">Jane Montgomery <span
                                                    class="dot dot-success d-md-none ms-1"></span></span>
                                            <span>jane84@example.com</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="nk-tb-col tb-col-mb" data-order="0.00">
                                    <span class="tb-amount">0.00 <span class="currency">USD</span></span>
                                </td>
                                <td class="nk-tb-col tb-col-md">
                                    <span>+439 271-5360</span>
                                </td>
                                <td class="nk-tb-col tb-col-lg">
                                    <span>01 Feb 2020</span>
                                </td>
                                <td class="nk-tb-col tb-col-md">
                                    <span class="tb-status text-danger">Suspend</span>
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
                                                        <li><a href="#"><em class="icon ni ni-na"></em><span>Suspend
                                                                    User</span></a></li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </li>
                                    </ul>
                                </td>
                            </tr><!-- .nk-tb-item  -->
                            <tr class="nk-tb-item">
                                <td class="nk-tb-col">
                                    <div class="user-card">
                                        <div class="user-avatar bg-purple d-none d-sm-flex">
                                            <span>FB</span>
                                        </div>
                                        <div class="user-info">
                                            <span class="tb-lead">Frances Burns <span
                                                    class="dot dot-success d-md-none ms-1"></span></span>
                                            <span>info@softnio.com</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="nk-tb-col tb-col-mb" data-order="42.50">
                                    <span class="tb-amount">42.50 <span class="currency">USD</span></span>
                                </td>
                                <td class="nk-tb-col tb-col-md">
                                    <span>+639 130-3150</span>
                                </td>
                                <td class="nk-tb-col tb-col-lg">
                                    <span>31 Jan 2020</span>
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
                                                        <li><a href="#"><em class="icon ni ni-na"></em><span>Suspend
                                                                    User</span></a></li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </li>
                                    </ul>
                                </td>
                            </tr><!-- .nk-tb-item  -->
                            <tr class="nk-tb-item">
                                <td class="nk-tb-col">
                                    <div class="user-card">
                                        <div class="user-avatar bg-dark d-none d-sm-flex">
                                            <span>AB</span>
                                        </div>
                                        <div class="user-info">
                                            <span class="tb-lead">Alan Butler<span
                                                    class="dot dot-success d-md-none ms-1"></span></span>
                                            <span>butler@example.com</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="nk-tb-col tb-col-mb" data-order="440.34">
                                    <span class="tb-amount">440.34 <span class="currency">USD</span></span>
                                </td>
                                <td class="nk-tb-col tb-col-md">
                                    <span>+963 309-1706</span>
                                </td>
                                <td class="nk-tb-col tb-col-lg">
                                    <span>18 Jan 2020</span>
                                </td>
                                <td class="nk-tb-col tb-col-md">
                                    <span class="tb-status text-info">Inactive</span>
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
                                                        <li><a href="#"><em class="icon ni ni-na"></em><span>Suspend
                                                                    User</span></a></li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </li>
                                    </ul>
                                </td>
                            </tr><!-- .nk-tb-item  -->
                            <tr class="nk-tb-item">
                                <td class="nk-tb-col">
                                    <div class="user-card">
                                        <div class="user-avatar bg-warning d-none d-sm-flex">
                                            <span>VL</span>
                                        </div>
                                        <div class="user-info">
                                            <span class="tb-lead">Victoria Lynch <span
                                                    class="dot dot-success d-md-none ms-1"></span></span>
                                            <span>victoria@example.com</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="nk-tb-col tb-col-mb" data-order="59400.68">
                                    <span class="tb-amount">59,400.68 <span class="currency">USD</span></span>
                                </td>
                                <td class="nk-tb-col tb-col-md">
                                    <span>+811 985-4846</span>
                                </td>
                                <td class="nk-tb-col tb-col-lg">
                                    <span>15 Jan 2020</span>
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
                                                        <li><a href="#"><em class="icon ni ni-na"></em><span>Suspend
                                                                    User</span></a></li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </li>
                                    </ul>
                                </td>
                            </tr><!-- .nk-tb-item  -->
                            <tr class="nk-tb-item">
                                <td class="nk-tb-col">
                                    <div class="user-card">
                                        <div class="user-avatar bg-success d-none d-sm-flex">
                                            <span>PN</span>
                                        </div>
                                        <div class="user-info">
                                            <span class="tb-lead">Patrick Newman <span
                                                    class="dot dot-success d-md-none ms-1"></span></span>
                                            <span>patrick@example.com</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="nk-tb-col tb-col-mb" data-order="30.00">
                                    <span class="tb-amount">30.00 <span class="currency">USD</span></span>
                                </td>
                                <td class="nk-tb-col tb-col-md">
                                    <span>+942 238-4474</span>
                                </td>
                                <td class="nk-tb-col tb-col-lg">
                                    <span>08 Jan 2020</span>
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
                                                        <li><a href="#"><em class="icon ni ni-na"></em><span>Suspend
                                                                    User</span></a></li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </li>
                                    </ul>
                                </td>
                            </tr><!-- .nk-tb-item  -->
                            <tr class="nk-tb-item">
                                <td class="nk-tb-col">
                                    <div class="user-card">
                                        <div class="user-avatar bg-info d-none d-sm-flex">
                                            <span>JL</span>
                                        </div>
                                        <div class="user-info">
                                            <span class="tb-lead">Joe Larson <span
                                                    class="dot dot-success d-md-none ms-1"></span></span>
                                            <span>larson@example.com</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="nk-tb-col tb-col-mb" data-order="32000.34">
                                    <span class="tb-amount">32,000.34 <span class="currency">USD</span></span>
                                </td>
                                <td class="nk-tb-col tb-col-md">
                                    <span>+168 603-2320</span>
                                </td>
                                <td class="nk-tb-col tb-col-lg">
                                    <span>04 Feb 2020</span>
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
                                                        <li><a href="#"><em class="icon ni ni-na"></em><span>Suspend
                                                                    User</span></a></li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </li>
                                    </ul>
                                </td>
                            </tr><!-- .nk-tb-item  -->
                            <tr class="nk-tb-item">
                                <td class="nk-tb-col">
                                    <div class="user-card">
                                        <div class="user-avatar bg-success d-none d-sm-flex">
                                            <span>AL</span>
                                        </div>
                                        <div class="user-info">
                                            <span class="tb-lead">Ashley Lawson <span
                                                    class="dot dot-warning d-md-none ms-1"></span></span>
                                            <span>ashley@softnio.com</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="nk-tb-col tb-col-mb" data-order="580.00">
                                    <span class="tb-amount">580.00 <span class="currency">USD</span></span>
                                </td>
                                <td class="nk-tb-col tb-col-md">
                                    <span>+124 394-1787</span>
                                </td>
                                <td class="nk-tb-col tb-col-lg">
                                    <span>07 Feb 2020</span>
                                </td>
                                <td class="nk-tb-col tb-col-md">
                                    <span class="tb-status text-warning">Pending</span>
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
                                                        <li><a href="#"><em class="icon ni ni-na"></em><span>Suspend
                                                                    User</span></a></li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </li>
                                    </ul>
                                </td>
                            </tr><!-- .nk-tb-item  -->
                            <tr class="nk-tb-item">
                                <td class="nk-tb-col">
                                    <div class="user-card">
                                        <div class="user-avatar bg-danger d-none d-sm-flex">
                                            <span>JM</span>
                                        </div>
                                        <div class="user-info">
                                            <span class="tb-lead">Jane Montgomery <span
                                                    class="dot dot-success d-md-none ms-1"></span></span>
                                            <span>jane84@example.com</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="nk-tb-col tb-col-mb" data-order="0.00">
                                    <span class="tb-amount">0.00 <span class="currency">USD</span></span>
                                </td>
                                <td class="nk-tb-col tb-col-md">
                                    <span>+439 271-5360</span>
                                </td>
                                <td class="nk-tb-col tb-col-lg">
                                    <span>01 Feb 2020</span>
                                </td>
                                <td class="nk-tb-col tb-col-md">
                                    <span class="tb-status text-danger">Suspend</span>
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
                                                        <li><a href="#"><em class="icon ni ni-na"></em><span>Suspend
                                                                    User</span></a></li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </li>
                                    </ul>
                                </td>
                            </tr><!-- .nk-tb-item  -->
                            <tr class="nk-tb-item">
                                <td class="nk-tb-col">
                                    <div class="user-card">
                                        <div class="user-avatar bg-purple d-none d-sm-flex">
                                            <span>FB</span>
                                        </div>
                                        <div class="user-info">
                                            <span class="tb-lead">Frances Burns <span
                                                    class="dot dot-success d-md-none ms-1"></span></span>
                                            <span>info@softnio.com</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="nk-tb-col tb-col-mb" data-order="42.50">
                                    <span class="tb-amount">42.50 <span class="currency">USD</span></span>
                                </td>
                                <td class="nk-tb-col tb-col-md">
                                    <span>+639 130-3150</span>
                                </td>
                                <td class="nk-tb-col tb-col-lg">
                                    <span>31 Jan 2020</span>
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
                                                        <li><a href="#"><em class="icon ni ni-na"></em><span>Suspend
                                                                    User</span></a></li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </li>
                                    </ul>
                                </td>
                            </tr><!-- .nk-tb-item  -->
                            <tr class="nk-tb-item">
                                <td class="nk-tb-col">
                                    <div class="user-card">
                                        <div class="user-avatar bg-dark d-none d-sm-flex">
                                            <span>AB</span>
                                        </div>
                                        <div class="user-info">
                                            <span class="tb-lead">Alan Butler<span
                                                    class="dot dot-success d-md-none ms-1"></span></span>
                                            <span>butler@example.com</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="nk-tb-col tb-col-mb" data-order="440.34">
                                    <span class="tb-amount">440.34 <span class="currency">USD</span></span>
                                </td>
                                <td class="nk-tb-col tb-col-md">
                                    <span>+963 309-1706</span>
                                </td>
                                <td class="nk-tb-col tb-col-lg">
                                    <span>18 Jan 2020</span>
                                </td>
                                <td class="nk-tb-col tb-col-md">
                                    <span class="tb-status text-info">Inactive</span>
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
                                                        <li><a href="#"><em class="icon ni ni-na"></em><span>Suspend
                                                                    User</span></a></li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </li>
                                    </ul>
                                </td>
                            </tr><!-- .nk-tb-item  --> --}}
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    @if (session('success'))
        <div class="modal fade" tabindex="-1" id="modalSuccess">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <a href="#" class="close" data-bs-dismiss="modal"><em class="icon ni ni-cross"></em></a>
                    <div class="modal-body modal-body-lg text-center">
                        <div class="nk-modal">
                            <em class="nk-modal-icon icon icon-circle icon-circle-xxl ni ni-check bg-success"></em>
                            <h4 class="nk-modal-title">{{ session('success') }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if (session('error'))
        <div class="modal fade" tabindex="-1" id="modalFailed">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <a href="#" class="close" data-bs-dismiss="modal"><em class="icon ni ni-cross"></em></a>
                    <div class="modal-body modal-body-lg text-center">
                        <div class="nk-modal">
                            <em class="nk-modal-icon icon icon-circle icon-circle-xxl ni ni-cross bg-danger"></em>
                            <h4 class="nk-modal-title">{{ session('error') }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
@endsection
@section('scripts')
    <script>
        function uploadFile(input) {
            var form = input.form;
            form.submit();
        }

        $(document).ready(function() {
            // Show success modal
            @if (session('success'))
                $('#modalSuccess').modal('show');
            @endif

            // Show error modal
            @if (session('error'))
                $('#modalFailed').modal('show');
            @endif
        });
    </script>
@endsection
