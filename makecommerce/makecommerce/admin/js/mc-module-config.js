window.addEventListener('message', function (event) {
    if (event.data === 'openMakeCommercePluginConfModal') {
        if (typeof mcApiData.redirect_url === 'string') {
            window.location.href = mcApiData.redirect_url;
        }
    }

    if (event.data === 'makecommerce_renew_session') {
        window.location.reload();
    }
}, false);
