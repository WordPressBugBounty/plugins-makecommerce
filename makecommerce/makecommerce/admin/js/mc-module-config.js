window.addEventListener('message', function (event) {
    console.log(mcApiData.redirect_url)

    if (event.data === 'openMakeCommercePluginConfModal') {
        if (typeof mcApiData.redirect_url === 'string') {
            window.location.href = mcApiData.redirect_url;
        }
    }
}, false);
