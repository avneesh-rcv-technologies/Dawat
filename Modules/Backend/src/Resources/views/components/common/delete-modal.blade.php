<div id="global-delete-modal"
    class="delete-confirmation-modal fixed inset-0 bg-black/60 backdrop-blur-sm flex items-center justify-center z-50 hidden transition-opacity duration-300 ease-out"
    role="presentation">
    <div
        class="delete-confirmation-card bg-[var(--color-white)] p-8 rounded-2xl shadow-[0_4px_20px_var(--color-shadow)] w-full max-w-md transform transition-all duration-300 ease-out scale-95"
        role="dialog" aria-modal="true" aria-labelledby="delete-confirmation-title" aria-describedby="delete-confirmation-description">
        <div class="delete-confirmation-top flex justify-between gap-4 mb-2">
            <div class="delete-confirmation-icon w-16 h-16 bg-red-100 text-red-500 rounded-full flex items-center justify-center"
                style="border: 8px solid #FEF3F2">
                <svg width="20" height="22" viewBox="0 0 20 22" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path
                        d="M14 5V4.2C14 3.0799 14 2.51984 13.782 2.09202C13.5903 1.71569 13.2843 1.40973 12.908 1.21799C12.4802 1 11.9201 1 10.8 1H9.2C8.07989 1 7.51984 1 7.09202 1.21799C6.71569 1.40973 6.40973 1.71569 6.21799 2.09202C6 2.51984 6 3.0799 6 4.2V5M8 10.5V15.5M12 10.5V15.5M1 5H19M17 5V16.2C17 17.8802 17 18.7202 16.673 19.362C16.3854 19.9265 15.9265 20.3854 15.362 20.673C14.7202 21 13.8802 21 12.2 21H7.8C6.11984 21 5.27976 21 4.63803 20.673C4.07354 20.3854 3.6146 19.9265 3.32698 19.362C3 18.7202 3 17.8802 3 16.2V5"
                        stroke="#D92D20" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
            </div>

            <div class="delete-confirmation-close text-gray-500 text-2xl">
                <button type="button" aria-label="Close confirmation dialog" onclick="closeDeleteModal()"><span
                        class="material-icons-outlined">close</span></button>
            </div>
        </div>

        <div class="delete-confirmation-copy mb-3">
            <h2 id="delete-confirmation-title" class="text-xl font-semibold text-[#181D27]">Need Confirmation</h2>
            <p id="delete-confirmation-description" class="text-[#535862] text-sm mt-1">
                Are you sure you want to make these changes?
            </p>
        </div>

        <form id="global-delete-form" class="form-ajax !w-full" method="POST">
            @csrf
            @method('DELETE')
            <div class="delete-confirmation-actions w-full grid grid-cols-2 gap-4">
                <button type="submit" name="button" class="btn-cancel">Delete</button>
                <button type="button" onclick="closeDeleteModal()" class="btn-primary">Cancel</button>

            </div>
        </form>
    </div>
</div>
<style>
    #global-delete-modal.delete-confirmation-modal { padding: 20px; background: rgba(24, 29, 39, .58); }
    #global-delete-modal .delete-confirmation-card {
        position: relative;
        width: min(100%, 448px);
        padding: 32px;
        border: 1px solid rgba(255, 255, 255, .7);
        border-radius: 18px;
        background: #fff;
        box-shadow: 0 24px 70px rgba(16, 24, 40, .24);
        transform: translateY(0) scale(1);
    }
    #global-delete-modal .delete-confirmation-top { align-items: flex-start; margin-bottom: 14px; }
    #global-delete-modal .delete-confirmation-icon { width: 64px; height: 64px; border: 8px solid #fff3f2 !important; background: #fee4e2; color: #d92d20; }
    #global-delete-modal .delete-confirmation-close button { display: grid; place-items: center; width: 36px; height: 36px; border: 0; border-radius: 8px; background: transparent; color: #667085; cursor: pointer; transition: background .15s ease, color .15s ease; }
    #global-delete-modal .delete-confirmation-close button:hover { background: #f2f4f7; color: #344054; }
    #global-delete-modal .delete-confirmation-close button:focus-visible,
    #global-delete-modal .delete-confirmation-actions button:focus-visible { outline: 3px solid rgba(37, 99, 235, .35); outline-offset: 2px; }
    #global-delete-modal .delete-confirmation-copy { margin-bottom: 18px; }
    #global-delete-modal .delete-confirmation-copy h2 { margin: 0; color: #181d27; font-size: 20px; line-height: 1.3; }
    #global-delete-modal .delete-confirmation-copy p { margin: 6px 0 0; color: #535862; line-height: 1.5; }
    #global-delete-modal .delete-confirmation-actions { gap: 12px; }
    #global-delete-modal .delete-confirmation-actions button { min-height: 42px; width: 100%; border-radius: 8px; font-size: 14px; font-weight: 650; }
    @media (max-width: 480px) {
        #global-delete-modal .delete-confirmation-card { padding: 24px; border-radius: 15px; }
        #global-delete-modal .delete-confirmation-actions { grid-template-columns: 1fr; }
    }
</style>
