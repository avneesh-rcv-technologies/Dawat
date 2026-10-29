
// Wrap everything in a function to avoid global scope pollution
(function () {
    class ModalController {
        constructor(modalId, ajaxUrl) {
            this.modalId = modalId;
            this.ajaxUrl = ajaxUrl;
            this.isOpen = false;
            this.isLoading = false;
            this.content = '';
            this.error = null;
            this.isTransitioning = false;

            this.initElements();
            this.setupEventListeners();
        }

        initElements() {
            this.backdrop = document.getElementById(`modal-backdrop-${this.modalId}`);
            this.container = document.getElementById(`modal-container-${this.modalId}`);
            this.closeButton = document.getElementById(`modal-close-${this.modalId}`);
            this.loadingElement = document.getElementById(`modal-loading-${this.modalId}`);
            this.contentElement = document.getElementById(`modal-content-${this.modalId}`);
            this.errorElement = document.getElementById(`modal-error-${this.modalId}`);
            this.errorMessageElement = document.getElementById(`modal-error-message-${this.modalId}`);
            this.retryButton = document.getElementById(`modal-retry-${this.modalId}`);

            // Store ajaxUrl on backdrop for later use
            if (this.backdrop) {
                this.backdrop.dataset.ajaxUrl = this.ajaxUrl;
            }
        }

        setupEventListeners() {
            if (this.closeButton) {
                this.closeButton.addEventListener('click', () => this.toggleModal(false));
            }

            if (this.retryButton) {
                this.retryButton.addEventListener('click', () => this.fetchData());
            }

            if (this.backdrop) {
                this.backdrop.addEventListener('mousedown', (e) => {
                    this.backdrop.dataset.clicked = (e.target === this.backdrop).toString();
                });

                this.backdrop.addEventListener('mouseup', (e) => {
                    if (this.backdrop.dataset.clicked === 'true' && !this.isTransitioning) {
                        this.toggleModal(false);
                    }
                    delete this.backdrop.dataset.clicked;
                });
            }
        }

        toggleModal(show) {
            if (this.isTransitioning) return;

            this.isTransitioning = true;
            this.isOpen = show;

            if (show) {
                this.showModal();
            } else {
                this.hideModal();
            }
        }

        showModal() {
            this.backdrop.style.pointerEvents = 'none';
            this.container.style.pointerEvents = 'none';

            this.backdrop.classList.remove('modal-hidden');
            this.container.classList.remove('modal-hidden');

            void this.backdrop.offsetWidth;

            this.backdrop.classList.add('show');
            this.container.classList.add('show');
            document.body.style.overflow = 'hidden';

            setTimeout(() => {
                this.backdrop.style.pointerEvents = 'auto';
                this.container.style.pointerEvents = 'auto';
                this.isTransitioning = false;
            }, 100);

            this.fetchData();
        }

        hideModal() {
            this.backdrop.style.pointerEvents = 'none';
            this.container.style.pointerEvents = 'none';

            this.backdrop.classList.remove('show');
            this.container.classList.remove('show');

            setTimeout(() => {
                this.backdrop.classList.add('modal-hidden');
                this.container.classList.add('modal-hidden');
                document.body.style.overflow = '';
                this.isTransitioning = false;
                this.error = null;
                this.updateErrorUI();

                // Remove modal from DOM after hiding
                setTimeout(() => {
                    if (this.backdrop && this.backdrop.parentNode) {
                        this.backdrop.parentNode.removeChild(this.backdrop);
                    }
                }, 100);
            }, 100);
        }

        updateLoadingUI() {
            if (!this.loadingElement || !this.contentElement || !this.errorElement) return;

            if (this.isLoading) {
                this.loadingElement.classList.remove('modal-hidden');
                this.contentElement.classList.add('modal-hidden');
                this.errorElement.classList.add('modal-hidden');
            } else {
                this.loadingElement.classList.add('modal-hidden');
            }
        }

        updateContentUI() {
            if (!this.contentElement || !this.errorElement) return;

            if (this.content && !this.isLoading && !this.error) {
                this.contentElement.innerHTML = this.content;
                this.contentElement.classList.remove('modal-hidden');
                this.errorElement.classList.add('modal-hidden');
            } else {
                this.contentElement.classList.add('modal-hidden');
            }
        }

        updateErrorUI() {
            if (!this.errorMessageElement || !this.errorElement || !this.contentElement) return;

            if (this.error && !this.isLoading) {
                this.errorMessageElement.textContent = this.error;
                this.errorElement.classList.remove('modal-hidden');
                this.contentElement.classList.add('modal-hidden');
            } else {
                this.errorElement.classList.add('modal-hidden');
            }
        }

        async fetchData() {
            if (!this.ajaxUrl) return;

            this.isLoading = true;
            this.error = null;
            this.updateLoadingUI();
            this.updateContentUI();
            this.updateErrorUI();

            try {
                const response = await fetch(this.ajaxUrl);
                if (!response.ok) throw new Error('Failed to load content');
                this.content = await response.text();
                if (this.contentElement) {
                    this.contentElement.innerHTML = this.content;

                    // Execute any scripts that came back in the fetched HTML.
                    // Browsers do not execute <script> tags added via innerHTML,
                    // so recreate them to ensure inline and external scripts run.
                    const runScripts = async () => {
                        const scripts = Array.from(this.contentElement.querySelectorAll('script'));

                        for (const oldScript of scripts) {
                            const newScript = document.createElement('script');

                            // copy attributes (type, async, defer, src, etc.)
                            for (let i = 0; i < oldScript.attributes.length; i++) {
                                const attr = oldScript.attributes[i];
                                newScript.setAttribute(attr.name, attr.value);
                            }

                            if (oldScript.src) {
                                // External script: load sequentially to preserve order
                                await new Promise((resolve, reject) => {
                                    newScript.onload = resolve;
                                    newScript.onerror = resolve; // resolve even on error to continue
                                    // ensure scripts are not loaded async to preserve order
                                    try { newScript.async = false; } catch (e) { }
                                    newScript.src = oldScript.src;
                                    document.head.appendChild(newScript);
                                });
                            } else {
                                // Inline script: set text and append to DOM to execute
                                newScript.text = oldScript.textContent || oldScript.innerText || '';
                                this.contentElement.appendChild(newScript);
                            }

                            // remove the original inert script tag
                            if (oldScript.parentNode) oldScript.parentNode.removeChild(oldScript);
                        }
                    };

                    // Run scripts and then dispatch an event so other code can initialize
                    runScripts()
                        .then(() => {
                            try {
                                const ev = new CustomEvent('modal:loaded', { detail: { modalId: this.modalId } });
                                document.dispatchEvent(ev);
                            } catch (e) {
                                // older browsers fallback
                                const evFallback = document.createEvent('Event');
                                evFallback.initEvent('modal:loaded', true, true);
                                document.dispatchEvent(evFallback);
                            }
                        })
                        .catch(err => console.error('Error running modal scripts', err));
                }
            } catch (err) {
                this.error = err.message;
                console.error(`Modal ${this.modalId} error:`, err);
            } finally {
                this.isLoading = false;
                this.updateLoadingUI();
                this.updateContentUI();
                this.updateErrorUI();
            }
        }
    }

    // Event delegation for modal triggers
    document.addEventListener('click', function (e) {
        const trigger = e.target.closest('[id^="modal-trigger-"]');
        if (!trigger) return;

        e.preventDefault();

        const modalId = trigger.dataset.modalId;
        const ajaxUrl = trigger.dataset.ajaxUrl;
        const modalTitle = trigger.dataset.modalTitle;
        const modalSize = trigger.dataset.modalSize;

        // Create and show the modal
        createModal(modalId, ajaxUrl, modalTitle, modalSize);
    });

    function createModal(modalId, ajaxUrl, modalTitle, modalSize) {

        // Check if modal already exists
        const existingModal = document.getElementById(`modal-backdrop-${modalId}`);
        if (existingModal) {
            existingModal.remove();
        }

        // Create modal HTML
        const modalHTML = `
        <div id="modal-backdrop-${modalId}" class="modal-backdrop fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm modal-hidden">
            <div id="modal-container-${modalId}" role="dialog" aria-modal="true" aria-labelledby="modal-title-${modalId}"
                class="modal-container w-full ${modalSize} bg-white rounded-xl shadow-2xl overflow-hidden max-h-[90vh]">

                <div class="flex justify-between items-center border-b border-b-black mx-4 !py-2  !pt-3 " >
                    <h5 class="text-lg font-semibold text-[#2B2F32]">${modalTitle}</h5>
                    <button id="modal-close-${modalId}"
                        class="p-1 rounded-full hover:bg-gray-100 transition-colors focus:outline-none ">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-gray-500 hover:text-gray-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class=" overflow-y-auto max-h-[60vh] ">
                    <div id="modal-loading-${modalId}" class="flex flex-col items-center justify-center py-12 space-y-4">
                        <div class="modal-spinner"></div>
                    </div>

                    <div id="modal-content-${modalId}" class="prose max-w-none modal-hidden p-6"></div>

                    <div id="modal-error-${modalId}"
                        class="p-4 bg-red-50 border-l-4 border-red-500 rounded-lg modal-hidden">
                        <div class="flex items-center">
                            <svg class="h-5 w-5 text-red-500 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <h4 class="text-lg font-medium text-red-800">Error</h4>
                        </div>
                        <p id="modal-error-message-${modalId}" class="mt-2 text-red-600"></p>
                        <button id="modal-retry-${modalId}"
                            class="mt-4 px-4 py-2 bg-red-100 text-red-700 rounded-md hover:bg-red-200 transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>
                            Retry
                        </button>
                    </div>
                </div>
            </div>
        </div>
        `;

        // Append modal to body
        document.body.insertAdjacentHTML('beforeend', modalHTML);

        // Initialize and show the modal
        const modal = new ModalController(modalId, ajaxUrl);
        modal.toggleModal(true);
    }
})();
