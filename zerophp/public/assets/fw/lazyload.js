jQuery(document).ready(function($) {
	$("img.lazy").lazyload();
	
	$(".lazyAjax").lazyAjax();
	
	$(".loadAjax").on('click', function(){
		$.fn.lazyAjax('#' + $(this).attr('data-content'));
	});
});

jQuery.fn.extend({
	lazyAjax: function (selector) {
		this.each(function() {
			if (selector == undefined) {
				var selector = '#' + $(this).attr('id');
			}
			
			$.ajax({
				url: $(selector).attr('data-url')
			}).done(function(data) {
				$(selector).html(data);
				
				FW.attachBehaviors();
			});
		});
    }
});
