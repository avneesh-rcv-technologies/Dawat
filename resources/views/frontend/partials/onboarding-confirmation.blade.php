<style>
    #onboarding-confirmation { position: fixed; inset: 0; margin: auto; width: calc(100% - 32px); max-width: 440px; max-height: calc(100dvh - 32px); overflow: auto; padding: 28px; border: 1px solid #d4dfe3; border-radius: 18px; background: #fff; color: #103647; font-family: inherit; box-shadow: 0 24px 80px rgb(0 0 0 / 25%); }
    #onboarding-confirmation::backdrop { background: rgb(0 0 0 / 60%); }
    #onboarding-confirmation h2 { margin: 0 0 12px; font-size: 24px; line-height: 1.3; font-weight: 700; }
    #onboarding-confirmation p { margin: 0; font-size: 15px; line-height: 1.6; }
    #onboarding-confirmation .confirmation-actions { display: flex; flex-wrap: wrap; justify-content: flex-end; gap: 12px; margin-top: 24px; }
    #onboarding-confirmation button { padding: 10px 20px; border: 1px solid #bdc8cc; border-radius: 24px; background: transparent; color: inherit; font: inherit; font-size: 14px; font-weight: 700; cursor: pointer; }
    #onboarding-confirmation button:hover { background: #edf5f5; }
    #onboarding-confirmation button:focus-visible { outline: 3px solid #31be91; outline-offset: 3px; }
    #onboarding-confirmation #onboarding-confirm-accept { background: #103647; border-color: #103647; color: #fff; }
    #onboarding-confirmation #onboarding-confirm-accept:hover { background: #204f61; }
    #onboarding-confirmation[data-theme="dark"] { background: #181818; color: #fff; border-color: #444; }
    #onboarding-confirmation[data-theme="dark"] button:hover { background: #333; }
    #onboarding-confirmation[data-theme="dark"] #onboarding-confirm-accept { background: #fff; border-color: #fff; color: #111; }
</style>
<dialog id="onboarding-confirmation" data-theme="{{ $confirmationTheme ?? 'light' }}" aria-labelledby="onboarding-confirmation-title" aria-describedby="onboarding-confirmation-message">
    <h2 id="onboarding-confirmation-title">Confirm submission</h2>
    <p id="onboarding-confirmation-message"></p>
    <div class="confirmation-actions">
        <button id="onboarding-confirm-cancel" type="button" autofocus>Cancel</button>
        <button id="onboarding-confirm-accept" type="button">Confirm</button>
    </div>
</dialog>
<script src="{{ asset('js/onboarding-confirmation.js') }}"></script>
