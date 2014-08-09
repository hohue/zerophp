<?php 
namespace ZeroPHP\ZeroPHP;

use ZeroPHP\ZeroPHP\Entity;

class Form {
    public static function build($form = array(), $form_values = array()) {
        $form_id = zerophp_uri_validate(zerophp_get_calling_function());
        $form_url = \Input::get('_form_url', md5(time() . csrf_token() . zerophp_get_instance()->request->url()));

        // Rebuild from error form
        $cache_name_error_form = __CLASS__ . "-build-$form_id-$form_url";
        if ($cache_value = \Cache::get($cache_name_error_form)) {
            \Cache::forget($cache_name_error_form);
            $form = $cache_value;
        }
        else {
            $cache_name = __METHOD__ . $form_id;
            if ($cache_value = \Cache::get($cache_name)) {
                $form = $cache_value;
            }
            else {
                $form['arguments'] = isset($form['arguments']) ? (array) $form['arguments'] : array();
                $form = call_user_func_array(array(new $form['class'], $form['method']), $form['arguments']);

                $form['#id'] = $form_id;
                $form['_form_id'] = array(
                    '#name' => '_form_id',
                    '#type' => 'hidden',
                    '#value' => $form_id,
                    '#disabled' => true,
                );

                //zerophp_devel_print($form);

                // Call form_alter functions
                self::_alter($form_id, $form);

                \Cache::forever($cache_name, $form);
            }

            // Call form_value_alter functions
            $form_values = zerophp_object_to_array($form_values);
            self::_alter($form_id, $form, $form_values, 'form_value_alter');

            // Set default value for form
            self::_setValues($form_id, $form, $form_values);
        }

        // Create cache to use when form submitted
        \Cache::put($cache_name_error_form, $form, \Config::get('session.lifetime', 120));

        $form['_form_url'] = array(
            '#name' => '_form_url',
            '#type' => 'hidden',
            '#value' => $form_url,
            '#disabled' => true,
        );
        self::_build($form_id, $form);

        return zerophp_view($form['#theme'], array('form' => $form));
    }

    private static function _alter($form_id, &$form, &$form_values = array(), $type = 'form_alter') {
        $form_alter_list = new \ZeroPHP\ZeroPHP\Hook;
        $form_alter_list = array_merge($form_alter_list->loadEntityAllByHookType($type), $form_alter_list->loadEntityAllByHookType($type, $form_id));

        foreach ($form_alter_list as $alter) {
            $hook = new $alter->class;
            if ($type == 'form_value_alter') {
                $hook->{$alter->method}($form_id, $form, $form_values);
            }
            else {
                $hook->{$alter->method}($form_id, $form);
            }
        }
    }

    private static function _build($form_id, &$form) {
        // Set form attributes default
        $form['#form'] = isset($form['#form']) ? $form['#form'] : array();
        $form['#theme'] = isset($form['#theme']) ? $form['#theme'] : 'form';
        $form['#actions'] = isset($form['#actions']) ? $form['#actions'] :  array();
        $form['#variable'] = isset($form['#variable']) ? $form['#variable'] :  array();
        $form['#error'] = isset($form['#error']) ? zerophp_object_to_array($form['#error']) :  array();
        $form['#message'] = isset($form['#message']) ? $form['#message'] :  array();

        // $form['#success'] is highest priority
        if (isset($form['#success'])) {
            $form['#success_message'] = '';
        }

        // Add class "modal" for modal form
        if (zerophp_get_instance()->response->getOutputType() == 'modal') {
            if (!isset($form['#form']['class'])) {
                $form['#form']['class'] = '';
            }
            $form['#form']['class'] .= ' modal';
        }
        elseif (isset($form['#form']['class'])) {
            $form['#form']['class'] = str_replace(' modal', '', $form['#form']['class']);

            if (empty($form['#form']['class'])) {
                unset($form['#form']['class']);
            }
        }

        // Move submit to $form['actions']
        if (isset($form['submit'])) {
            $form['#actions']['submit'] = $form['submit'];
            unset($form['submit']);
        }

        foreach ($form as $key => $value) {
            // Don't care with #validate, #submit...
            if (substr($key, 0, 1) != '#') {
                if (isset($form['#error'][$key]) && zerophp_variable_get('form error message show in field', 1)) {
                    if (!isset($value['#error_messages'])) {
                        $value['#error_messages'] = '';
                    }
                    else {
                        $value['#error_messages'] .= '<br />';
                    }
                    $value['#error_messages'] .= implode('<br />', $form['#error'][$key]);

                    if (!isset($value['#class'])) {
                        $value['#class'] = '';
                    }
                    $value['#class'] .= ' error';

                    unset($form['#error'][$key]);
                }

                $form[$key] = self::buildItem($value);
            }
            elseif ($key == '#actions') {
                foreach ($value as $k => $v) {
                    $form[$key][$k] = self::buildItem($v);
                }
            }
            elseif ($key == '#table') {
                foreach ($value as $k => $v) {
                    foreach ($v as $k1 => $v1) {
                        $form[$key][$k][$k1] = self::buildItem($v1);
                    }
                }
            }
        }
    }

    public static function buildItem($item) {

        // Normal field
        $item['#id'] = isset($item['#id']) ? $item['#id'] : 'fii_' . $item['#name']; // fii = form item id
        $item['#class'] = 'form_item form_item_' . $item['#type'] . ' form_item_' . $item['#name'] . (isset($item['#class']) ? ' ' . $item['#class'] : '');
        $item['#value'] = isset($item['#value']) ? $item['#value'] : '';
        $item['#attributes'] = isset($item['#attributes']) ? $item['#attributes'] : array();
        $item['#attributes']['id'] = isset($item['#attributes']['id']) ? $item['#attributes']['id'] : $item['#id'] . '_field';

        // Special field
        switch ($item['#type']) {
            case 'checkbox':
            case 'radio':
                $item['#checked'] = isset($item['#checked']) ? $item['#checked'] : false;
                break;

            case 'select':
            case 'radios':
            case 'checkboxes':
                $item['#options'] = isset($item['#options']) ? $item['#options'] : array();
                break;

            case 'textarea':
                if (isset($item['#rte_enable']) && $item['#rte_enable']) {
                    $item['#attributes']['id'] = zerophp_form_get_rte();

                    //@todo 9 Hack for SEO
                    $item['#value'] = str_replace('data-original', 'src', $item['#value']);
                }
                break;

            case 'date':
                if (empty($item['#config']['form_type'])) {
                    $item['#config']['form_type'] = 'datepicker';
                }

                switch ($item['#config']['form_type']) {
                    case 'select_group':
                        if (empty($item['#config']['group_format'])) {
                            $item['#config']['group_format'] = 'dmY';
                        }
                        break;
                }

                break;
        }

        //Reference Options
        if (!empty($item['#options_callback'])) {
            $item['#options_callback']['arguments'] = isset($item['#options_callback']['arguments']) ? (array) $item['#options_callback']['arguments'] : array();
            $item['#options'] = call_user_func_array(array(new $item['#options_callback']['class'], $item['#options_callback']['method']), $item['#options_callback']['arguments']);
        }

        // AJAX
        if (isset($item['#ajax'])) {
            $js = array(
                'AJAX' => array(
                    $item['#attributes']['id'] => $item['#ajax'],
                ),
            );
            zerophp_get_instance()->response->addJS($js, 'settings');
        }

        return $item;
    }

    private static function _setValues($form_id, &$form, $form_values = array()) {
        foreach ($form_values as $key => $value) {
            if (isset($form[$key])) {
                switch ($form[$key]['#type']) {
                    // Do not tracking password field
                    case 'password':
                        break;

                    case 'file':
                        if (empty($value)) continue;
                        switch ($form[$key]['#widget']) {
                            case 'image':
                                if (empty($form[$key]['#prefix'])) {
                                    $form[$key]['#prefix'] = '';
                                }
                                $form[$key]['#prefix'] .= zerophp_view('form_prefix-image', array('images' => (array) $value));

                                if (empty($form[$key]['#description'])) {
                                    $form[$key]['#description'] = '';
                                }
                                //@todo 7 Viet doan script de xoa file cu neu bi update de
                                // Them chuc nang cho phep xoa anh da upload
                                $form[$key]['#description'] .= zerophp_lang('Upload new image to override this image.');
                                break;

                            case 'file':
                                break;
                        }
                        break;

                    default:
                        $form[$key]['#value'] = $value;
                }
            }
        }
    }

    public static function submit() {
        $form_id = \Input::get('_form_id', false);
        $form_url = \Input::get('_form_url', false);

        // Restore $form_items from cache
        $cache_name = __CLASS__ . "-build-$form_id-$form_url";
        $form = \Cache::get($cache_name);
        \Cache::forget($cache_name);

        if (!count($form)) {
            return true;
        }

        $form_values = \Input::all();

        // Validate this form
        $validate = self::_submitValidate($form_id, $form, $form_values);
        if ($validate && !empty($form['#validate'])) {
            foreach ($form['#validate'] as $value) {
                $class = '\\' . ltrim($value['class'], '\\');
                $entity = new $class;
                if (!$entity->{$value['method']}($form_id, $form, $form_values)) {
                    $validate = false;
                }
            }
        }

        // Submit action
        $zerophp =& zerophp_get_instance();
        $redirect = '';
        if ($validate) {
            if (!empty($form['#submit'])) {
                foreach ($form['#submit'] as $value) {
                    $class = '\\' . ltrim($value['class'], '\\');
                    $entity = new $class;
                    $entity->{$value['method']}($form_id, $form, $form_values);
                }
            }

            if (!empty($form['#success_message'])) {
                $zerophp->response->addMessage($form['#success_message']);
            }

            if(!empty($form['#redirect'])) {
                $redirect = $form['#redirect'];
            }
        }
        // Set default value for error form
        else {
            foreach ($form_values as $key => $value) {
                if (isset($form[$key])) {
                    $form[$key]['#value'] = $value;
                }
            }
            \Cache::put($cache_name, $form, ZEROPHP_CACHE_EXPIRE_TIME);
        }

        //Close modal after submit
        if (isset($form['#success'])) {
            if (is_array($form['#success'])) {
                $form['#success']['arguments'] = isset($form['#success']['arguments']) ? (array) $form['#success']['arguments'] : array();
                $form['#success'] = call_user_func_array(array(new $form['#success']['class'], $form['#success']['method']), $form['#success']['arguments']);
            }

            $zerophp->response->addContent($form['#success']);
            return false;
        }

        // Redirect after submit finalize
        if ($redirect) {
            switch ($zerophp->response->getOutputType()) {
                case 'json':
                case 'ajax':
                case 'modal':
                    $data = array(
                        'form_redirect' => $redirect,
                    );
                    $zerophp->response->addContentJSON($data);
                    $zerophp->response->setOutputType('json');
                    return false;

                default:
                    return zerophp_redirect($redirect);
            }
        }

        return true;
    }

    // Validate & reset $form_values
    private static function _submitValidate($form_id, &$form, &$form_values) {
        $rules = array();

        foreach ($form as $key => $value) {
            if (substr($key, 0, 1) != '#') {
                // Remove disabled fields were edited by client
                if (isset($value['#disabled']) && $value['#disabled']) {
                    $form_values[$key] = $value['#value'];
                }
                else {
                    // Build value
                    // @todo 9 Hack for date field
                    if (isset($value['#type']) && $value['#type'] == 'date') {
                        if (!empty($form_values[$key]['year']) && is_numeric($form_values[$key]['year'])
                            && 1000 <= $form_values[$key]['year'] && $form_values[$key]['year'] <= 9999
                            && !empty($form_values[$key]['month']) && is_numeric($form_values[$key]['month'])
                            && !empty($form_values[$key]['day']) && is_numeric($form_values[$key]['day'])
                            && checkdate($form_values[$key]['month'], $form_values[$key]['day'], $form_values[$key]['year'])
                        ) {
                            $form_values[$key] = $form_values[$key]['year'] . '-' . $form_values[$key]['month'] . '-' . $form_values[$key]['day'];
                        }
                        else {
                            $form_values[$key] = '';
                        }
                    }

                    $form_values[$key] = isset($form_values[$key]) ? $form_values[$key] : 
                        (isset($value['#default']) ? $value['#default'] : '');
                    if (isset($value['#validate'])) {
                        $rules['value'][$key] = $form_values[$key];
                        $rules['rule'][$key] = $value['#validate'];
                    }
                }
            }
        }

        // Remove new fields were added by client
        foreach ($form_values as $key => $value) {
            if (!isset($form[$key])) {
                unset($form_values[$key]);
            }
        }

        if (count($rules)) {
            $validator = \Validator::make($rules['value'], $rules['rule']);

            if ($validator->fails()) {
                $form['#error'] = array_merge($form['#error'], json_decode($validator->messages()));
                return false;
            }
        }

        return true;
    }
}

// Checked
