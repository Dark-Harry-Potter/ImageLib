// toast.js - Toast notification system
function showToast(message, type = 'success', duration = 4000) {
    const toast = document.createElement('div');
    toast.className = 'toast-notification toast-' + type;
    
    const icons = {
        success: '✅',
        error: '❌',
        warning: '⚠️',
        info: 'ℹ️'
    };
    
    toast.innerHTML = `
        <span class="toast-icon">${icons[type] || 'ℹ️'}</span>
        <span class="toast-message">${message}</span>
        <span class="toast-close">&times;</span>
    `;
    
    document.body.appendChild(toast);
    
    // Show with animation
    setTimeout(() => toast.classList.add('show'), 10);
    
    // Auto-hide
    const timeout = setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => toast.remove(), 300);
    }, duration);
    
    // Close button
    toast.querySelector('.toast-close').addEventListener('click', () => {
        clearTimeout(timeout);
        toast.classList.remove('show');
        setTimeout(() => toast.remove(), 300);
    });
}

// Add toast styles dynamically
const toastStyles = document.createElement('style');
toastStyles.textContent = `
    .toast-notification {
        position: fixed;
        top: 30px;
        right: 30px;
        background: var(--bg-card, #FFFFFF);
        color: var(--text-primary, #1A2A3A);
        padding: 16px 24px;
        border-radius: 12px;
        box-shadow: 0 8px 30px rgba(0,0,0,0.15);
        display: flex;
        align-items: center;
        gap: 12px;
        z-index: 99999;
        transform: translateX(120%);
        transition: transform 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        border-left: 4px solid var(--accent-color, #FF6B4A);
        min-width: 280px;
        max-width: 450px;
        font-size: 14px;
        font-family: 'Inter', sans-serif;
    }
    .toast-notification.show {
        transform: translateX(0);
    }
    .toast-notification .toast-icon {
        font-size: 20px;
        flex-shrink: 0;
    }
    .toast-notification .toast-message {
        flex: 1;
    }
    .toast-notification .toast-close {
        cursor: pointer;
        font-size: 20px;
        color: var(--text-muted, #6A7A8A);
        transition: color 0.2s;
        line-height: 1;
        margin-left: 8px;
    }
    .toast-notification .toast-close:hover {
        color: var(--text-primary, #1A2A3A);
    }
    .toast-success {
        border-left-color: #20B2AA;
    }
    .toast-error {
        border-left-color: #EF4444;
    }
    .toast-warning {
        border-left-color: #F59E0B;
    }
    .toast-info {
        border-left-color: #2D9CDB;
    }
    @media (max-width: 600px) {
        .toast-notification {
            top: 20px;
            right: 20px;
            left: 20px;
            min-width: auto;
            padding: 14px 18px;
        }
    }
`;
document.head.appendChild(toastStyles);