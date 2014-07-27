<?php 
namespace ZeroPHP\ZeroPHP;

use ZeroPHP\ZeroPHP\Entity;

class Form {
    public static function build($form = array(), $form_values = array()) {
        $form_id = zerophp_uri_validate(zerophp_get_calling_function());

        $cache_name = __METHOD__ . $form_id;
        if ($cache_value = \Cache::get($cache_name)) {
            $form = $cache_value;
        }
        else {
            $form['#id'] = $form_id;
            $form['_form_id'] = array(
                '#name' => '_form_id',
                '#type' => 'hidden',
                '#value' => $form_id,
            );

            // Call form_alter functions
            self::_alter($form_id, $form);
            $form = self::_build($form_id, $form);

            \Cache::forever($cache_name, $form);
        }

        // Call form_value_alter functions
        $form_values = zerophp_object_to_array($form_values);
        self::_alter($form_id, $form, $form_values, 'form_value_alter');

        // Set default value for form
        $form = self::_setValues($form_id, $form, $form_values);

        // Create cache to use when form submitted
        \Cache::put(__CLASS__ . "-build-$form_id-" . \Session::getId(), $form, \Config::get('session.lifetime', 120));

        return zerophp_view($form['#theme'], array('form' => $form));
    }

    private static function _alter($form_id, &$form, &$form_values = array(), $type = 'form_alter') {
        $form_alter_list = Entity::loadEntityObject('ZeroPHP\ZeroPHP\Hook');
        $form_alter_list = array_merge($form_alter_list->loadEntityAllByHookType($type), $form_alter_list->loadEntityAllByHookType($type, $form_id));

        foreach ($form_alter_list as $alter) {
            $hook = Entity::loadEntityObject($alter->class);
            if ($type == 'form_value_alter') {
                $hook->{$alter->method}($form_id, $form, $form_values);
            }
            else {
                $hook->{$alter->method}($form_id, $form);
            }
        }
    }

    private static function _build($form_id, $form) {
        // Set form attributes default
        $form['#form'] = isset($form['#form']) ? $form['#form'] : array();
        $form['#theme'] = isset($form['#theme']) ? $form['#theme'] : 'form';
        $form['#actions'] = isset($form['#actions']) ? $form['#actions'] :  array();

        // Move submit to $form['actions']
        if (isset($form['submit'])) {
            $form['#actions']['submit'] = $form['submit'];
            unset($form['submit']);
        }

        foreach ($form as $key => $value) {
            if (substr($key, 0, 1) == '_') {
                \App::error(function(InvalidUserException $exception) {
                    $message = "Field name must not start with _";
                    \Log::error($message);

                    return $message;
                });
            }

            // Don't care with #validate, #submit...
            if (substr($key, 0, 1) != '#') {
                $form[$key] = self::__buildItem($value);
            }
            elseif ($key == '#actions') {
                foreach ($value as $k => $v) {
                    $form[$key][$k] = self::__buildItem($v);
                }
            }
        }

        return $form;
    }

    private static function __buildItem($item) {
        /*if (!isset($item['#name'])) {
            zerophp_devel_print($item);
        }*/

        $item['#id'] = isset($item['#id']) ? $item['#id'] : 'fii_' . $item['#name']; // fii = form item id
        $item['#class'] = 'form_item form_item_' . $item['#type'] . ' form_item_' . $item['#name'] . (isset($item['#class']) ? ' ' . $item['#class'] : '');
        $item['#value'] = isset($item['#value']) ? $item['#value'] : '';
        $item['#attributes'] = isset($item['#attributes']) ? $item['#attributes'] : array();

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
        }

        return $item;
    }

    private static function _setValues($form_id, $form, $form_values = array()) {
        if (is_object($form_values)) {
            $form_values = fw_object_to_array($form_values);
        }

        //@todo 9 Form: _form_set_values
        //        $form_items[$key]['#value'] is using to validate form
        //        $form_items[$key]['#item']['value'] is using to set default value when form render
        //        Need to compile them in next version
        /*foreach ($form_values as $key => $value) {
            if (isset($form_items[$key])) {
                switch ($form_items[$key]['#type']) {
                    // Do not tracking password field
                    case 'password':
                        break;

                    case 'hidden':
                        if (is_array($value)) {
                            foreach ($value as $k => $v) {
                                $form_items[$key]['#item'][$k] = $v;
                                $form_items[$key]['#value'][$k] = $v;
                            }
                        }
                        else {
                            $form_items[$key]['#item'][$key] = $value;
                            $form_items[$key]['#value'] = $value;
                        }
                        break;

                    case 'checkboxes':
                        // For Reference Entity Value
                        $test_value = reset($value);
                        if (is_array($test_value)) {
                            $value = array_keys($value);
                        }

                        foreach ($form_items[$key]['#field'] as $k => $v) {
                            if (in_array($v['value'], $value)) {
                                $form_items[$key]['#field'][$k]['checked'] = true;
                            }
                            else {
                                $form_items[$key]['#field'][$k]['checked'] = false;
                            }
                            $form_items[$key]['#value'][$k] = $value;
                        }
                        break;

                    case 'radios':
                        foreach ($form_items[$key]['#field'] as $k => $v) {
                            if ($v['value'] == $value) {
                                $form_items[$key]['#field'][$k]['checked'] = true;
                            }
                            else {
                                $form_items[$key]['#field'][$k]['checked'] = false;
                            }
                            $form_items[$key]['#value'][$k] = $value;
                        }
                        break;

                    case 'upload':
                        switch ($form_items[$key]['#item']['widget']) {
                            case 'image':
                                if (empty($form_items[$key]['#prefix'])) {
                                    $form_items[$key]['#prefix'] = '';
                                }

                                if (empty($form_items[$key]['#description'])) {
                                    $form_items[$key]['#description'] = '';
                                }

                                $form_items[$key]['#prefix'] .= zerophp_view('form_image_field', array('images' => array($value)));
                                //@todo 7 Viet doan script de xoa file cu neu bi update de
                                // Them chuc nang cho phep xoa anh da upload
                                $form_items[$key]['#description'] .= zerophp_lang('Upload new image to override this image.');
                                break;

                            case 'file':
                                break;
                        }
                        break;

                    default:
                        if (is_array($value)) {
                            $value = reset(array_keys($value));
                        }
                        $form_items[$key]['#item']['value'] = $value;
                        $form_items[$key]['#value'] = $value;
                        break;
                }
            }
        }*/

        return $form;
    }






    function form_values_get_all($form_id) {
        return $form_values;
    }

    function csrf_expire_get() {
        return $this->_csrf_expire;
    }

    function submit() {
        $form_id = $this->CI->input->post('form_id');

        // Restore $form_items from cache
        $cache_name = "Form-form_items-$form_id-" . $this->csrf_get_hash();
        $form_items = $this->CI->cachef->get_form($cache_name);
        $this->CI->cachef->del_form($cache_name);

        if (!count($form_items)) {
            return;
        }

        $form_values = $this->CI->input->post();
        $this->_form_validate($form_id, $form_items, $form_values);
        $_POST = $form_values;

        // Generate form_values
        foreach ($form_items as $key => $value) {
            if (isset($value['#type']) && $value['#type'] == 'date_group') {
                if (!empty($form_values[$key]['year']) && is_numeric($form_values[$key]['year'])
                    && 1000 <= $form_values[$key]['year'] && $form_values[$key]['year'] <= 9999
                    && !empty($form_values[$key]['month']) && is_numeric($form_values[$key]['month'])
                    && !empty($form_values[$key]['day']) && is_numeric($form_values[$key]['day'])
                ) {
                    $form_values[$key] = $form_values[$key]['year'] . '-' . $form_values[$key]['month'] . '-' . $form_values[$key]['day'];
                }
                else {
                    $form_values[$key] = '';
                }
            }
        }

        // Validate this form
        $validate = true;
        if (!empty($form_items['#validate'])) {
            foreach ($form_items['#validate'] as $value) {
                $entity = Entity::loadEntityObject($value['class']);
                if (!$this->CI->{$value['class']}->{$value['function']}($form_id, $form_items, $form_values)) {
                    $validate = false;
                }
            }
        }

        // Submit action
        $redirect = '';
        if ($validate) {
            if (!empty($form_items['#submit'])) {
                foreach ($form_items['#submit'] as $value) {
                    $entity = Entity::loadEntityObject($value['class']);

                    $this->CI->{$value['class']}->{$value['function']}($form_id, $form_items, $form_values);
                }

                unset($form_values);
            }

            if (!empty($zerophp->request->query('destination'))) {
                $redirect = $zerophp->request->query('destination');
            }
            elseif(!empty($form_items['#redirect'])) {
                $redirect = $form_items['#redirect'];
            }
        }

        unset($form_items);

        // Redirect after submit finalize
        if ($redirect) {
            switch ($this->CI->theme->output_type_get()) {
                case 'json':
                case 'html':
                    $data = array(
                        'form_redirect' => $redirect,
                    );
                    $zerophp->response->addContent_json($data);
                    $this->CI->theme->output_type_set('json');
                    fw_output();
                    die();

                default:
                    redirect($redirect);
            }
        }
    }

    // Validate & reset $form_values
    private function _form_validate($form_id, $form, &$form_values) {
        if (count($form)) {
            // Client edit disabled field
            foreach ($form as $value) {
                if (isset($value['#disabled']) && $value['#disabled']) {
                    $form_values[$value['#name']] = $value['#value'];
                }
            }

            // Client add a new field
            foreach ($form_values as $key => $value) {
                if (!isset($form[$key])) {
                    unset($form_values[$key]);
                }
            }
        }
    }

    function form_key_make() {
        return $this->form_keys;
    }

    private function _form_token_make($form_key, $form_id) {
        return do_hash($form_id . $this->csrf_get_hash() . $form_key);
    }

    function form_token_get() {
        $key = $this->form_key_make();
        return $this->_form_token_make($this->CI->input->post($key['form_key']), $this->CI->input->post('form_id'));
    }

    function form_item_generate($field, $item_name = '') {
        $field['name'] = $item_name ? $item_name : $field['name'];

        $form = array(
            '#type' => $field['type'],
            '#name' => $field['name']
        );

        if (isset($field['title'])) {
            $form['#label'] = $field['title'];
            unset($field['title']);
        }

        if (isset($field['description'])) {
            $form['#description'] = $field['description'];
            unset($field['description']);
        }

        if (isset($field['required'])) {
            $form['#required'] = $field['required'];
            unset($field['required']);
        }

        if (isset($field['error_messages'])) {
            $form['#error_messages'] = $field['error_messages'];
            unset($field['error_messages']);
        }

        if (isset($field['js_validate'])) {
            foreach ($field['js_validate'] as $key => $value) {
                $field[$key] = $value;
            }
            unset($field['js_validate']);
        }

        $this->_form_item_generate_reference($field);
        switch ($field['type']) {
            case 'checkboxes':
            case 'radios':
                foreach ($field['options'] as $option_key => $option_value) {
                    $form_item_data = array();
                    $form_item_data['value'] = $option_key;
                    $form_item_data['name'] = $field['name'];
                    $form_item_data['id'] = $field['name'] . "_$option_key";
                    $form_item_data['label'] = $option_value;

                    $form_item[] = $form_item_data;
                }
                break;

            // From here $form_item[] = $field;
            case 'textarea':
                if (isset($field['rte_enable']) && $field['rte_enable']) {
                    $field['id'] = form_textarea_get_rte();
                }

            default:
                $form_item[] = $field;
        }

        if (count($form_item) > 1) {
            $form['#field'] = $form_item;
        }
        else {
            $form['#item'] = reset($form_item);
        }

        return $form;
    }

    private function _form_item_generate_reference(&$field) {
        if (isset($field['reference']) && $field['reference']) {
            $entity = Entity::loadEntityObject($field['reference']);
            $ref_structure = $field['reference']['class']::getStructure();

            if (empty($field['reference_option'])) {
                $reference = $this->CI->{$field['reference']}->loadEntityAll();
            }
            else {
                $entity = Entity::loadEntityObject($field['reference_option']['library']);
                $reference = $this->CI->{$field['reference_option']['library']}->{$field['reference_option']['function']}($field['reference_option']['arguments']);
            }

            foreach ($reference as $ref) {
                $ref = fw_object_to_array($ref);
                $field['options'][$ref[$ref_structure['id']]] = isset($ref['title']) ? $ref['title'] : $ref[$ref_structure['id']];
            }
        }
    }

    function form_item_get($form_item, $form_id) {
        if (isset($form_items) && isset($form_items[$form_item])) {
            $result = $form_items[$form_item];
            return $result;
        }

        return array();
    }

    // Delete form_item rendered
    function form_item_rendered($form_item, $form_id) {
        unset($form_items[$form_item]);
    }

    function form_item_get_all($form_id) {
        if (isset($form_items)) {
            return $form_items;
        }

        return array();
    }

    function form_get_all() {
        return $this->form_items;
    }

    function form_get_form_id() {
        return array_keys($this->form_items);
    }

    /**
     * Get CSRF Hash
     *
     * Getter Method
     *
     * @return string self::_csrf_hash
     */
    function csrf_get_hash() {
        return $this->_csrf_hash;
    }

    /**
     * Get CSRF Token Name
     *
     * Getter Method
     *
     * @return string self::csrf_token_name
     */
    function csrf_get_token_name() {
        return $this->_csrf_token_name;
    }

    /**
     * Set Cross Site Request Forgery Protection Cookie
     *
     * @return string
     */
    private function _csrf_set_hash() {
        if ($this->_csrf_hash == '') {
            // If the cookie exists we will use it's value.
            // We don't necessarily want to regenerate it with
            // each page load since a page could contain embedded
            // sub-pages causing this feature to fail
            $csrf_hash = $this->CI->session->userdata('csrf');
            if (isset($csrf_hash['expire']) && $csrf_hash['expire'] >= time()) {
                return $this->_csrf_hash = $csrf_hash['value'];
            }

            $csrf_hash = array(
                'csrf' => array(
                    'expire' => time() + $this->_csrf_expire,
                    'value' => do_hash(uniqid(rand(), TRUE))
                )
            );
            $this->CI->session->set_userdata($csrf_hash);

            return $this->_csrf_hash = $csrf_hash['csrf']['value'];
        }

        return $this->_csrf_hash;
    }
}