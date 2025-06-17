jQuery(function ($) {
    const apiToggle = $('#mc_api_mode');

    const shopIdField = $('#shopId');
    const secretKeyField = $('#secretKey');
    const publicKeyField = $('#publicKey');

    function updateCredentialValues() {
        const mode = apiToggle.prop('checked') ? 'test' : 'live';

        shopIdField.val(shopIdField.data(mode));
        secretKeyField.val(secretKeyField.data(mode));
        publicKeyField.val(publicKeyField.data(mode));
    }

    updateCredentialValues();                  // On load
    apiToggle.change(updateCredentialValues);  // On toggle
});
