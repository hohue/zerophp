jQuery(document).ready(function($) {
	jQuery.validateExtend({
		email : {
			required : true,
			pattern : /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,6}$/
		},

		password : {
			required : true,
			conditional : function(value) {
				return value.length > 7;
			}
		},

		password_confirm : {
			required : true,
			conditional : function(value) {
				return value == $('#fii_password input').val();
			}
		}
	});
});