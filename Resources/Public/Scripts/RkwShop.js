const RkwShop = RkwShop || {};

RkwShop.handle = (function ($) {

    let $orderContainer;

    let $quantities;

    let $sameAsBilling;

    let $shippingAddressForm;

    const _init = function () {
        $(document)
            .ready(_onReady);
    };

    let _onReady = function(){
		$orderContainer = $('#rkw-order-container');
		_getContent();

		//	listen to cart quantity update
		$quantities = $('.js-order-list .quantity');
		if ($quantities.length > 0) {
			_changeQuantity();
		}

		$sameAsBilling = $('.js-same-as-billing');
		if ($sameAsBilling.length > 0) {
            $shippingAddressForm = $('.js-shipping-address-form');

		    _showShippingAddressForm();
        }
	};

	let _changeQuantity = function() {

		$quantities.on('change', function () {
            const changeUrl = $(this)
                .closest('.order-list__item')
                .find('.js-change-quantity-url')
                .val()
            ;
            window.location.href = changeUrl + encodeURI('&tx_rkwshop_cart[amount]=') + $(this).val();
		});

	};

	let _showShippingAddressForm = function() {

	    $sameAsBilling.on('change', function () {

            const $jcfCheckbox = $(this)
                .closest('.jcf-checkbox');

            ($jcfCheckbox.hasClass('jcf-checked')) ? $shippingAddressForm.removeClass('hide') : $shippingAddressForm.addClass('hide');
        });

    };

	let _getContent = function(e){

		if ($orderContainer.attr('data-url')) {

            let url = $orderContainer.attr('data-url');
            if($orderContainer.attr('data-url').indexOf('?') === -1){
				url += '?v=' + jQuery.now();
			} else {
				url += '&v=' + jQuery.now();
			}

			jQuery.ajax({
				url: url,
				data: {
					'tx_rkwshop_itemlist[controller]': 'Order',
					'tx_rkwshop_itemlist[action]': 'newAjax'
				},
				success: function (json) {

					try {
						if (json) {
							for (let property in json) {

								if (property === 'html') {

                                    const htmlObject = json[property];
                                    for (parent in htmlObject) {

										targetObject = jQuery('#' + parent);
										if (targetObject.length) {
											for (let method in htmlObject[parent]) {
												if (method === 'append') {
													let newContent = jQuery(htmlObject[parent][method]).appendTo(targetObject);
                                                    // jQuery(document).trigger('ajax-api-content-changed', newContent);
													jQuery(document).trigger('rkw-ajax-api-content-changed', newContent);

                                                } else
												if (method === 'prepend') {
                                                    let newContent = jQuery(htmlObject[parent][method]).prependTo(targetObject);
                                                    // jQuery(document).trigger('ajax-api-content-changed', newContent);
													jQuery(document).trigger('rkw-ajax-api-content-changed', newContent);
												} else
												if (method === 'replace') {
													targetObject.empty();
                                                    let newContent = jQuery(htmlObject[parent][method]).prependTo(targetObject);
                                                    // jQuery(document).trigger('ajax-api-content-changed', newContent);
													jQuery(document).trigger('rkw-ajax-api-content-changed', newContent);
												}
											}
										}
									}
								}
							}
						}
					} catch (error) {}
				},
				dataType: 'json'
			});
		}
	};

	/**
	 * Public interface
	 * @public
	 */
	return {
		init: _init,
	}

})(jQuery);

RkwShop.handle.init();
