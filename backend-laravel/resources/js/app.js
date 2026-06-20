document.addEventListener('DOMContentLoaded', () => {
    const sidebarWrapper = document.querySelector('.sidebar-wrapper');
    const overlayScrollbars = window.OverlayScrollbarsGlobal?.OverlayScrollbars;
    const shouldUseCustomScrollbar = sidebarWrapper && overlayScrollbars && window.innerWidth > 992;

    if (! shouldUseCustomScrollbar) {
        return;
    }

    overlayScrollbars(sidebarWrapper, {
        scrollbars: {
            theme: 'os-theme-light',
            autoHide: 'leave',
            clickScroll: true,
        },
    });
});
