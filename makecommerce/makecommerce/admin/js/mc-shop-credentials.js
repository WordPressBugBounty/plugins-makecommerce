window.addEventListener('message', function (event) {
    if (event.data === 'openMakeCommerceShopCredentialsModal') {
        const container = document.getElementById('makecommerce-modal-container');

        if (!document.getElementById('makecommerceCredentialsModal')) {
            container.insertAdjacentHTML('beforeend', `

            `);
        }

        const modal = new bootstrap.Modal(document.getElementById('makecommerceCredentialsModal'));
        modal.show();
    }
}, false);

document.getElementById('mc-shop-credentials')?.addEventListener('click', function () {
    let myModal = new bootstrap.Modal(document.getElementById('makecommerceCredentialsModal'));
    myModal.show();
});
