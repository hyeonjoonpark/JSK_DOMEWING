<ul class="nav flex-column px-2 mt-auto">
    <li class="nav-item member-nav">
        <a class="d-flex align-items-center {{ Request::is('domewing/account-settings') ? 'active' : '' }} py-3 nav-link "
            href={{ route('member_details') }}>
            @if (Request::is('domewing/account-settings'))
                <img src="{{ asset('media/Asset_Section_Selected_Profile.svg') }}" class="icon-size">
            @else
                <img src="{{ asset('media/Asset_Section_Unselected_Profile.svg') }}" class="icon-size">
            @endif
            <h4 class="{{ Request::is('domewing/account-settings') ? 'fw-bold' : '' }} px-3"
                style="color: var(--dark-blue);">
                Account Details</h4>
        </a>
    </li>
    <li class="nav-item member-nav">
        <a class="d-flex align-items-center {{ Request::is('domewing/to-ship') ? 'active' : '' }} py-3 nav-link"
            href={{ route('to_ship') }}>
            @if (Request::is('domewing/to-ship'))
                <img src="{{ asset('media/Asset_Section_Selected_To_Ship.svg') }}" class="icon-size">
            @else
                <img src="{{ asset('media/Asset_Section_Unselected_To_Ship.svg') }}" class="icon-size">
            @endif
            <h4 class="px-3" style="color: var(--dark-blue);">
                To Ship</h4>
        </a>
    </li>
    <li class="nav-item member-nav">
        <a class="d-flex align-items-center py-3 nav-link {{ Request::is('domewing/to-receive') ? 'active' : '' }}"
            href={{ route('to_receive') }}>
            @if (Request::is('domewing/to-receive'))
                <img src="{{ asset('media/Asset_Section_Selected_To_Receive.svg') }}" class="icon-size">
            @else
                <img src="{{ asset('media/Asset_Section_Unselected_To_Receive.svg') }}" class="icon-size">
            @endif
            <h4 class="px-3" style="color: var(--dark-blue);">To Receive</h4>
        </a>
    </li>
    <li class="nav-item member-nav">
        <a class="d-flex align-items-center py-3 nav-link {{ Request::is('domewing/to-rate') ? 'active' : '' }}"
            href={{ route('to_rate') }}>
            @if (Request::is('domewing/to-rate'))
                <img src="{{ asset('media/Asset_Section_Selected_To_Rate.svg') }}" class="icon-size">
            @else
                <img src="{{ asset('media/Asset_Section_Unselected_To_Rate.svg') }}" class="icon-size">
            @endif
            <h4 class="px-3" style="color: var(--dark-blue);">To Rate</h4>
        </a>
    </li>
    <li class="nav-item member-nav">
        <a class="d-flex align-items-center py-3 nav-link {{ Request::is('domewing/purchase-history') ? 'active' : '' }}"
            href={{ route('purchase_history') }}>
            @if (Request::is('domewing/purchase-history'))
                <img src="{{ asset('media/Asset_Section_Selected_Purchased_History.svg') }}" class="icon-size">
            @else
                <img src="{{ asset('media/Asset_Section_Unselected_Purchased_History.svg') }}" class="icon-size">
            @endif
            <h4 class="px-3" style="color: var(--dark-blue);">Purchase History</h4>
        </a>
    </li>
    <li class="nav-item member-nav">
        <a class="d-flex align-items-center py-3 nav-link {{ Request::is('domewing/wishlist*') ? 'active' : '' }}"
            href={{ route('wishlist') }}>
            @if (Request::is('domewing/wishlist'))
                <img src="{{ asset('media/Asset_Section_Selected_Wishlist.svg') }}" class="icon-size">
            @else
                <img src="{{ asset('media/Asset_Section_Unselected_Wishlist.svg') }}" class="icon-size">
            @endif
            <h4 class="px-3" style="color: var(--dark-blue);">Wishlist</h4>
        </a>
    </li>
</ul>

<div class="pt-4" style="border-bottom: 2px solid var(--light-blue)"></div>
<div class="pb-4"></div>
