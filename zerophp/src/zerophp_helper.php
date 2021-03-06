<?php

use ZeroPHP\ZeroPHP\Entity;

function zerophp_config_get($key, $default_value = '') {
    return \Config::get("packages/zerophp/zerophp/$key", $default_value);
}

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

function form_options_day() {
    $options = array(
        '' => zerophp_lang('Day'),
    );
    for($i = 1; $i <= 31; $i ++) {
        $options[$i] = $i;
    }

    return $options;
}

function form_options_month() {
    $options = array(
        '' => zerophp_lang('Month'),
    );
    for($i = 1; $i <= 12; $i ++) {
        $options[$i] = $i;
    }

    return $options;
}

function form_options_year($min = null, $max = null) {
    $min = $min ? $min : date('Y') - 100;
    $max = $max ? $max : date('Y') + 100;

    $options = array(
        '' => zerophp_lang('Year'),
    );
    for($i = $min; $max <= 12; $i ++) {
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

    if ($userid = zerophp_userid()) {
        $user_obj = new \ZeroPHP\ZeroPHP\Users;
        $user = $user_obj->loadEntity($userid);
    }
    else {
        $user->id = 0;
        $user->roles[] = 1; // Anonymous user role
    }

    return zerophp_static(__FUNCTION__, $user);
}

function zerophp_anchor($url, $title, $attributes = array()) {
    return '<a href="'.zerophp_url($url).'" ' . \HTML::attributes($attributes) . '>'.$title.'</a>';
}

function zerophp_anchor_login($url, $title, $attributes = array()) {
    if (zerophp_userid()) {
        return zerophp_anchor($url, $title, $attributes);
    }

    return zerophp_anchor_popup("modal/user/login?destination=$url", $title, $attributes);
}

function zerophp_anchor_popup($url, $title, $attributes = array()) {
    $class= 'cboxInline cboxInlineAjax cboxElement';
    $attributes['class'] = isset($attributes['class']) ? $attributes['class'] . ' ' . $class : $class;

    return '<a href="#cboxInlineAjax" data-url="' . zerophp_url($url) . '" ' . \HTML::attributes($attributes) . '>'.$title.'</a>';
}

function zerophp_url_current() {
    $zerophp = zerophp_get_instance();
    
    $prefix = $zerophp ->request->prefix();
    $url = $zerophp->request->url();
    $url = $prefix ? "$prefix/$url" : $url;
    return $url;
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
    $cachedViewsDirectory = app('path.storage').'/views/';
    $files = glob($cachedViewsDirectory.'*');
        
        foreach($files as $file) {
            if(is_file($file)) {
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

function zerophp_object_to_array($object) {
    return json_decode(json_encode($object), true);
}

/**
* Returns the calling function through a backtrace
*/
/*function zerophp_get_calling_function() {
  // a funciton x has called a function y which called this
  // see stackoverflow.com/questions/190421
  $caller = debug_backtrace();

  zerophp_devel_print($caller);


  $caller = $caller[2];
  $r = $caller['function'];
  if (isset($caller['class'])) {
    $r = $caller['class'] . '::' . $r;
  }
  return $r;
}*/

function zerophp_form_render($key, &$form, $subkey = null) {
    //zerophp_devel_print($key, $form);
    if (substr($key, 0, 1) != '#') {
        if (isset($form[$key])) {
            $item = $form[$key];
            unset($form[$key]);
        }
        elseif (isset($form['#actions'][$key])) {
            $item = $form['#actions'][$key];
            unset($form['#actions'][$key]);
        }
        elseif (!empty($form['#table'][$key][$subkey])) {
            $item = $form['#table'][$key][$subkey];
            unset($form['#table'][$key][$subkey]);
        }

        if (isset($item)) {
            if (isset($item['#theme'])) {
                $template = $item['#theme'];
                unset($item['#theme']);
            }
            else {
                $template_collection = array();
                if (isset($form['#id'])) {
                    $template_collection[] = 'form_item-' . $item['#type'] . '-' . $item['#name'] . '-' . $form['#id'];
                }
                $template_collection[] = 'form_item-' . $item['#type'] . '-' . $item['#name'];
                $template_collection[] = 'form_item-' . $item['#type'];
                $template_collection[] = 'form_item';

                $template = array_shift($template_collection);
                //($template, $template_collection);
                while (!\View::exists($template) && count($template_collection)) {
                    $template = array_shift($template_collection);
                }
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

function zerophp_url($path = '', $query = '', $attributes = array(), $secure = false) {
    $path = $path ? $path : zerophp_url_current();
    $path .= $query ? "?$query" : '';

    return \URL::to($path, $attributes, $secure);
}

function zerophp_redirect($url = '') {
    $url = trim(zerophp_redirect_get_path($url), '/');
    $url = $url ? $url : '/';

    return \Redirect::to($url);
}

function zerophp_redirect_get_path($url = '') {
    if ($redirect = zerophp_get_instance()->request->query('destination')) {
        $url = $redirect;
    }

    return '/' . $url;
}

if (!function_exists('template_item_list')) {
    function template_item_list($items, $level = 1) {
        $result = '<ul class="items-level-' . $level . '">';
            foreach ($items as $value) {
                $result .= '<li>';
                    if (isset($value['#item'])) {
                        $result .= $value['#item'];
                    }

                    if (isset($value['#children'])) {
                        $result .= template_item_list($value['#children'], $level++);
                    }

                $result .= '</li>';
            }
        $result .= '</ul>';

        return $result;
    }
}

if (!function_exists('template_tree_build')) {
    function template_tree_build($tree) {
        $result = array();

        $tree = zerophp_object_to_array($tree);

        foreach ($tree as $key => $value) {
            if (!empty($value['#parent'])) {
                if (is_array($value['#parent'])) {
                    $parent = reset(array_keys($value['#parent']));
                }
                else {
                    $parent = $value['#parent'];
                }
                unset($value['#parent']);

                if (!isset($result[$parent]['#children'])) {
                    $result[$parent]['#children'] = array();
                }

                $result[$parent]['#children'][] = $value;
            }
            else {
                if (isset($value['#parent'])) {
                    unset($value['#parent']);
                }
                $result[$key] = $value;
            }
        }

        return $result;
    }
}

if (!function_exists('template_tree_build_option')) {
    function template_tree_build_option($tree, $parent = 0, $load_children = true, $level = 0) {
        $result = array();
        $tree = template_tree_build($tree);

        
        if ($parent) {
            if(!empty($tree[$parent]['#children'])) {
                $tree = $tree[$parent]['#children'];
            }
            else {
                return $result;
            }
        }

        $prefix = '---';
        foreach ($tree as $key => $value) {
            if (isset($value['#title'])) {
                $i = $level;
                while ($i > 0) {
                    $value['#title'] = $prefix . $value['#title'];
                    $i--;
                }
            }

            $result[$key] = $value;

            if ($load_children) {
                if (isset($value['#children']) && count($value['#children'])) {
                    $result = array_merge($result, template_tree_build_option($value['#children'], $parent, $load_children, $level+1));
                    $level-1;
                }
            }
        }

        return $result;
    }
}

function zerophp_mail($email, $subject, $body, $template = 'email') {
    $email = explode('|', $email);
    $email[1] = isset($email[1]) ? $email[1] : '';

    zerophp_static('zerophp_mail', array(
        'email' => $email,
        'subject' => $subject,
    ));

    \Mail::send($template, array('body' => $body), function($message) {
        $attributes = zerophp_static('zerophp_mail');
        $message->to($attributes['email'][0], $attributes['email'][1])->subject($attributes['subject']);
    });
}

function zerophp_form_get_rte() {
    global $rte;

    if (empty($rte)) {
        $rte = 'rte';
    }
    else {
        $next = substr($rte, 3);
        $next = $next ? intval($next) + 1 : 1;
        $rte = "rte$next";
    }

    return $rte;
}

function zerophp_file_get_filename($file, $path) {
    if (!\File::isDirectory($path)) {
        \File::makeDirectory($path);
    }

    // Get file name
    $file_extension = $file->getClientOriginalExtension();
    $file_name = $file->getClientOriginalName();
    $file_name = zerophp_uri_validate(str_replace($file_extension, '', $file_name));
    $result = "$file_name.$file_extension";
    while (\File::exists("$path/$result")) {
        $result = "$file_name-" . strtolower(\Str::random(4)) . ".$file_extension";
    }

    return $result;
}

function zerophp_image_style($path, $style = 'normal', $attributes = array()) {
    $image = new \ZeroPHP\ZeroPHP\ImageStyle;
    $image = $image->image($path, $style);

    if (!isset($image['path'])) {
        return '';
    }

    if (!isset($attributes['class'])) {
        $attributes['class'] = '';
    }

    $attributes['width'] = isset($attributes['width']) ? $attributes['width'] : (isset($image['width']) ? $image['width'] : '');
    $attributes['height'] = isset($attributes['height']) ? $attributes['height'] : (isset($image['height']) ? $image['height'] : '');

    if (zerophp_variable_get('image lazy load', 1)) {
        $src = 'data-original="' . $image['path'] . '"';
        $attributes['class'] .= 'lazy loading';
    }
    else {
        $src = 'src="' . $image['path'] . '"';
    }

    $attr = \HTML::attributes(array_filter($attributes));

    return "<img $src $attr  />";
}

function zerophp_form_get_type() {
    return array(
        0 => array('radios', 'checkboxes', 'markup', 'date'),
        1 => array('checkbox', 'radio'), //Form::type($name, $value, $checked, $attributes)
        2 => array('password', 'file'), //Form::type($name, $attributes)
        3 => array('select'), // Form::type($name, $options, $value, $attributes)
        4 => array('submit', 'button', 'reset'), //Form::type($value, $attributes)
        //default //Form::type($name, $value, $attributes)
    );
}

function zerophp_form_content($element) {
    $type = zerophp_form_get_type();

    switch ($element['#type']) {
        case 'checkboxes':
            $checkboxes = '[]';
            $form_type = 'checkbox';
        case 'radios':
            $form_type = isset($form_type) ? $form_type : 'radio';
            $result = '';
            foreach ($element['#options'] as $key => $value) {
                $result .= \Form::$form_type($element['#name'] . (isset($checkboxes) ? $checkboxes : ''), $key, ($key == $element['#value'] ? true : false), $element['#attributes']);
                $result .= "<sub_label>$value</sub_label>";
            }
            return $result;

        case 'markup':
            return $element['#value'];

        case 'date':
            $result = '';
            $value = $element['#value'] ? strtotime($element['#value']) : '';
            //zerophp_devel_print($value);
            switch ($element['#config']['form_type']) {
                case 'select_group':
                    switch ($element['#config']['group_format']) {
                        // dmY - day is select, month is select, year is text
                        default:
                            $result .= \Form::select(
                                $element['#name'] . '[day]', 
                                form_options_day(), 
                                $value ? date('d', $value) : '', 
                                isset($element['#attributes']['day']) ? $element['#attributes']['day'] : array());
                            $result .= \Form::select(
                                $element['#name'] . '[month]',
                                form_options_month(), 
                                $value ? date('m', $value) : '', 
                                isset($element['#attributes']['month']) ? $element['#attributes']['month'] : array());
                            $result .= \Form::text(
                                $element['#name'] . '[year]', 
                                $value ? date('Y', $value) : '', 
                                isset($element['#attributes']['year']) ? $element['#attributes']['year'] : array());
                            break;
                    }
                    break;
                
                //datepicker
                default:
                    # code...
                    break;
            }
            return $result;

        case in_array($element['#type'], $type[1]):
            return \Form::{$element['#type']}($element['#name'], $element['#value'], $element['#checked'], $element['#attributes']);
        
        case in_array($element['#type'], $type[2]):
            return \Form::{$element['#type']}($element['#name'], $element['#attributes']);
        
        case in_array($element['#type'], $type[3]):
            return \Form::{$element['#type']}($element['#name'], $element['#options'], $element['#value'], $element['#attributes']);
        
        case in_array($element['#type'], $type[4]):
            return \Form::{$element['#type']}($element['#value'], $element['#attributes']);

        default:
            return \Form::{$element['#type']}($element['#name'], $element['#value'], $element['#attributes']);
    }
}

function zerophp_paganization($current, $sum) {
    $prev = max(1, $current - 1);
    $next = min($sum, $current + 1);

    $data = array(
        'current' => $current,
        'first' => 1,
        'prev' => $prev,
        'next' => $next,
        'last' => $sum,
        'sum' => $sum,
        'item' => zerophp_variable_get('paganization items', 5),
    );

    return zerophp_view('paganization', $data);
}



    /**
     * From Drupal 7
     *
     * Returns the ancestors (and relevant placeholders) for any given path.
     *
     * For example, the ancestors of node/12345/edit are:
     * - node/12345/edit
     * - node/12345/%
     * - node/%/edit
     * - node/%/%
     * - node/12345
     * - node/%
     * - node
     *
     * To generate these, we will use binary numbers. Each bit represents a
     * part of the path. If the bit is 1, then it represents the original
     * value while 0 means wildcard. If the path is node/12/edit/foo
     * then the 1011 bitstring represents node/%/edit/foo where % means that
     * any argument matches that part. We limit ourselves to using binary
     * numbers that correspond the patterns of wildcards of router items that
     * actually exists. This list of 'masks' is built in menu_rebuild().
     *
     * @param $parts
     *   An array of path parts; for the above example, 
     *   array('node', '12345', 'edit').
     *
     * @return
     *   An array which contains the ancestors and placeholders. Placeholders
     *   simply contain as many '%s' as the ancestors.
     */
    function zerophp_menu_ancestors($parts) {
      $number_parts = count($parts);
      $ancestors = array();
      $length =  $number_parts - 1;
      $end = (1 << $number_parts) - 1;
      //$masks = variable_get('menu_masks');
      // If the optimized menu_masks array is not available use brute force to get
      // the correct $ancestors and $placeholders returned. Do not use this as the
      // default value of the menu_masks variable to avoid building such a big
      // array.
      //if (!$masks) {
        $masks = range(511, 1);
      //}
      // Only examine patterns that actually exist as router items (the masks).
      foreach ($masks as $i) {
        if ($i > $end) {
          // Only look at masks that are not longer than the path of interest.
          continue;
        }
        elseif ($i < (1 << $length)) {
          // We have exhausted the masks of a given length, so decrease the length.
          --$length;
        }
        $current = '';
        for ($j = $length; $j >= 0; $j--) {
          // Check the bit on the $j offset.
          if ($i & (1 << $j)) {
            // Bit one means the original value.
            $current .= $parts[$length - $j];
          }
          else {
            // Bit zero means means wildcard.
            $current .= '%';
          }
          // Unless we are at offset 0, add a slash.
          if ($j) {
            $current .= '/';
          }
        }
        $ancestors[] = $current;
      }
      return $ancestors;
    }