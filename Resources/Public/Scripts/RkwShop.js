var RkwShop = RkwShop || {};

RkwShop.handle = (function ($) {

	var $orderContainer;

	var _init = function(){
		$(document).ready(_onReady);
	};

	var _onReady = function(){
		$orderContainer = $('#rkw-order-container');
		_getContent();

	};

	var _getContent = function(e){

		if ($orderContainer.attr('data-url')) {

			var url =  $orderContainer.attr('data-url');
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
							for (var property in json) {

								if (property === 'html') {

									var htmlObject = json[property];
									for (parent in htmlObject) {

										targetObject = jQuery('#' + parent);
										if (targetObject.length) {
											for (var method in htmlObject[parent]) {
												if (method === 'append') {
													var newContent = jQuery(htmlObject[parent][method]).appendTo(targetObject);
                                                    // jQuery(document).trigger('ajax-api-content-changed', newContent);
													jQuery(document).trigger('rkw-ajax-api-content-changed', newContent);

                                                } else
												if (method === 'prepend') {
                                                    var newContent = jQuery(htmlObject[parent][method]).prependTo(targetObject);
                                                    // jQuery(document).trigger('ajax-api-content-changed', newContent);
													jQuery(document).trigger('rkw-ajax-api-content-changed', newContent);
												} else
												if (method === 'replace') {
													targetObject.empty();
                                                    var newContent = jQuery(htmlObject[parent][method]).prependTo(targetObject);
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
