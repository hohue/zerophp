<?php 
namespace ZeroPHP\ZeroPHP;

use ZeroPHP\ZeroPHP\Theme;

class Form {

    private $_csrf_hash = '';
    private $_csrf_expire = 7200;
    private $_csrf_token_name = 'ci_csrf_token';
    private $form_items = array();
    private $form_values = array();
    private $form_keys = array(
        'form_key' => '208db74',
        'form_token' => 'c4beb89'
    );
    private $form_special_keys = array(
        '#validate',
        '#submit',
        '#redirect'
    );

    function __construct() {
        

        // CSRF config
        foreach (array(
            'csrf_expire',
            'csrf_token_name'
        ) as $key) {
            if (FALSE !== ($val = config_item($key))) {
                $this->{'_' . $key} = $val;
            }
        }

        // Append application specific cookie prefix
        if (config_item('cookie_prefix')) {
            $this->_csrf_cookie_name = config_item('cookie_prefix') . $this->_csrf_cookie_name;
        }

        // Set the CSRF hash
        $this->_csrf_set_hash();
    }

    function form_values_get_all($form_id) {
        return $this->form_values[$form_id];
    }

    function csrf_expire_get() {
        return $this->_csrf_expire;
    }

    function submit() {
        $form_id = $this->CI->input->post('form_id');

        // Restore $form_items from cache
        $cache_name = "Form-form_items-$form_id-" . $this->csrf_get_hash();
        $this->form_items[$form_id] = $this->CI->cachef->get_form($cache_name);
        $this->CI->cachef->del_form($cache_name);

        if (!count($this->form_items[$form_id])) {
            return;
        }

        $this->form_values[$form_id] = $this->CI->input->post();
        $this->_form_validate($form_id, $this->form_items[$form_id], $this->form_values[$form_id]);
        $_POST = $this->form_values[$form_id];

        // Generate form_values
        foreach ($this->form_items[$form_id] as $key => $value) {
            if (isset($value['#type']) && $value['#type'] == 'date_group') {
                if (!empty($this->form_values[$form_id][$key]['year']) && is_numeric($this->form_values[$form_id][$key]['year'])
                    && 1000 <= $this->form_values[$form_id][$key]['year'] && $this->form_values[$form_id][$key]['year'] <= 9999
                    && !empty($this->form_values[$form_id][$key]['month']) && is_numeric($this->form_values[$form_id][$key]['month'])
                    && !empty($this->form_values[$form_id][$key]['day']) && is_numeric($this->form_values[$form_id][$key]['day'])
                ) {
                    $this->form_values[$form_id][$key] = $this->form_values[$form_id][$key]['year'] . '-' . $this->form_values[$form_id][$key]['month'] . '-' . $this->form_values[$form_id][$key]['day'];
                }
                else {
                    $this->form_values[$form_id][$key] = '';
                }
            }
        }

        // Validate this form
        $validate = true;
        if (!empty($this->form_items[$form_id]['#validate'])) {
            foreach ($this->form_items[$form_id]['#validate'] as $value) {
                $entity = Entity::loadEntityObject($value['class']);
                if (!$this->CI->{$value['class']}->{$value['function']}($form_id, $this->form_items[$form_id], $this->form_values[$form_id])) {
                    $validate = false;
                }
            }
        }

        // Submit action
        $redirect = '';
        if ($validate) {
            if (!empty($this->form_items[$form_id]['#submit'])) {
                foreach ($this->form_items[$form_id]['#submit'] as $value) {
                    $entity = Entity::loadEntityObject($value['class']);

                    $this->CI->{$value['class']}->{$value['function']}($form_id, $this->form_items[$form_id], $this->form_values[$form_id]);
                }

                unset($this->form_values[$form_id]);
            }

            if (!empty($zerophp->request->query('destination'))) {
                $redirect = $zerophp->request->query('destination');
            }
            elseif(!empty($this->form_items[$form_id]['#redirect'])) {
                $redirect = $this->form_items[$form_id]['#redirect'];
            }
        }

        unset($this->form_items[$form_id]);

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

    private function _form_set_values($form_id, $form_values = array()) {
        if (is_object($form_values)) {
            $form_values = fw_object_to_array($form_values);
        }

        //@todo 9 Form: _form_set_values
        //        $this->form_items[$form_id][$key]['#value'] is using to validate form
        //        $this->form_items[$form_id][$key]['#item']['value'] is using to set default value when form render
        //        Need to compile them in next version
        foreach ($form_values as $key => $value) {
            if (isset($this->form_items[$form_id][$key])) {
                switch ($this->form_items[$form_id][$key]['#type']) {
                    // Do not tracking password field
                    case 'password':
                        break;

                    case 'hidden':
                        if (is_array($value)) {
                            foreach ($value as $k => $v) {
                                $this->form_items[$form_id][$key]['#item'][$k] = $v;
                                $this->form_items[$form_id][$key]['#value'][$k] = $v;
                            }
                        }
                        else {
                            $this->form_items[$form_id][$key]['#item'][$key] = $value;
                            $this->form_items[$form_id][$key]['#value'] = $value;
                        }
                        break;

                    case 'checkbox_build':
                        // For Reference Entity Value
                        $test_value = reset($value);
                        if (is_array($test_value)) {
                            $value = array_keys($value);
                        }

                        foreach ($this->form_items[$form_id][$key]['#field'] as $k => $v) {
                            if (in_array($v['value'], $value)) {
                                $this->form_items[$form_id][$key]['#field'][$k]['checked'] = true;
                            }
                            else {
                                $this->form_items[$form_id][$key]['#field'][$k]['checked'] = false;
                            }
                            $this->form_items[$form_id][$key]['#value'][$k] = $value;
                        }
                        break;

                    case 'radio_build':
                        foreach ($this->form_items[$form_id][$key]['#field'] as $k => $v) {
                            if ($v['value'] == $value) {
                                $this->form_items[$form_id][$key]['#field'][$k]['checked'] = true;
                            }
                            else {
                                $this->form_items[$form_id][$key]['#field'][$k]['checked'] = false;
                            }
                            $this->form_items[$form_id][$key]['#value'][$k] = $value;
                        }
                        break;

                    case 'upload':
                        switch ($this->form_items[$form_id][$key]['#item']['widget']) {
                            case 'image':
                                if (empty($this->form_items[$form_id][$key]['#prefix'])) {
                                    $this->form_items[$form_id][$key]['#prefix'] = '';
                                }

                                if (empty($this->form_items[$form_id][$key]['#description'])) {
                                    $this->form_items[$form_id][$key]['#description'] = '';
                                }

                                $this->form_items[$form_id][$key]['#prefix'] .= zerophp_view('form_image_field', array('images' => array($value)));
                                //@todo 7 Viet doan script de xoa file cu neu bi update de
                                // Them chuc nang cho phep xoa anh da upload
                                $this->form_items[$form_id][$key]['#description'] .= zerophp_lang('Upload new image to override this image.');
                                break;

                            case 'file':
                                break;
                        }
                        break;

                    default:
                        if (is_array($value)) {
                            $value = reset(array_keys($value));
                        }
                        $this->form_items[$form_id][$key]['#item']['value'] = $value;
                        $this->form_items[$form_id][$key]['#value'] = $value;
                        break;
                }
            }
        }
    }

    function build($form_id, $form = array(), $form_values = array(), $cache = true) {
        if ($cache && $cache_value = \Cache::get("Form-form_build-$form_id")) {
            $this->form_items[$form_id] = $cache_value;
        }
        else {
            // Call form_alter functions
            $this->_form_alter($form_id, $form);

            // Move $form['submit'] & $form['#actions'] to the end of form
            if (isset($form['submit'])) {
                $tmp = $form['submit'];
                unset($form['submit']);
                $form['submit'] = $tmp;
            }

            if (isset($form['#actions'])) {
                foreach ($form['#actions'] as $key => $val) {
                    $form[$key] = $val;
                }
            }

            $this->_form_build($form, $form_id);
        }

        if ($cache) {
            \Cache::forever("Form-form_build-$form_id", $this->form_items[$form_id]);
        }

        $this->form_values[$form_id] = isset($this->form_values[$form_id]) ? $this->form_values[$form_id] : $form_values;
        $this->form_values[$form_id] = fw_object_to_array($this->form_values[$form_id]);

        $this->_form_alter($form_id, $this->form_items[$form_id], $this->form_values[$form_id], 'form_value_alter');
        $this->_form_set_values($form_id, $this->form_values[$form_id]);
        $this->_form_setting_add($form_id, $this->form_key_make());

        $this->CI->cachef->set_form("Form-form_items-$form_id-" . $this->csrf_get_hash(), $this->form_items[$form_id], $this->csrf_expire_get());
    }

    private function _form_alter($form_id, &$form, &$form_values = array(), $type = 'form_alter') {
        $entity = Entity::loadEntityObject('hook');
        $form_alter_list = array_merge($this->CI->hook->hook_get_all($type, $form_id), $this->CI->hook->hook_get_all($type));

        foreach ($form_alter_list as $alter) {
            $entity = Entity::loadEntityObject($alter['library']);
            if ($type == 'form_value_alter') {
                $this->CI->{$alter['library']}->{$alter['function']}($form_id, $form, $form_values);
            }
            else {
                $this->CI->{$alter['library']}->{$alter['function']}($form_id, $form);
            }
        }
    }

    private function _form_build($form, $form_id, $change_setting = true) {
        $key = $this->form_key_make();
        $keys_not_support = array(
            $key['form_key'],
            $key['form_token'],
            'form_id'
        );
        foreach ($form as $form_key => $form_value) {
            // Don't care with #validate, #submit
            if (!in_array($form_key, $this->form_special_keys)) {
                if (in_array($form_key, $keys_not_support) && $change_setting) {
                    show_error("Your form is not valid: $form_key / $form_id");
                }

                $form_value['#id'] = "fii_$form_key"; // fii = form item id

                if ($form_value['#type'] != 'hidden') {
                    if (isset($form_value['#item'])) {
                        $form_value['#item']['id'] = isset($form_value['#item']['id']) ? $form_value['#item']['id'] : $form_value['#id'] . '_field';
                    }
                    elseif (isset($form_value['#field'])) {
                        foreach ($form_value['#field'] as $k => $v) {
                            $form_value['#field'][$k]['id'] = isset($v['id']) ? $v['id'] : $form_value['#id'] . '_field_' . $k;
                        }
                    }
                }

                $class = "form_items form_item_" . $form_value['#type'] . " form_item_$form_key";
                $form_value['#class'] = $class . (isset($form_value['#class']) ? ' ' . $form_value['#class'] : '');
            }

            $this->form_items[$form_id][$form_key] = $form_value;
        }
    }

    private function _form_setting_add($form_id, $key) {
        $this->_form_build(array(
            'form_id' => array(
                '#name' => 'form_id',
                '#type' => 'hidden',
                '#item' => array(
                    'form_id' => $form_id
                )
            )
        ), $form_id, false);

        $form_key = do_hash(microtime());
        $this->_form_build(array(
            $key['form_key'] => array(
                '#name' => $key['form_key'],
                '#type' => 'hidden',
                '#item' => array(
                    $key['form_key'] => $form_key
                )
            )
        ), $form_id, false);

        $this->_form_build(array(
            $key['form_token'] => array(
                '#name' => $key['form_token'],
                '#type' => 'hidden',
                '#item' => array(
                    $key['form_token'] => $this->_form_token_make($form_key, $form_id)
                )
            )
        ), $form_id, false);
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
            case 'checkbox_build':
            case 'radio_build':
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
                $reference = $this->CI->{$field['reference']}->loadEntity_all();
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
        if (isset($this->form_items[$form_id]) && isset($this->form_items[$form_id][$form_item])) {
            $result = $this->form_items[$form_id][$form_item];
            return $result;
        }

        return array();
    }

    // Delete form_item rendered
    function form_item_rendered($form_item, $form_id) {
        unset($this->form_items[$form_id][$form_item]);
    }

    function form_item_get_all($form_id) {
        if (isset($this->form_items[$form_id])) {
            return $this->form_items[$form_id];
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