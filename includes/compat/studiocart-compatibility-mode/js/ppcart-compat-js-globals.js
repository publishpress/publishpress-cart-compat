(function (window) {
	if (typeof window.sc_tax_settings !== 'undefined' && typeof window.ppcart_tax_settings === 'undefined') {
		window.ppcart_tax_settings = window.sc_tax_settings;
	}
})(window);
