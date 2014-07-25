<?php

use ZeroPHP\ZeroPHP\Entity;

function zerophp_devel_print($args) {
    $args = func_get_args();
    print '<pre>';
    foreach ($args as $arg) {
        print '<br />';
        print_r($arg);
    }
    print '</pre>';
    die();
}

function zerophp_string_utf8_ascii($text) {
    $text = preg_replace('/[áàảãạâấầẩẫậăắằẳẵặªä]/u', 'a', $text);
    $text = preg_replace('/[ÁÀẢÃẠÂẤẦẨẪẬĂẮẰẲẴẶÄ]/u', 'A', $text);
    $text = preg_replace('/[éèẻẽẹêếềểễệë]/u', 'e', $text);
    $text = preg_replace('/[ÉÈẺẼẸÊẾỀỂỄỆË]/u', 'E', $text);
    $text = preg_replace('/[íìỉĩịîï]/u', 'i', $text);
    $text = preg_replace('/[ÍÌỈĨỊÎÏ]/u', 'I', $text);
    $text = preg_replace('/[óòỏõọôốồổỗộơớờởỡợºö]/u', 'o', $text);
    $text = preg_replace('/[ÓÒỎÕỌÔỐỒỔỖỘƠỚỜỞỠỢÖ]/u', 'O', $text);
    $text = preg_replace('/[úùủũụưứừửữựûü]/u', 'u', $text);
    $text = preg_replace('/[ÚÙỦŨỤƯỨỪỬỮỰÛÜ]/u', 'U', $text);
    $text = preg_replace('/[ýỳỷỹỵ]/u', 'y', $text);
    $text = preg_replace('/[ÝỲỶỸỴ]/u', 'Y', $text);
    $text = preg_replace('/[đ]/u', 'd', $text);
    $text = preg_replace('/[Đ]/u', 'D', $text);

    return $text;
}

function zerophp_uri_validate($text) {

    $text = strip_tags($text); // Strip html & php tag
    $text = zerophp_string_utf8_ascii($text); // Convert utf8 to similar ascii character
    $text = strtolower($text); // Change uppercase to lowercase
    $text = preg_replace('/[^a-z0-9\-_\/]/u', '-', $text); // Replace unexpected character
    // full trim "-" characters
    $text = preg_replace('/(?:(?:^|\n)-+|-+(?:$|\n))/u', '', $text);
    $text = preg_replace('/-+/u', '-', $text);

    return $text;
}

function zerophp_lang($line, $trans = array()) {
        $zerophp = zerophp_get_instance();

        if ($zerophp->language != 'en') {
            if (isset($zerophp->translate[$line])) {
                $line = $zerophp->translate[$line];
            }
            // Insert English line to DB
            else {
                if (!\DB::table('language_translate')->where('en', $line)->first()) {
                    \DB::table('language_translate')->insert(array('en' => $line));
                }
            }
        }

        if (count($trans)) {
            $line = strtr($line, $trans);
        }

        return $line;
}


function form_options_make_weight() {
    $options = array();
    for($i = -99; $i <= 99; $i ++) {
        $options[$i] = $i;
    }

    return $options;
}

function zerophp_static($key, $default_value = null) {
    static $fw_static;

    // $fw_static[$key] can is 'false'/0 but not 'null'
    if(!isset($fw_static[$key]) || $fw_static[$key] === null) {
        $fw_static[$key] = $default_value;
    }

    return $fw_static[$key];
}

function zerophp_view($template, $data = array()) {
    return \View::make($template, $data)->render();
}

//@todo 4 tra ve user id hien tai
function zerophp_user_current() {
    return 0;
}

function zerophp_anchor($url, $title, $attributes = array()) {
    return '<a href="'.\URL::to($url).'">'.$title.'</a>';
}

function zerophp_anchor_shop($url, $title, $attributes = array()) {
    if (zerophp_user_current()) {
        return zerophp_anchor($url, $title, $attributes);
    }

    return zerophp_anchor_popup("ajax/user/login?destination=$url", $title, $attributes);
}

function zerophp_anchor_popup($url, $title, $attributes) {
    $class= 'cboxInline cboxInlineAjax cboxElement';
    $attributes['class'] = isset($attributes['class']) ? $attributes['class'] . ' ' . $class : $class;

    return '<a href="#cboxInlineAjax" data-url="'.\URL::to($url).'" class="'.$attributes['class'].'">'.$title.'</a>';
}

function zerophp_url_current() {
    return zerophp_get_instance()->request->url();
}

function zerophp_is_frontpage() {
    return zerophp_get_instance()->response->isFrontPage();
}

function zerophp_is_userpanel() {
    return zerophp_get_instance()->response->isUserpanel();
}

function zerophp_is_adminpanel() {
    return zerophp_get_instance()->response->isAdminPanel();
}

function zerophp_message() {
    return zerophp_get_instance()->response->getMessage();
}

function &zerophp_get_instance() {
    return \ZeroPHP\ZeroPHP\ZeroPHP::getInstance();
}

function zerophp_flush_cache_view() {
    $cachedViewsDirectory= app('path.storage').'/views/';
    $files = glob($cachedViewsDirectory.'*');
        
        foreach($files as $file)
        {
            if(is_file($file))
            {
                @unlink($file);
            }
        }  
}