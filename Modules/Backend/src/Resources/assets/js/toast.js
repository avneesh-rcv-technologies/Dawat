
// Modern Toast System
class ModernToast {
    constructor() {
        this.container = document.getElementById('toast-container');
        this.toasts = [];
        this.maxToasts = 5;
    }

    show(message, type = 'info', title = '', duration = 5000) {
        // Remove oldest toast if we have too many
        if (this.toasts.length >= this.maxToasts) {
            this.remove(this.toasts[0]);
        }

        // console.log(this.container);

        const toast = this.create(message, type, title, duration);
        this.container.appendChild(toast);
        this.toasts.push(toast);

        // Trigger animation
        setTimeout(() => {
            toast.classList.add('show');
        }, 10);

        // Auto remove
        if (duration > 0) {
            setTimeout(() => {
                this.remove(toast);
            }, duration);
        }

        return toast;
    }

    create(message, type, title, duration) {
        const toast = document.createElement('div');
        toast.className = `toast toast-${type}`;

        const icons = {
            success: 'check_circle',
            error: 'error',
            warning: 'warning',
            info: 'info'
        };

        const titles = {
            success: title || 'Success',
            error: title || 'Error',
            warning: title || 'Warning',
            info: title || 'Information'
        };

        toast.innerHTML = `
    <div class="toast-left">
        <div class="toast-inner">
        <span class="material-icons-outlined toast-left-icon">
            <svg width="12" height="12" viewBox="0 0 12 12" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M2.81087 0.477295V2.46063C2.81087 2.78733 2.81087 2.95067 2.87445 3.07546C2.93038 3.18522 3.01962 3.27446 3.12938 3.33038C3.25416 3.39396 3.41751 3.39396 3.74421 3.39396H7.71087C8.03757 3.39396 8.20092 3.39396 8.3257 3.33038C8.43546 3.27446 8.5247 3.18522 8.58063 3.07546C8.64421 2.95067 8.64421 2.78733 8.64421 2.46063V1.06063M8.64421 10.9773V7.24396C8.64421 6.91726 8.64421 6.75392 8.58063 6.62913C8.5247 6.51937 8.43546 6.43013 8.3257 6.37421C8.20092 6.31063 8.03757 6.31063 7.71087 6.31063H3.74421C3.41751 6.31063 3.25416 6.31063 3.12938 6.37421C3.01962 6.43013 2.93038 6.51937 2.87445 6.62913C2.81087 6.75392 2.81087 6.91726 2.81087 7.24396V10.9773M10.9775 4.16716V8.17729C10.9775 9.15739 10.9775 9.64743 10.7868 10.0218C10.619 10.3511 10.3513 10.6188 10.022 10.7866C9.64768 10.9773 9.15763 10.9773 8.17754 10.9773H3.27754C2.29745 10.9773 1.8074 10.9773 1.43306 10.7866C1.10377 10.6188 0.836056 10.3511 0.668278 10.0218C0.477539 9.64743 0.477539 9.15739 0.477539 8.17729V3.27729C0.477539 2.2972 0.477539 1.80716 0.668278 1.43281C0.836056 1.10353 1.10377 0.835812 1.43306 0.668034C1.8074 0.477295 2.29745 0.477295 3.27754 0.477295H7.28767C7.57303 0.477295 7.71571 0.477295 7.84998 0.50953C7.96902 0.53811 8.08282 0.585248 8.1872 0.649215C8.30494 0.721363 8.40583 0.822252 8.60761 1.02403L10.4308 2.84723C10.6326 3.049 10.7335 3.14989 10.8056 3.26763C10.8696 3.37201 10.9167 3.48582 10.9453 3.60486C10.9775 3.73913 10.9775 3.8818 10.9775 4.16716Z" stroke="white" stroke-width="0.954545" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        </span>
        </div>
    </div>

    <div class="toast-body">
        <div class="toast-title">${titles[type]}</div>
        <div class="toast-message">${message}</div>
    </div>

    <button class="toast-close" onclick="toastSystem.remove(this.parentElement)">
        <span class="material-icons-outlined">close</span>
    </button>

    ${duration > 0 ? `<div class="toast-progress" style="animation-duration:${duration}ms;"></div>` : ''}
`;


        // Click to dismiss
        toast.addEventListener('click', (e) => {
            if (!e.target.closest('.toast-close')) {
                this.remove(toast);
            }
        });

        return toast;
    }

    remove(toast) {
        if (!toast || !toast.parentElement) return;

        toast.classList.add('hide');

        setTimeout(() => {
            if (toast.parentElement) {
                toast.parentElement.removeChild(toast);
            }
            this.toasts = this.toasts.filter(t => t !== toast);
        }, 300);
    }

    clear() {
        this.toasts.forEach(toast => this.remove(toast));
    }
}



// Initialize toast system
const toastSystem = new ModernToast();

// Global function for easy use
function showToast(message, type = 'info', title = '', duration = 5000) {

    return toastSystem.show(message, type, title, duration);
}

// Additional utility functions
function showSuccess(message, title = 'Success', duration = 4000) {
    return showToast(message, 'success', title, duration);
}

function showError(message, title = 'Error', duration = 6000) {
    return showToast(message, 'error', title, duration);
}

function showWarning(message, title = 'Warning', duration = 5000) {
    return showToast(message, 'warning', title, duration);
}

function showInfo(message, title = 'Information', duration = 4000) {
    return showToast(message, 'info', title, duration);
}

// Clear all toasts
function clearAllToasts() {
    toastSystem.clear();
}