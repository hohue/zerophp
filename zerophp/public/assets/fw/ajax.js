(function ($) {
	jQuery.fn.extend({
	    ajax_form_call: function (item, method) {
	    	$('#' + item.wrapper).addClass('spinner');
	    	
	    	$.ajax({
				type: "GET",
				url: "/esi/" + item.path,
				data: $('#' + item.wrapper).parents('form').serialize(),
			}).done(function(data) {
				$('#' + item.wrapper).removeClass('spinner');
				$('#' + item.wrapper)[method](data);
				FW.attachBehaviors();
			});
	    }
	});
		
	FW.behaviors.ajax = {
			attach: function (context, settings) {
				if (settings.AJAX != undefined) {
					$.each(settings.AJAX, function(i, item) {
						var method = 'replaceWith';
						if (item.method != undefined) {
							method = item.method;
						}

						var event = 'change';
						if (item.event != undefined) {
							event = item.event;
						}

						$('#' + i).once().on(event, function() {
							$.fn.ajax_form_call(item, method);
						});
						
						if (item.autoload != undefined && item.autoload) {
							$('#' + i).once('ajax_' + i, function() {
								$.fn.ajax_form_call(item, method);
							});
						}
					});
				}
			}
	};
})(jQuery);