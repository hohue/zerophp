<?php
//@todo 6 Tach ra 2 file config
return array(
	'css' => array(
        'engine.style' => 'themes/engine/css/style.css',

        'assets.jquery.ui' => 'assets/jquery.ui/css/smoothness/jquery.ui.min.css',
        'assets.colorbox' => 'assets/colorbox/colorbox.css',

        // Chovip
        'theme.reset' => 'css_reset',
        'theme.style' => 'style',
        'theme.bg_gradient' => 'bg_gradient',
        'theme.style_ak' => 'style_ak',
        'theme.responsive' => 'mytheme.responsive',
    ),

    // You can override core assets file: Ex: 'jquery' => 'jquery.2.0.0.min'
    'js' => array(
        //'assets.jquery' => 'assets/jquery/jquery.min.js',
        'assets.once' => 'assets/once/jquery.once.min.js',
        'assets.lazyload' => 'assets/lazyload/jquery.lazyload.min.js',
        'assets.validate' => 'assets/validate/jquery-validate.min.js',

        //'fw.fw' => 'assets/fw/fw.js',
        'fw.ajax' => 'assets/fw/ajax.js',
        'fw.form' => 'assets/fw/form.js',
        'fw.lazyload' => 'assets/fw/lazyload.js',
        'fw.validate-extend' => 'assets/fw/validate-extend.js',
        'fw.validate' => 'assets/fw/validate.js',

        // Chovip
        'assets.jquery.ui' => 'assets/jquery.ui/js/jquery.ui.min.js',
        'assets.tinymce' => 'assets/tinymce/tinymce.min.js',
        'assets.colorbox' => 'assets/colorbox/jquery.colorbox-min.js',

        'fw.tinymce' => 'assets/fw/tinymce.config.js',
        'fw.colorbox' => 'assets/fw/colorbox.js',

        'theme.custom' => 'custom',
    ),

    'regions' => array(
        'header' => 'header',
        'primary menu' => 'primary menu',
        'left sidebar' => 'left sidebar',
        'user panel sidebar' => 'user panel sidebar',
        'right sidebar' => 'right sidebar',
        'secondary menu' => 'secondary menu',
        'footer' => 'footer',

        //Chovip
        'content top' => 'content top',
        'content bottom' => 'content bottom',
        'tags' => 'tags',

        //Admin
        'admin left sidebar' => 'admin left sidebar',
    ),
);
