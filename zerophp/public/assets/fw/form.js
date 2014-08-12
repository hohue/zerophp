jQuery(document).ready(function($) {
	$('.form-prefix').form_prefix();
});

jQuery.fn.extend({
	form_prefix: function () {
		var prefix = (this).attr('data-prefix');
		var width = (this).width() + parseInt((this).css('padding-left'));
		var id = 'fpv-' + Math.floor((Math.random()*100) + 1);
		
		(this).parent().append('<span class="form-prefix-value" id="' + id + '">' + prefix + '</span>');
		
		var padding_left = $('#' + id).width() + 3 + parseInt($('#' + id).css('padding-left')) + parseInt($('#' + id).css('padding-right'));
		
		(this).css('padding-left', padding_left + 'px');
		(this).css('width', width - padding_left + 'px');
    }
});