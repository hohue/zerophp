
var FW = FW || { 'settings': {}, 'behaviors': {}};

(function ($) {
	/**
	 * Attach all registered behaviors to a page element.
	 *
	 * Behaviors are event-triggered actions that attach to page elements, enhancing
	 * default non-JavaScript UIs. Behaviors are registered in the FW.behaviors
	 * object using the method 'attach' and optionally also 'detach' as follows:
	 * @code
	 *    FW.behaviors.behaviorName = {
	 *      attach: function (context, settings) {
	 *        ...
	 *      },
	 *      detach: function (context, settings, trigger) {
	 *        ...
	 *      }
	 *    };
	 * @endcode
	 *
	 * FW.attachBehaviors is added below to the jQuery ready event and so
	 * runs on initial page load. Developers implementing AHAH/Ajax in their
	 * solutions should also call this function after new page content has been
	 * loaded, feeding in an element to be processed, in order to attach all
	 * behaviors to the new content.
	 *
	 * Behaviors should use
	 * @code
	 *   $(selector).once('behavior-name', function () {
	 *     ...
	 *   });
	 * @endcode
	 * to ensure the behavior is attached only once to a given element. (Doing so
	 * enables the reprocessing of given elements, which may be needed on occasion
	 * despite the ability to limit behavior attachment to a particular element.)
	 *
	 * @param context
	 *   An element to attach behaviors to. If none is given, the document element
	 *   is used.
	 * @param settings
	 *   An object containing settings for the current context. If none given, the
	 *   global FW.settings object is used.
	 */
	FW.attachBehaviors = function (context, settings) {
		context = context || document;
		settings = settings || FW.settings;
		// Execute all of them.
		$.each(FW.behaviors, function () {
			if ($.isFunction(this.attach)) {
				this.attach(context, settings);
			}
		});
	};

	/**
	 * Detach registered behaviors from a page element.
	 *
	 * Developers implementing AHAH/Ajax in their solutions should call this
	 * function before page content is about to be removed, feeding in an element
	 * to be processed, in order to allow special behaviors to detach from the
	 * content.
	 *
	 * Such implementations should look for the class name that was added in their
	 * corresponding FW.behaviors.behaviorName.attach implementation, i.e.
	 * behaviorName-processed, to ensure the behavior is detached only from
	 * previously processed elements.
	 *
	 * @param context
	 *   An element to detach behaviors from. If none is given, the document element
	 *   is used.
	 * @param settings
	 *   An object containing settings for the current context. If none given, the
	 *   global FW.settings object is used.
	 * @param trigger
	 *   A string containing what's causing the behaviors to be detached. The
	 *   possible triggers are:
	 *   - unload: (default) The context element is being removed from the DOM.
	 *   - move: The element is about to be moved within the DOM (for example,
	 *     during a tabledrag row swap). After the move is completed,
	 *     FW.attachBehaviors() is called, so that the behavior can undo
	 *     whatever it did in response to the move. Many behaviors won't need to
	 *     do anything simply in response to the element being moved, but because
	 *     IFRAME elements reload their "src" when being moved within the DOM,
	 *     behaviors bound to IFRAME elements (like WYSIWYG editors) may need to
	 *     take some action.
	 *   - serialize: When an Ajax form is submitted, this is called with the
	 *     form as the context. This provides every behavior within the form an
	 *     opportunity to ensure that the field elements have correct content
	 *     in them before the form is serialized. The canonical use-case is so
	 *     that WYSIWYG editors can update the hidden textarea to which they are
	 *     bound.
	 *
	 * @see FW.attachBehaviors
	 */
	FW.detachBehaviors = function (context, settings, trigger) {
		context = context || document;
		settings = settings || FW.settings;
		trigger = trigger || 'unload';
		// Execute all of them.
		$.each(FW.behaviors, function () {
			if ($.isFunction(this.detach)) {
				this.detach(context, settings, trigger);
			}
		});
	};
	
	//	Attach all behaviors.
	$(function () {
		FW.attachBehaviors(document, FW.settings);
	});
	
	$.fn.form_submit_modal = function(url, data) {
		$('.modal_html_return .overlay').show('slow');

		$.ajax({
			type: "POST",
			url: url,
			data: data,
			success: function(response) {
				// JSON
				if (typeof response == "object" && response.form_redirect != undefined) {
					window.location.replace(window.location.protocol + "//" + window.location.host + "/" + response.form_redirect);
				}
				// HTML
				else {
					$('.modal_html_return .overlay').hide('slow');

					$('.modal_html_return').replaceWith(response);
					if ($.colorbox != undefined) {
						$.colorbox.resize();
					}
					FW.attachBehaviors();
				}
			}
		});
	}
	
	$.fn.uri_validate = function(text) {
		text = $.fn.string_utf8_ascii(text); // Convert utf8 to similar ascii character
		text = text.toLowerCase(); // Change uppercase to lowwercase
		text = text.replace(/[^a-z0-9\-_\/]/g, '-'); // Replace unexpected character
		text = text.replace(/(?:(?:^|\n)-+|-+(?:$|\n))/g,'').replace(/-+/g,'-'); // full trim "-" characters
		$('input.url_alias').val(text);
	}

	$.fn.string_utf8_ascii = function(text) {
		text = text.replace(/[áàảãạâấầẩẫậăắằẳẵặªä]/g, 'a');
		text = text.replace(/[ÁÀẢÃẠÂẤẦẨẪẬĂẮẰẲẴẶÄ]/g, 'A');
		text = text.replace(/[éèẻẽẹêếềểễệë]/g, 'e');
		text = text.replace(/[ÉÈẺẼẸÊẾỀỂỄỆË]/g, 'E');
		text = text.replace(/[íìỉĩịîï]/g, 'i');
		text = text.replace(/[ÍÌỈĨỊÎÏ]/g, 'I');
		text = text.replace(/[óòỏõọôốồổỗộơớờởỡợºö]/g, 'o');
		text = text.replace(/[ÓÒỎÕỌÔỐỒỔỖỘƠỚỜỞỠỢÖ]/g, 'O');
		text = text.replace(/[úùủũụưứừửữựûü]/g, 'u');
		text = text.replace(/[ÚÙỦŨỤƯỨỪỬỮỰÛÜ]/g, 'U');
		text = text.replace(/[ýỳỷỹỵ]/g, 'y');
		text = text.replace(/[ÝỲỶỸỴ]/g, 'Y');
		text = text.replace(/[Đ]/g, 'D');
		text = text.replace(/[đ]/g, 'd');
		
		return text;
	}
	
	// URL alias
	if ($('input.url_alias').length
		&& $('input.url_alias').attr('disabled') == undefined
	) {
		$('input[name=title]').on('change', function(){
			$.fn.uri_validate($(this).val());
		});
		
		$("form").submit(function(event) {
			var text = $('input.url_alias').val();
			if (!text.length || text == ' ') {
				text = $('input[name=title]').val();
			}
			$.fn.uri_validate(text);
		});
	}
})(jQuery);