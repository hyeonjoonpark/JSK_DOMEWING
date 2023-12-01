@extends('domewing.layouts.main')
@section('content')
    @include('domewing.partials.user_profile_short')

    <div class="p-lg-5 p-2" style="background-color: var(--pure-white);">
        <div class="row m-0">
            <div class="col-md-4 col-12">
                @include('domewing.partials.user_navbar')
            </div>
            {{-- <div class="col-md-8 col-12">
                <div class="user-details-padding">
                    <div class="row">
                        <div class="col-lg-4 col-md-12">
                            <label for="title" class="form-label text-regular text-md text-light-blue">Title</label>
                            <input type="text"
                                class="form-control custom-user-textbox text-regular text-xl text-dark-blue" id="title"
                                placeholder="Ms">
                            <div style="padding-bottom: 20px;"></div>
                        </div>
                        <div class="col-lg-4 col-md-12">
                            <label for="fname" class="form-label text-regular text-md text-light-blue">First Name</label>
                            <input type="text"
                                class="form-control custom-user-textbox text-regular text-xl text-dark-blue" id="fname"
                                placeholder="Jane">
                            <div style="padding-bottom: 20px;"></div>
                        </div>
                        <div class="col-lg-4 col-md-12">
                            <label for="lname" class="form-label text-regular text-md text-light-blue">Last Name</label>
                            <input type="text"
                                class="form-control custom-user-textbox text-regular text-xl text-dark-blue" id="lname"
                                placeholder="Doe">
                            <div style="padding-bottom: 20px;"></div>
                        </div>
                    </div>

                    <label for="userId" class="form-label text-regular text-md text-light-blue">User ID</label>
                    <input type="text" class="form-control custom-user-textbox text-regular text-xl text-dark-blue"
                        id="userId" placeholder="janedoetesting" disabled>
                    <div style="padding-bottom: 20px;"></div>

                    <label for="phoneNumber" class="form-label text-regular text-md text-light-blue">Phone Number</label>
                    <div class="input-group">
                        <button class="input-group-text custom-user-textbox text-xl text-dark-blue text-regular"
                            type="button" data-bs-toggle="dropdown" aria-expanded="false"
                            style="border-right: none;">+60</button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="#">+60</a></li>
                            <li><a class="dropdown-item" href="#">+85</a></li>
                            <li><a class="dropdown-item" href="#">+65</a></li>
                        </ul>
                        <div class="vr vr-dark my-2" style="color:var(--dark-blue); width:2px;"></div>
                        <input type="text" style="border-left:none;"
                            class="form-control custom-user-textbox text-xl text-dark-blue text-regular"
                            placeholder="0123456789" id="phoneNumber">
                    </div>
                    <div style="padding-bottom: 20px;"></div>

                    <label for="email" class="form-label text-regular text-md text-light-blue">Email Address</label>
                    <input type="text" class="form-control custom-user-textbox text-regular text-xl text-dark-blue"
                        id="email" placeholder="janedoe@gmail.com">

                    <div style="padding-bottom: 50px;"></div>

                    <p class="text-regular text-xl text-dark-blue">Default Shipping Address</p>
                    <div style="padding-bottom: 20px;"></div>

                    <label for="street" class="form-label text-regular text-md text-light-blue">Street</label>
                    <input type="text" class="form-control custom-user-textbox text-regular text-xl text-dark-blue"
                        id="street" placeholder="1 Pusat Sumber 1 Jln Bukit Jalil Taman Teknologi 5">

                    <div style="padding-bottom: 20px;"></div>

                    <div class="row">
                        <div class="col-lg-6 col-md-12">
                            <label for="city" class="form-label text-regular text-md text-light-blue">City</label>
                            <input type="text"
                                class="form-control custom-user-textbox text-regular text-xl text-dark-blue" id="city"
                                placeholder="Kuala Lumpur">
                            <div style="padding-bottom: 20px;"></div>
                        </div>
                        <div class="col-lg-6 col-md-12">
                            <label for="state" class="form-label text-regular text-md text-light-blue">State</label>
                            <input type="text"
                                class="form-control custom-user-textbox text-regular text-xl text-dark-blue" id="state"
                                placeholder="Wilayah Persekutuan">
                            <div style="padding-bottom: 20px;"></div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-lg-6 col-md-12">
                            <label for="zipCode" class="form-label text-regular text-md text-light-blue">Zip Code</label>
                            <input type="text"
                                class="form-control custom-user-textbox text-regular text-xl text-dark-blue" id="zipCode"
                                placeholder="57100">
                            <div style="padding-bottom: 20px;"></div>
                        </div>
                        <div class="col-lg-6 col-md-12">
                            <label for="Country" class="form-label text-regular text-md text-light-blue">Country</label>
                            <input type="text"
                                class="form-control custom-user-textbox text-regular text-xl text-dark-blue"
                                id="Country" placeholder="Malaysia">
                            <div style="padding-bottom: 20px;"></div>
                        </div>
                    </div>

                    <button type="button" class="btn d-inline-flex align-items-center">
                        <img src="media\Asset_Control_Add.svg" class="icon-size">
                        <p class="text-regular text-dark-blue text-xl px-3">Add Another Address</p>
                    </button>

                </div>
            </div> --}}
        </div>
    </div>
@endsection

@section('scripts')
@endsection
