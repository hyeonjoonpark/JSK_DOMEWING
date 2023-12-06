<!-- Modal Success -->
<div class="modal fade" tabindex="-1" id="modalSuccess">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content" style="border: 2px solid var(--dark-blue);">
            <a href="#" class="close" data-bs-dismiss="modal" aria-label="Close">
                <em class="icon ni ni-cross"></em>
            </a>
            <div class="modal-body text-center p-0 pb-4">
                <img src="{{ asset('media/Asset_Notif_Success.svg') }}">
                <h2 id="modalSuccessTitle" class="py-2" style="color: var(--cyan-blue)">Success</h2>
                <h3 id="modalSuccessMessage" class="py-2" style="color: var(--dark-blue)">Message Here</h3>
                <button data-bs-dismiss="modal" aria-label="Close" class="btn btn-lg btn-primary">OK</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Fail -->
<div class="modal fade" tabindex="-1" id="modalFail">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content" style="border: 2px solid var(--dark-blue);">
            <a href="#" class="close" data-bs-dismiss="modal" aria-label="Close">
                <em class="icon ni ni-cross"></em>
            </a>
            <div class="modal-body text-center p-0 pb-4">
                <img src="{{ asset('media/Asset_Notif_Error.svg') }}">
                <h2 id="modalFailTitle" class="py-2" style="color: var(--cyan-blue)">Error</h2>
                <h3 id="modalFailMessage" class="py-2" style="color: var(--dark-blue)">Cant Process</h3>
            </div>
        </div>
    </div>
</div>

<!-- Modal Loading -->
<div class="modal fade" tabindex="-1" id="modalLoading">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content" style="border: 2px solid var(--dark-blue);">
            <div class="modal-body text-center p-0 pb-4">
                <img src="{{ asset('assets/images/search-loader.gif') }}">
                <h2 id="modalLoadingTitle" class="py-2" style="color: var(--cyan-blue)">Loading...</h2>
            </div>
        </div>
    </div>
</div>
