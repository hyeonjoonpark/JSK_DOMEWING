<div class="p-lg-5 p-2">

    <div class="card card-bordered p-3" style="background-color:var(--thin-blue);">
        <div class="row user-profile-card m-0">
            <div class="col-md-2 col-2 m-auto text-center">
                <img src="{{ asset('media\Asset_nav_Profile.svg') }}" class="img-fluid">
            </div>
            <div class="col-md-7 col-10 my-auto">
                <h6 style="color: var(--cyan-blue);">Username</h6>
                <h2 style="color: var(--dark-blue);">Jane Doe</h2>
                <ul class="pricing-features pt-4 fs-18px " style="color: var(--dark-blue);">
                    <li>
                        <h6 class="w-30 align-self-center m-0" style="color: var(--cyan-blue);">
                            Location</h6>
                        <h6 class="w-70 align-self-center m-0" style="color: var(--dark-blue);">
                            Kuala Lumpur, Malaysia
                        </h6>
                    </li>
                    <li>
                        <h6 class="w-30 align-self-center m-0" style="color: var(--cyan-blue);">
                            Status</h6>
                        <h6 class="w-70 fw-bold align-self-center m-0" style="color: var(--dark-blue);">
                            7 months on Domewing
                        </h6>
                    </li>
                </ul>
            </div>
            <div class="col-md-3 d-md-grid d-none">
                <button class="btn ms-auto my-auto" style="background: var(--dark-blue);">
                    <a href={{ route('member_details') }}>
                        <h6 class="text-white">Edit Profile</h6>
                    </a>
                </button>
            </div>
        </div>

    </div>
</div>
