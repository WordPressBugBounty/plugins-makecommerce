document.addEventListener("DOMContentLoaded", () => {
    const adminBar = document.getElementById("wpadminbar");
    const wpContent = document.getElementById("wpbody-content");
    if (adminBar && wpContent) {
        const barHeight = adminBar.offsetHeight;
        wpContent.style.height = `calc(100vh - ${barHeight}px)`;
    }
});
