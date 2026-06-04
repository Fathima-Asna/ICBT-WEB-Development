/**
 * GlobeTrek Adventures - Dark/Light Theme Controller (Decoupled SVG-ready)
 */

(function() {
    const savedTheme = localStorage.getItem('globetrek-theme') || 'light';
    document.documentElement.setAttribute('data-theme', savedTheme);
})();

function toggleTheme() {
    const current = document.documentElement.getAttribute('data-theme') || 'light';
    const target = current === 'dark' ? 'light' : 'dark';
    
    document.documentElement.setAttribute('data-theme', target);
    localStorage.setItem('globetrek-theme', target);
}
