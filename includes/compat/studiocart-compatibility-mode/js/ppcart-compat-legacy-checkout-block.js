(function () {
	if (! window.wp || ! wp.blocks || typeof wp.blocks.getBlockType !== 'function') {
		return;
	}

	var leftover = 'sc-products-shortcode/product-shortcode';
	if (wp.blocks.getBlockType(leftover)) {
		return;
	}

	var config = window.publishpressCartCheckoutBlock || {};
	var canonical = config.blockName || 'publishpress-cart/checkout-form';
	var settings = wp.blocks.getBlockType(canonical);
	if (! settings) {
		return;
	}

	var createBlock = wp.blocks.createBlock;
	var title = wp.i18n && typeof wp.i18n.__ === 'function'
		? wp.i18n.__('Checkout (Legacy)', 'publishpress-cart-compat')
		: 'Checkout (Legacy)';
	var supports = settings.supports ? Object.assign({}, settings.supports) : {};
	supports.inserter = false;

	wp.blocks.registerBlockType(
		leftover,
		Object.assign({}, settings, {
			name: leftover,
			title: title,
			supports: supports,
			transforms: {
				to: [
					{
						type: 'block',
						blocks: [ canonical ],
						transform: function (attributes) {
							return createBlock(canonical, attributes);
						}
					}
				]
			}
		})
	);
})();
