(function ($) {
	$('<div id="cboxInlineAjax" class="invisible"></div>').appendTo('body');
	
	$(".cboxGallery").colorbox({rel:'cboxGallery'});
	$(".cboxOutside").colorbox();
	
	FW.behaviors.colorbox = {
			attach: function (context, settings) {
				$(".cboxInline").colorbox({
					inline:true,
					maxWidth: "100%",
					minHeight: "100%",
					maxHeight: "100%",
					width: "600px",
					onComplete: function() { 
						$(this).colorbox.resize({
							width: $('#cboxLoadedContent').width() - 10
						});
					} 
				});
				
				$(".cboxInlineAjax").on("click", function() {
					$.ajax({
						url: $(this).attr('data-url')
					}).done(function(data) {
						$('#cboxInlineAjax').html(data);
						FW.attachBehaviors();
					});
				});
			}
	};
})(jQuery);