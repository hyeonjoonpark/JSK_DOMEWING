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
                    <div class="nk-block-between">
                        <div class="nk-block-head-content">
                            <h5 class="card-title">Domain</h5>
                            <h6 class="card-subtitle mb-2">Name Your Domain Here.</h6>
                        </div>
                        <div class="nk-block-head-content">
                            <div class="nk-block-between">
                                <div class="form-group m-auto px-2">
                                    <div class="form-control-wrap">
                                        <div class="input-group input-group-lg">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text" id="basic-addon3">domewing/</span>
                                            </div>
                                            <input type="text" class="form-control" aria-label="Large" id="domain-name"
                                                onchange="domainNameFormat(this);" value="{{ $domain->domain_name }}"
                                                required>
                                        </div>
                                    </div>
                                </div>

                                <button type="button" onclick="updateDomainName()" class="btn btn-lg btn-primary"><em
                                        class="icon fa-solid fa-floppy-disk"></em></button>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card card-bordered preview">
                <div class="card-inner">
                    <div class="nk-block-between">
                        <div class="nk-block-head-content">
                            <h5 class="card-title">Theme Color</h5>
                            <h6 class="card-subtitle mb-2">Pick your favourite theme color.</h6>
                        </div>
                        <div class="nk-block-head-content">
                            @if ($theme_color != null)
                                <input type="color" id="colorpicker" value="{{ $theme_color->color_code }}"
                                    onchange="changeThemeColor({{ $domain->domain_id }})">
                            @else
                                <input type="color" id="colorpicker" value="#0000ff"
                                    onchange="changeThemeColor({{ $domain->domain_id }})">
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            @if (count($image_banners) > 0)
                <div class="card card-bordered preview">
                    <div class="card-inner">
                        <h5 class="card-title">Image Banner</h5>
                        <h6 class="card-subtitle mb-2">This is a preview of your banner.</h6>
                        <div id="carouselExCap" class="carousel slide" data-bs-ride="carousel">
                            <ol class="carousel-indicators">
                                @foreach ($image_banners as $index => $image_banner)
                                    <li data-bs-target="#carouselExCap" data-bs-slide-to="{{ $index }}"
                                        class="{{ $index === 0 ? 'active' : '' }}"></li>
                                @endforeach
                            </ol>
                            <div class="carousel-inner">
                                @foreach ($image_banners as $index => $image_banner)
                                    <div class="carousel-item{{ $index === 0 ? ' active' : '' }}">
                                        <img src="{{ asset('library/' . $image_banner->source) }}" class="d-block w-100">
                                    </div>
                                @endforeach
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
            @endif
            <div class="card card-bordered preview">
                <div class="card-inner">
                    <div class="nk-block-between pb-2">
                        <div class="nk-block-head-content">
                            <h5 class="card-title">Manage Image Banner</h5>
                            <h6 class="card-subtitle mb-2">Upload your images here.</h6>
                        </div>
                        <div class="nk-block-head-content">
                            <form id="uploadForm" enctype="multipart/form-data">
                                @csrf
                                <label class="btn btn-white btn-dim btn-outline-primary">
                                    <input type="file" name="file" style="display:none;" accept="image/*"
                                        onchange="prepareUpload({{ $domain->domain_id }})">
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
                                <th class="nk-tb-col tb-col-md text-center"><span class="sub-text">Date Added</span>
                                </th>
                                <th class="nk-tb-col text-center"><span class="sub-text">Status</span></th>
                                <th class="nk-tb-col nk-tb-col-tools text-end">
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($images as $image)
                                <tr class="nk-tb-item">
                                    <td class="nk-tb-col">
                                        <img src="{{ asset('library/' . $image->source) }}" alt>
                                    </td>
                                    <td class="nk-tb-col tb-col-md text-center">
                                        <span>{{ $image->formatted_created_at }}</span>
                                    </td>
                                    @if ($image->status == 'ACTIVE')
                                        <td class="nk-tb-col text-center">
                                            <span class="tb-status text-success">{{ $image->status }}</span>
                                        </td>
                                    @else
                                        <td class="nk-tb-col text-center">
                                            <span class="tb-status text-warning">{{ $image->status }}</span>
                                        </td>
                                    @endif
                                    <td class="nk-tb-col nk-tb-col-tools">
                                        <ul class="nk-tb-actions gx-1">
                                            <li>
                                                <div class="drodown">
                                                    <a href="#" class="dropdown-toggle btn btn-icon btn-trigger"
                                                        data-bs-toggle="dropdown"><em class="icon ni ni-more-h"></em></a>
                                                    <div class="dropdown-menu dropdown-menu-end">
                                                        <ul class="link-list-opt no-bdr">
                                                            <li>
                                                                @if ($image->status == 'ACTIVE')
                                                                    <a onclick="changeStatus({{ $image->id }});">
                                                                        <em
                                                                            class="icon fa-regular fa-eye-slash"></em><span>Hide</span>
                                                                    </a>
                                                                @else
                                                                    <a onclick="changeStatus({{ $image->id }});">
                                                                        <em
                                                                            class="icon fa-regular fa-eye"></em><span>Show</span>
                                                                    </a>
                                                                @endif
                                                            </li>
                                                            <li>
                                                                <a onclick="removeImageInit({{ $image->id }});">
                                                                    <em
                                                                        class="icon fa-solid fa-trash"></em><span>Remove</span>
                                                                </a>
                                                            </li>
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

    <div class="modal fade" id="modalRemove">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <a class="close" data-bs-dismiss="modal"><em class="icon ni ni-cross"></em></a>
                <div class="modal-body modal-body-lg text-center">
                    <div class="nk-modal">
                        <em class="nk-modal-icon icon icon-circle icon-circle-xxl ni ni-property-remove bg-danger"></em>
                        <h4 class="nk-modal-title">Are you sure to remove this image?</h4>
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
        function updateDomainName() {
            var remember_token = "{{ Auth::guard('user')->user()->remember_token }}";
            var domain_id = "{{ $domain->domain_id }}";
            var domain_name = $('#domain-name').val();

            $.ajax({
                url: '/api/seller/edit-domain-name',
                type: 'post',
                dataType: 'json',
                data: {
                    domain_name: domain_name,
                    domain_id: domain_id,
                    rememberToken: remember_token
                },
                success: function(response) {
                    const status = parseInt(response.status);
                    if (status == 1) {
                        Swal.fire({
                            icon: 'success',
                            title: response.return,
                        })
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
                    console.log(response);
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

        function prepareUpload(domain_id) {
            var formData = new FormData($('#uploadForm')[0]);
            formData.append('domain_id', domain_id);
            formData.append('rememberToken', '{{ Auth::guard('user')->user()->remember_token }}');

            $.ajax({
                url: '../../../api/seller/upload-image-banner',
                type: 'POST',
                processData: false,
                contentType: false,
                data: formData,
                success: function(response) {

                    const status = parseInt(response.status);
                    if (status == 1) {
                        Swal.fire({
                            icon: 'success',
                            title: response.message
                        }).then((result) => {
                            location.reload();
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Opps',
                            text: response.message
                        });
                    }
                },
                error: function(xhr, status, error) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Opps',
                        text: error
                    });
                }
            });
        }

        //change theme color
        function changeThemeColor(domain_id) {
            var color = $('#colorpicker').val();
            var remember_token = "{{ Auth::guard('user')->user()->remember_token }}";

            $.ajax({
                url: '/api/seller/change-theme-color',
                type: 'post',
                dataType: 'json',
                data: {
                    color: color,
                    domain_id: domain_id,
                    rememberToken: remember_token
                },
                success: function(response) {
                    const status = parseInt(response.status);
                    if (status == 1) {
                        Swal.fire({
                            icon: 'success',
                            title: response.return,
                        })
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

        //remove image
        function removeImageInit(image_id) {
            $("#confirmRemove").attr("onclick", 'removeImage(' + image_id + ')');
            $('#modalRemove').modal('show');
        }

        function removeImage(image_id) {

            var remember_token = "{{ Auth::guard('user')->user()->remember_token }}";

            $.ajax({
                url: '/api/seller/remove-image-banner',
                type: 'post',
                dataType: 'json',
                data: {
                    image_id: image_id,
                    rememberToken: remember_token,
                },
                success: function(response) {
                    const status = parseInt(response.status);
                    $('.modal').modal('hide');
                    if (status == 1) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Image Removed Successfully',
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

        //show or hide image from banner
        function changeStatus(image_id) {
            var remember_token = "{{ Auth::guard('user')->user()->remember_token }}";

            $.ajax({
                url: '/api/seller/change-image-status',
                type: 'post',
                dataType: 'json',
                data: {
                    image_id: image_id,
                    rememberToken: remember_token,
                },
                success: function(response) {
                    const status = parseInt(response.status);
                    $('.modal').modal('hide');
                    if (status == 1) {
                        Swal.fire({
                            icon: 'success',
                            title: response.return,
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
                    console.log(response);
                }
            });
        }
    </script>
@endsection
