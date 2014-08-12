<?php
//@todo 6 Tach ra 2 file config
return array(
	'css' => array(
        'engine.style' => '/packages/zerophp/zerophp/themes/engine/css/style.css',

        'assets.colorbox' => '/packages/zerophp/zerophp/assets/colorbox/colorbox.css',
    ),

    'js' => array(
        'assets.jquery' => '/packages/zerophp/zerophp/assets/jquery/jquery.min.js',

        'assets.once' => '/packages/zerophp/zerophp/assets/once/jquery.once.min.js',
        'assets.lazyload' => '/packages/zerophp/zerophp/assets/lazyload/jquery.lazyload.min.js',
        'assets.validate' => '/packages/zerophp/zerophp/assets/validate/jquery-validate.min.js',

        'fw.fw' => '/packages/zerophp/zerophp/assets/fw/fw.js',
        'fw.ajax' => '/packages/zerophp/zerophp/assets/fw/ajax.js',
        'fw.form' => '/packages/zerophp/zerophp/assets/fw/form.js',
        'fw.lazyload' => '/packages/zerophp/zerophp/assets/fw/lazyload.js',
        'fw.validate-extend' => '/packages/zerophp/zerophp/assets/fw/validate-extend.js',
        'fw.validate' => '/packages/zerophp/zerophp/assets/fw/validate.js',

        'assets.tinymce' => '/packages/zerophp/zerophp/assets/tinymce/tinymce.min.js',
        'fw.tinymce' => '/packages/zerophp/zerophp/assets/fw/tinymce.config.js',

        'assets.colorbox' => '/packages/zerophp/zerophp/assets/colorbox/jquery.colorbox-min.js',
        'fw.colorbox' => '/packages/zerophp/zerophp/assets/fw/colorbox.js',
    ),

    'regions' => array(
        'header' => 'header',
        'primary menu' => 'primary menu',
        'left sidebar' => 'left sidebar',
        'user panel sidebar' => 'user panel sidebar',
        'right sidebar' => 'right sidebar',
        'secondary menu' => 'secondary menu',
        'footer' => 'footer',

        //Admin
        'admin menu' => 'admin menu',
    ),
);
