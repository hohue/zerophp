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

function zerophp_userid() {
    if ($id = zerophp_static(__FUNCTION__)) {
        return $id;
    }

    $id = Auth::id();
    return zerophp_static(__FUNCTION__, $id ? $id : 0);
}

function zerophp_user() {
    if ($user = zerophp_static(__FUNCTION__)) {
        return $user;
    }

    $user = new \stdClass();

    if ($userid= zerophp_userid()) {
        $user_obj = \ZeroPHP\ZeroPHP\Entity::loadEntityObject('ZeroPHP\ZeroPHP\Users');
        $user = $user_obj->loadEntity($userid);
    }

    return zerophp_static(__FUNCTION__, $user);
}

function zerophp_anchor($url, $title, $attributes = array()) {
    return '<a href="'.\URL::to($url).'">'.$title.'</a>';
}

function zerophp_anchor_shop($url, $title, $attributes = array()) {
    if (zerophp_userid()) {
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

function zerophp_page_title() {
    return zerophp_get_instance()->response->getPageTitle();
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

function zerophp_is_login() {
    if (\Auth::check() || \Auth::viaRemember()) {
        return true;
    }

    return false;
}

function zerophp_variable_get($key, $default = null) {
    $cache_name = __METHOD__ . $key;
    if ($cache = \Cache::get($cache_name)) {
        return $cache;
    }

    $result =  \ZeroPHP\ZeroPHP\VariableModel::get($key, $default);

    \Cache::forever($cache_name, $result);
    return $result;
}

function zerophp_variable_set($key, $value) {
    return \ZeroPHP\ZeroPHP\VariableModel::set($key, $value);
}

function zerophp_form($template, $data = array()) {
    return \View::make($template, $data)->render();
}

function zerophp_object_to_array($object) {
    return json_decode(json_encode($object), true);
}

/**
* Returns the calling function through a backtrace
*/
function zerophp_get_calling_function() {
  // a funciton x has called a function y which called this
  // see stackoverflow.com/questions/190421
  $caller = debug_backtrace();
  $caller = $caller[2];
  $r = $caller['function'];
  if (isset($caller['class'])) {
    $r = $caller['class'] . '::' . $r;
  }
  return $r;
}

function zerophp_form_render($key, &$form) {
    if (substr($key, 0, 1) != '#') {
        if (isset($form[$key])) {
            $item = $form[$key];
            unset($form[$key]);
        }
        elseif (isset($form['#actions'][$key])) {
            $item = $form['#actions'][$key];
            unset($form['#actions'][$key]);
        }

        if (isset($item)) {
            if (isset($item['theme'])) {
                $template = $item['theme'];
                unset($item['theme']);
            }
            else {
                $template = 'form_item';
            }

            return zerophp_view($template, array('element' => $item));
        }
    }
    
    return '';
}

function zerophp_form_render_all(&$form) {
    $result = '';
    foreach ($form as $key => $value){
        $result .= zerophp_form_render($key, $form);
    }

    return $result;
}