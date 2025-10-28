// Toast Notification Utility
class ToastManager {
    constructor() {
        this.container = document.querySelector('.toast-container');
        if (!this.container) {
            // Create container if it doesn't exist
            this.container = document.createElement('div');
            this.container.className = 'toast-container position-fixed top-0 end-0 p-3';
            this.container.style.zIndex = '9999';
            document.body.appendChild(this.container);
        }
    }

    /**
     * Show a toast notification
     * @param {string} message - The message to display
     * @param {string} type - The type of toast (success, error, warning, info)
     * @param {string} title - Optional title
     * @param {number} duration - Duration in milliseconds (0 to not auto-hide)
     */
    show(message, type = 'info', title = null, duration = 5000) {
        const toast = document.createElement('div');
        toast.className = 'toast';
        toast.setAttribute('role', 'alert');
        toast.setAttribute('aria-live', 'assertive');
        toast.setAttribute('aria-atomic', 'true');
        
        if (duration > 0) {
            toast.setAttribute('data-bs-autohide', 'true');
            toast.setAttribute('data-bs-delay', duration);
        } else {
            toast.setAttribute('data-bs-autohide', 'false');
        }

        // Icon mapping
        const icons = {
            success: { icon: 'fa-check-circle', color: 'text-success' },
            error: { icon: 'fa-exclamation-circle', color: 'text-danger' },
            warning: { icon: 'fa-exclamation-triangle', color: 'text-warning' },
            info: { icon: 'fa-info-circle', color: 'text-info' }
        };

        const iconConfig = icons[type] || icons.info;

        // Toast header
        const header = document.createElement('div');
        header.className = 'toast-header';
        
        const titleDiv = document.createElement('div');
        titleDiv.className = 'me-auto fw-bold';
        titleDiv.innerHTML = `<i class="fas ${iconConfig.icon} me-2 ${iconConfig.color}"></i>${title || type.charAt(0).toUpperCase() + type.slice(1)}`;
        
        const closeBtn = document.createElement('button');
        closeBtn.type = 'button';
        closeBtn.className = 'btn-close';
        closeBtn.setAttribute('data-bs-dismiss', 'toast');
        closeBtn.setAttribute('aria-label', 'Close');
        
        header.appendChild(titleDiv);
        header.appendChild(closeBtn);

        // Toast body
        const body = document.createElement('div');
        body.className = 'toast-body';
        body.textContent = message;

        toast.appendChild(header);
        toast.appendChild(body);

        // Add to container
        this.container.appendChild(toast);

        // Initialize and show toast
        const bsToast = new bootstrap.Toast(toast);
        bsToast.show();

        // Remove from DOM when hidden
        toast.addEventListener('hidden.bs.toast', () => {
            toast.remove();
        });

        return bsToast;
    }

    /**
     * Show success toast
     */
    success(message, title = 'Success') {
        return this.show(message, 'success', title);
    }

    /**
     * Show error toast
     */
    error(message, title = 'Error') {
        return this.show(message, 'error', title);
    }

    /**
     * Show warning toast
     */
    warning(message, title = 'Warning') {
        return this.show(message, 'warning', title);
    }

    /**
     * Show info toast
     */
    info(message, title = 'Information') {
        return this.show(message, 'info', title);
    }
}

// Initialize Toast Manager
window.toastManager = new ToastManager();
window.ToastManager = window.toastManager;

// Helper function for convenience
window.showToast = function(message, type, title, duration) {
    return window.toastManager.show(message, type, title, duration);
};

