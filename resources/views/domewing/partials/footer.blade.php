{{-- footer content put here --}}
<div class="nk-footer is-light" style="padding:0%;">
    <div style="border-bottom: 3px solid var(--dark-blue)"></div>
    <div class="container-fluid p-4">
        <div class="row">
            <div class="col-md-3 col-12 m-auto pt-2">
                <img src="{{ asset('media\Asset_Logo_Lightbg.svg ') }}" alt="Logo">
            </div>
            <div class="col-md-9 col-12 pt-2">
                <div class="hstack px-2">
                    <ul class="nav">
                        <li class='nav-item'><a href={{ route('domewing.home') }} class='nav-link pe-2 fs-18px'
                                style='color:var(--dark-blue);'>{{ $translation['about_us'] }}</a></li>
                        <div class="px-2"></div>
                        <div class="vr" style="color:var(--dark-blue); width:2px; opacity:1;"></div>
                        <div class="px-2"></div>
                        <li class='nav-item'><a href="/domewing/#contactUsSection" class='nav-link px-2 fs-18px'
                                style='color:var(--dark-blue);'>{{ $translation['contact'] }}</a></li>
                        <div class="px-2"></div>
                        <div class="vr" style="color:var(--dark-blue); width:2px; opacity:1;"></div>
                        <div class="px-2"></div>
                        <li class='nav-item'><a href={{ route('domewing.FAQ') }} class='nav-link px-2 fs-18px'
                                style='color:var(--dark-blue);'>{{ $translation['help'] }}</a></li>
                    </ul>
                </div>
                <div class="p-2"></div>
                <div>
                    <div class="px-2 fs-16px" style="color:var(--dark-blue); line-height:1.0;">
                        For legal statements. Lorem ipsum dolor sit amet, consectetuer
                        adipiscing elit, sed diam nonummy nibh euismod tincidunt ut laoreet dolore magna aliquam
                        erat volutpat. Ut
                        wisi enim ad minim veniam, quis nostrud exerci tation ullamcorper suscipit lobortis nisl
                        ut aliquip ex ea commodo consequat. Duis autem vel eum iriure dolor in hendrerit in
                        vulputate velit esse molestie consequat, vel illum dolore eu feugiat nulla facilisis at
                        vero eros et accumsan et iusto odio dignissim qui blandit praesent luptatum zzril delenit
                        augue duis dolore te feugait nulla facilisi.
                    </div>
                    <div class="p-2"></div>
                    <div class="px-2 fs-16px" style="color:var(--dark-blue); line-height:1.0;">
                        Lorem ipsum dolor sit amet, cons ectetuer adipiscing elit, sed diam nonummy nibh euismod
                        tincidunt ut laoreet dolore magna aliquam erat volutpat. Ut wisi enim ad minim
                        veniam, quis nostrud exerci tation ullamcorper suscipit lobortis nisl ut aliquip ex ea
                        commodo consequat.
                    </div>
                    <div class="p-2"></div>
                    <div class="px-2 fs-16px" style="color:var(--dark-blue); line-height:1.0;">
                        Copyright(c) Domewing 2023
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
