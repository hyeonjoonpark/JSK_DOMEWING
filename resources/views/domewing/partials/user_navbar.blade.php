<div>
    <ul class="nav flex-column">
        <li class="nav-item user-nav p-2">
            <a class="d-flex align-items-center py-3 nav-link {{ set_active(['user_details']) }}" href="#">
                <img src="{{ set_active(['user_details']) ? 'media/Asset_Section_Selected_Profile.svg' : 'media/Asset_Section_Unselected_Profile.svg' }}"
                    class="icon-size">
                <p class="{{ set_bold(['user_details']) }} text-dark-blue text-xl px-3">Account Details</p>
            </a>
        </li>
        <li class="nav-item user-nav p-2">
            <a class="d-flex align-items-center py-3 nav-link {{ set_active(['to_ship']) }}" href="#">
                <img src="{{ set_active(['to_ship']) ? 'media/Asset_Section_Selected_To_Ship.svg' : 'media/Asset_Section_Unselected_To_Ship.svg' }}"
                    class="icon-size">
                <p class="{{ set_bold(['to_ship']) }} text-dark-blue text-xl px-3">To Ship</p>
            </a>
        </li>
        <li class="nav-item user-nav p-2">
            <a class="d-flex align-items-center py-3 nav-link {{ set_active(['to_receive']) }}" href="#">
                <img src="{{ set_active(['to_receive']) ? 'media/Asset_Section_Selected_To_Receive.svg' : 'media/Asset_Section_Unselected_To_Receive.svg' }}"
                    class="icon-size">
                <p class="{{ set_bold(['to_receive']) }} text-dark-blue text-xl px-3">To Receive</p>
            </a>
        </li>
        <li class="nav-item user-nav p-2">
            <a class="d-flex align-items-center py-3 nav-link {{ set_active(['to_rate']) }}" href="#">
                <img src="{{ set_active(['to_rate']) ? 'media/Asset_Section_Selected_To_Rate.svg' : 'media/Asset_Section_Unselected_To_Rate.svg' }}"
                    class="icon-size">
                <p class="{{ set_bold(['to_rate']) }} text-dark-blue text-xl px-3">To Rate</p>
            </a>
        </li>
        <li class="nav-item user-nav p-2">
            <a class="d-flex align-items-center py-3 nav-link {{ set_active(['purchase_history']) }}" href="#">
                <img src="{{ set_active(['purchase_history']) ? 'media/Asset_Section_Selected_Purchased_History.svg' : 'media/Asset_Section_Unselected_Purchased_History.svg' }}"
                    class="icon-size">
                <p class="{{ set_bold(['purchase_history']) }} text-dark-blue text-xl px-3">Purchase History</p>
            </a>
        </li>
        <li class="nav-item user-nav p-2">
            <a class="d-flex align-items-center py-3 nav-link {{ set_active(['wishlist']) }}" href="#">
                <img src="{{ set_active(['wishlist']) ? 'media/Asset_Section_Selected_Wishlist.svg' : 'media/Asset_Section_Unselected_Wishlist.svg' }}"
                    class="icon-size">
                <p class="{{ set_bold(['wishlist']) }} text-dark-blue text-xl px-3">Wishlist</p>
            </a>
        </li>
    </ul>
</div>
