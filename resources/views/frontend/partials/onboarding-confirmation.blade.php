<style>
    #onboarding-confirmation { position: fixed; inset: 0; margin: auto; width: calc(100% - 32px); max-width: 440px; max-height: calc(100dvh - 32px); overflow: auto; padding: 28px; border: 1px solid #232328; border-radius: 18px; background: #141418; color: #fff; font-family: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif; box-shadow: 0 24px 80px rgb(0 0 0 / 60%); }
    #onboarding-confirmation::backdrop { background: rgb(0 0 0 / 75%); backdrop-filter: blur(4px); }
    #onboarding-confirmation h2 { margin: 0 0 12px; font-size: 20px; line-height: 1.3; font-weight: 700; color: #fff; letter-spacing: -0.01em; }
    #onboarding-confirmation p { margin: 0; font-size: 14px; line-height: 1.6; color: #a1a1aa; }
    #onboarding-confirmation .confirmation-actions { display: flex; flex-wrap: wrap; justify-content: flex-end; gap: 12px; margin-top: 24px; }
    #onboarding-confirmation button { padding: 10px 20px; border: 1px solid #383842; border-radius: 24px; background: transparent; color: #d4d4d8; font: inherit; font-size: 14px; font-weight: 600; cursor: pointer; transition: all 0.2s ease; }
    #onboarding-confirmation button:hover { background: #1f1f26; color: #fff; }
    #onboarding-confirmation button:focus-visible { outline: 2px solid #ff5500; outline-offset: 2px; }
    #onboarding-confirmation #onboarding-confirm-accept { background: #ff5500; border-color: #ff5500; color: #fff; }
    #onboarding-confirmation #onboarding-confirm-accept:hover { background: #ff6611; }
    #onboarding-confirmation[data-theme="light"] { background: #fff; color: #103647; border-color: #d4dfe3; box-shadow: 0 24px 80px rgb(0 0 0 / 25%); }
    #onboarding-confirmation[data-theme="light"] h2 { color: #103647; }
    #onboarding-confirmation[data-theme="light"] p { color: #526b77; }
    #onboarding-confirmation[data-theme="light"] button { border-color: #bdc8cc; color: inherit; }
    #onboarding-confirmation[data-theme="light"] button:hover { background: #edf5f5; }
    #onboarding-confirmation[data-theme="light"] button:focus-visible { outline-color: #31be91; }
    #onboarding-confirmation[data-theme="light"] #onboarding-confirm-accept { background: #103647; border-color: #103647; color: #fff; }
    #onboarding-confirmation[data-theme="light"] #onboarding-confirm-accept:hover { background: #204f61; }
</style>
<dialog id="onboarding-confirmation" data-theme="{{ $confirmationTheme ?? 'banking' }}" aria-labelledby="onboarding-confirmation-title" aria-describedby="onboarding-confirmation-message">
    <h2 id="onboarding-confirmation-title">Confirm submission</h2>
    <p id="onboarding-confirmation-message"></p>
    <div class="confirmation-actions">
        <button id="onboarding-confirm-cancel" type="button" autofocus>Cancel</button>
        <button id="onboarding-confirm-accept" type="button">Confirm</button>
    </div>
</dialog>
<script src="{{ asset('js/onboarding-confirmation.js') }}"></script>
