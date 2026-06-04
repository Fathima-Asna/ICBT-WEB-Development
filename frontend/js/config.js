// GlobeTrek Adventures - Dynamic Decoupled API Base URL Configurations
const CONFIG = {
    // Detect environment: Use local PHP server if running locally, otherwise use production Back4App URL
    API_BASE_URL: window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1'
        ? 'http://localhost:8000'
        : 'https://globetrek-backend.back4app.io'
};

// Dynamic Form Action Rewriter
document.addEventListener('DOMContentLoaded', () => {
    const forms = document.querySelectorAll('form[action]');
    forms.forEach(form => {
        const action = form.getAttribute('action');
        if (action && !action.startsWith('http://') && !action.startsWith('https://')) {
            const cleanAction = action.replace(/^\//, '');
            form.setAttribute('action', `${CONFIG.API_BASE_URL}/${cleanAction}`);
        }
    });
});
