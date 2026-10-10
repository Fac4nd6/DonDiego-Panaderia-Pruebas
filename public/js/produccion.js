document.addEventListener('DOMContentLoaded', () => {
    document.querySelector('[data-imprimir-produccion]')?.addEventListener('click', () => {
        window.print();
    });
});