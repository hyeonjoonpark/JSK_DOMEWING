@extends('domewing.layouts.main')
@section('content')
    <div class="auth-container">
        <img class="bg-img" src="media\Asset_Bg_Whale.svg" alt="Background">
        <div class="custom-container custom-inner-content auth-page">
            <div class="auth-inner-content">
                <div class="text-bold text-xl text-dark-blue">Sign Up Here</div>
                <div style="padding-bottom:50px;"></div>
                <div class="row">
                    <div class="col-md-4 col-sm-12">
                        <div class="text-regular text-sm pb-2" style="color: var(--light-blue)">Title*</div>
                        <div class="form-group">
                            <input type="text"
                                class="form-control custom-auth-textbox text-md text-dark-blue text-regular"
                                placeholder="Ms">
                        </div>
                        <div style="padding-bottom:30px;"></div>
                    </div>
                    <div class="col-md-4 col-sm-12">
                        <div class="text-regular text-sm pb-2" style="color: var(--light-blue)">First Name*
                        </div>
                        <div class="form-group">
                            <input type="text"
                                class="form-control custom-auth-textbox text-md text-dark-blue text-regular"
                                placeholder="Jane">
                        </div>
                        <div style="padding-bottom:30px;"></div>
                    </div>
                    <div class="col-md-4 col-sm-12">
                        <div class="text-regular text-sm pb-2" style="color: var(--light-blue)">Last Name*</div>
                        <div class="form-group">
                            <input type="text"
                                class="form-control custom-auth-textbox text-md text-dark-blue text-regular"
                                placeholder="Doe">
                        </div>
                        <div style="padding-bottom:30px;"></div>
                    </div>
                </div>

                <div class="text-regular text-sm pb-2" style="color: var(--light-blue)">Phone Number*</div>
                <div class="input-group">
                    <button class="input-group-text custom-auth-textbox text-md text-dark-blue text-regular" type="button"
                        data-bs-toggle="dropdown" aria-expanded="false" style="border-right: none;">+60</button>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="#">+60</a></li>
                        <li><a class="dropdown-item" href="#">+85</a></li>
                        <li><a class="dropdown-item" href="#">+65</a></li>
                    </ul>
                    <div class="vr vr-dark my-2" style="color:var(--dark-blue); width:2px;"></div>
                    <input type="text" style="border-left:none;"
                        class="form-control custom-auth-textbox text-md text-dark-blue text-regular"
                        placeholder="0123456789">
                </div>
                <div style="padding-bottom:30px;"></div>
                <div class="text-regular text-sm pb-2" style="color: var(--light-blue)">Email Address*</div>
                <div class="form-group">
                    <input type="text" class="form-control custom-auth-textbox text-md text-dark-blue text-regular"
                        placeholder="janedoe@gmail.com">
                </div>
                <div style="padding-bottom:30px;"></div>
                <div class="text-regular text-sm pb-2" style="color: var(--light-blue)">Set Password*</div>
                <div class="form-group">
                    <input type="text" class="form-control custom-auth-textbox text-md text-dark-blue text-regular"
                        placeholder="password123456">
                </div>
                <div style="padding-bottom:30px;"></div>
                <div class="text-regular text-sm pb-2" style="color: var(--light-blue)">Confirm Your Password*</div>
                <div class="form-group">
                    <input type="text" class="form-control custom-auth-textbox text-md text-dark-blue text-regular"
                        placeholder="password123456">
                </div>
                <div style="padding-bottom:30px;"></div>
                <div class="text-regular text-sm pb-2" style="color: var(--light-blue)">Set Username*</div>
                <div class="form-group">
                    <input type="text" class="form-control custom-auth-textbox text-md text-dark-blue text-regular"
                        placeholder="janedoetest">
                </div>
                <div style="padding-bottom:80px;"></div>
                <div class="d-grid col-4 mx-auto">
                    <button type="button" class="btn btn-primary auth-button text-regular text-md p-3">Sign Up</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
@endsection
