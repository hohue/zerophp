(function ($) {
	FW.behaviors.validate = {
			attach: function (context, settings) {
				$('form').validate({
					onChange : true,
					eachValidField : function() {
						$(this).parent().removeClass('error').addClass('success');
					},
					eachInvalidField : function() {
						$(this).parent().removeClass('success').addClass('error');
					},
					valid : function(event) {
						if ($(this).hasClass('modal')) {
							event.preventDefault();
							$.fn.form_submit_modal($(this).attr('action'), $(this).serialize());
						}
					},
					invalid : function() {
						event.preventDefault();
					}
				});
			}
	};
})(jQuery);