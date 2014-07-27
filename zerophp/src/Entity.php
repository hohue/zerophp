<?php 
namespace ZeroPHP\ZeroPHP;

use ZeroPHP\ZeroPHP\Theme;
use ZeroPHP\ZeroPHP\EntityModel;

class Entity {

    private $structure;

    /**
     * <pre>
     * $structure = array(
     *      'name' => '',                             // Trung voi ten cua class
     *      'id' => '',                               // ID field
     *      'title' => '',                            // Ten cua entity
     *      'ids_can_not_delete' => array(),          // Mang chua cac ID default khong the xoa
     *      'fields' => array(
     *          'shop_topic_id' => array(
     *              ’name’ => ‘shop_topic_id’,        // name trùng với field key
     *              ‘title’ => ‘ID’,                  // Label của form item
     *              ’type’ => ‘hidden’,               // form item type: hidden/input/pasword/textarea….,
     *              ’validate’ => ‘required’,         // Form Validation Rule reference
     *          ),
     *      ),
     * );
     * </pre>
     * @param string $structure
     */
    public function setStructure($structure = null) {
        $this->structure = $structure;
    }

    public function getStructure() {
        return $this->structure;
    }

    public static function loadEntityObject($entity) {
        $zerophp =& zerophp_get_instance();

        $entity_name = zerophp_uri_validate($entity);
        $entity_name = str_replace('-', '_', $entity_name);

        if (!isset($zerophp->entity[$entity_name])) {
            $zerophp->entity[$entity_name] = new $entity;
        }

        return $zerophp->entity[$entity_name];
    }

    function loadEntityAll($attributes = array()) {
        $attributes['load_all'] = true;
        return $this->loadEntityExecutive(null, $attributes);
    }

    function loadEntityExecutive($entity_id = 0, $attributes = array()) {
        // Get from cache
        if (!isset($attributes['cache']) || $attributes['cache']) {
            $cache_name = __METHOD__ . $entity_id . $this->structure['#name'];
            $cache_name .= serialize($attributes);

            $cache_content = \Cache::get($cache_name);
            if ($cache_content) {
                return $cache_content;
            }
        }

        // Get from database
        $entities = array();

        if ($entity_id === 0 && empty($attributes['load_all'])) {
            $entities[0] = new stdClass();
            $entities[0]->{$this->structure['#id']} = 0;
        }
        else {
            $entities = EntityModel::loadEntity($entity_id, $this->structure, $attributes);
        }

        foreach ($entities as $entity_key => $entity) {
            $entities[$entity_key] = $this->buildEntity($entity, $attributes);
        }

        // Set to cache
        if (!isset($attributes['cache']) || $attributes['cache']) {
            \Cache::put($cache_name, $entities, ZEROPHP_CACHE_EXPIRE_TIME);
        }

        return $entities;
    }

    public function buildEntity($entity, $attributes = false) {
        foreach ($this->structure['#fields'] as $value) {
            if ((!isset($attributes['load_hidden']) || !$attributes['load_hidden'])
                 && (isset($value['#load_hidden']) && $value['#load_hidden']) ) {
                $entity->{$value['#name']} = '';
                continue;
            }

            if (isset($value['#reference']) && $value['#reference']) {
                $entity->{$value['#name']} = $this->buildEntityReference($entity, $value['#name'], $attributes);
                continue;
            }

            if (empty($entity->{$this->structure['#id']})) {
                if ($value['#name'] == $this->structure['#id']) {
                    $entity->{$value['#name']} = 0;
                }
                else {
                    $entity->{$value['#name']} = '';
                }
            }
        }

        return $entity;
    }

    function buildEntityReference($entity, $field, $attributes = array()) {
        $entity_id = $entity->{$this->structure['#id']};

        // Get from cache
        if (!isset($attributes['cache']) || $attributes['cache'] == true) {
            $cache_name = __METHOD__ . "$field-$entity_id-" . $this->structure['#name'] . serialize($attributes);
            if ($cache_content = \Cache::get($cache_name)) {
                return $cache_content;
            }
        }

        $field = $this->structure['#fields'][$field];

        $ref = $this->loadEntityObject($field['#reference']['class']);
        $ref_structure = $ref->getStructure();

        $reference = array();
        if (!isset($field['#reference']['type']) || $field['#reference']['type'] != 'internal') {
            $reference = EntityModel::loadReference($field['#name'], $entity_id, $this->structure, $ref_structure);
        }
        else {
            $reference = array(
                $entity->{$field['#name']},
            );
        }

        $result = array();
        if (count($reference)) {
            foreach ($reference as $ref_id) {
                if ($ref_id) {
                    $attributes['filter'] = false;
                    $reference_entity_obj = $this->loadEntityObject($field['#reference']['class']);
                    $reference_entity = $reference_entity_obj->loadEntity($ref_id, $attributes);

                    //if ($field['#name'] == 'district_id') {
                        //fw_devel_print($field, $ref, $ref_structure);
                    //}

                    if (!empty($reference_entity->{$ref_structure['#id']})) {
                        $result[$reference_entity->{$ref_structure['#id']}] = $reference_entity;
                    }
                }
            }
        }

        // Set to cache
        if (!isset($attributes['cache']) || $attributes['cache'] == true) {
            \Cache::put($cache_name, $result);
        }

        return $result;
    }

    function loadEntity($entity_id, $attributes = array()) {
        // entity load default
        $cached = false;
        if (is_numeric($entity_id) && !count($attributes)) {
            $cached = true;
            $cache_name = __CLASS__ . "-Entity-$entity_id-" . $this->structure['#name'];
            if ($cache = \Cache::get($cache_name)) {
                return $cache;
            }
        }

        $attributes['load_all'] = false;
        $entity = $this->loadEntityExecutive($entity_id, $attributes);

        $result = reset($entity);

        if ($cached) {
            \Cache::forever($cache_name, $result);
        }
        return $result;
    }

    function saveEntity($entity) {
        $reference = array();
        foreach ($this->structure['#fields'] as $field) {
            // Save Reference fields to temp
            if (!empty($field['#reference']) && isset($entity->{$field['#name']})) {
                if (!is_array($entity->{$field['#name']})) {
                    $reference_field = array(
                        $entity->{$field['#name']},
                    );
                }
                else {
                    $reference_field = $entity->{$field['#name']};
                }
                $reference[$field['#name']] = $reference_field;

                if (empty($field['#reference']['type']) || $field['#reference']['type'] != 'internal') {
                    unset($entity->{$field['#name']});
                }
            }
        }

        $update = false;
        if (isset($entity->{$this->structure['#id']}) && $entity->{$this->structure['#id']}) {
            $entity_old = $this->loadEntity($entity->{$this->structure['#id']}, array(
                'check_active' => false,
                'cache' => false,
            ));

            if (!empty($entity_old->{$this->structure['#id']})) {
                $entity_id = EntityModel::update($entity, $this->structure);

                $cache_name = __CLASS__ . "-Entity-$entity_id-" . $this->structure['#name'];
                \Cache::forget($cache_name);

                $update = true;

                unset($entity->{$this->structure['#id']});
            }
        }

        if (!$update) {
            $entity_id = EntityModel::create($entity, $this->structure);
        }

        // Save reference fields from temp to database
        if (count($reference)) {
            $this->saveEntityReference($reference, $entity_id);
        }

        return $entity_id;
    }

    function saveEntityReference($reference, $entity_id) {
        EntityModel::saveReference($reference, $entity_id, $this->structure);
    }









    function crud_list($url_prefix = '', $page = 1) {
        $template = "entity_list_" . $this->structure['#name'] . '|' . $this->structure['#name'];
        $template = $this->CI->theme->template_check($template, $this->structure['#name']) ? $template : 'entity_list';

        $attributes = array(
            'page' => $page,
        );

        if ($this->CI->theme->admin_get()) {
            $attributes['check_active'] = false;
            $attributes['cache'] = false;
        }

        $pager_sum = 1;
        $entities = $this->loadEntityAll($attributes, $pager_sum);

        $data = array(
            'form_id' => $this->crud_list_form($entities, $url_prefix, $page),
            'entities' => $entities,
            'structure' => $this->structure,
            'add_new_link' => fw_anchor($url_prefix . "up/e/create/" . $this->structure['#name'], zerophp_lang('Add new')),
            'url_prefix' => $url_prefix,
            'pager_current' => $page,
            'pager_sum' => $pager_sum,
            'pager_uri' => $url_prefix . "up/e/index/" . $this->structure['#name'],
        );

        return array(
            'page_title' => $this->structure->title,
            'template' => $template,
            'data' => $data,
        );
    }

    function crud_create($type = 'create', $entity = null, $url_prefix = '', $action = '') {
        $template = "entity_create_" . $this->structure['#name'] . '|' . $this->structure['#name'];
        $template = $this->CI->theme->template_check($template, $this->structure['#name']) ? $template : 'entity_create';

        // Update
        if ($type == 'update' && $entity) {
            $page_title = zerophp_lang('Editing') . ": ";
            if (isset($entity->title) && $entity->title) {
                $page_title .= $entity->title;
            }
            else {
                $page_title .= $this->structure->title . " " . $entity->{$this->structure['#id']};
            }
        }

        // Duplicate
        elseif ($type == 'duplicate' && $entity) {
            $page_title = zerophp_lang('Clone') . ": ";
            if (isset($entity->title) && $entity->title) {
                $page_title .= $entity->title;
            }
            else {
                $page_title .= $this->structure->title . " " . $entity->{$this->structure['#id']};
            }

            unset($entity->{$this->structure['#id']});
        }

        // Create
        else {
            $page_title = zerophp_lang('Creating') . ': ' . $this->structure->title;
        }

        $data = array(
            'form_id' => $this->crud_create_form($type, $entity, $type == 'update' ? true : false, $url_prefix, $action),
        );

        return array(
            'page_title' => $page_title,
            'template' => $template,
            'data' =>$data,
        );
    }

    function crud_read($entity, $url_prefix = '') {
        $template = "entity_read_" . $this->structure['#name'] . '|' . $this->structure['#name'];
        $template = $this->CI->theme->template_check($template) ? $template : 'entity_read';

        $data = array(
            'entity' => $entity,
            'structure' => $this->structure,
        );

        $page_title = isset($entity->title) ? $entity->title : $this->structure->title;


        $this->CI->theme->breadcrumbs_add(array(array('item' => $page_title)));

        if (!empty($entity->{$this->structure['#id']})) {
            $this->CI->theme->tabs_add($this->link_tab($this->link_action($entity->{$this->structure['#id']}, $url_prefix, 'read')));
        }

        return array(
            'page_title' => $page_title,
            'template' => $template,
            'data' => $data,
        );
    }

    function crud_preview($entity, $url_prefix = '') {
        return $this->crud_read($entity, $url_prefix);
    }

    function crud_update($entity, $url_prefix = '') {
        return $this->crud_create('update', $entity, $url_prefix);
    }

    function crud_delete($entity, $url_prefix = '') {
        if (isset($entity->title) && $entity->title) {
            $entity_title = $entity->title;
        }
        else {
            $entity_title = "#" . $entity->{$this->structure['#id']};
        }

        $template = "entity_delete_" . $this->structure['#name'] . '|' . $this->structure['#name'];
        $template = $this->CI->theme->template_check($template, $this->structure['#name']) ? $template : 'entity_delete';

        $data = array(
            'form_id' => $this->crud_delete_form($entity->{$this->structure['#id']}, $url_prefix),
            'entity_name' => $this->structure['#name'],
            'entity_title' => $entity_title,
        );

        return array(
            'page_title' => zerophp_lang('Deleting') . ": " . $entity_title,
            'template' => $template,
            'data' => $data,
        );
    }

    function crud_duplicate($entity, $url_prefix) {
        return $this->crud_create('duplicate', $entity, $url_prefix);
    }

    function crud_views($attributes = array()) {
        $attributes['entity_name'] = isset($attributes['entity_name']) ? $attributes['entity_name'] : '';
        $attributes['entity_id'] = isset($attributes['entity_id']) ? $attributes['entity_id'] : '';
        $attributes['view_type'] = isset($attributes['view_type']) ? $attributes['view_type'] : '';
        $attributes['admin'] = isset($attributes['admin']) ? $attributes['admin'] : false;

        switch ($attributes['entity_id']) {
            case 'me':
                $attributes['entity_id'] = zerophp_user_current();
                break;

            case 'sess':
                $attributes['entity_id'] = $this->CI->session->userdata('session_id');
                $attributes['entity_id_type'] = 'session_id';
                break;

            default:
                $attributes['entity_id'] = intval($attributes['entity_id']);
        }

        $url_prefix = $attributes['admin'] ? 'admin/' : '';

        switch ($attributes['view_type']) {
            case 'crud_list':
                $vars = $this->CI->{$attributes['entity_name']}->{$attributes['view_type']}($url_prefix, isset($attributes['page']) ? $attributes['page'] : 1);

                break;

            case 'crud_create':
                $vars = $this->CI->{$attributes['entity_name']}->{$attributes['view_type']}('create', null, $url_prefix);

                break;

            case 'crud_read':
                if (! $entity = $this->CI->{$attributes['entity_name']}->entity_exists($attributes['entity_id'])) {
                    redirect(fw_variable_get('url page 404', 'dashboard/e404'));
                }
                $vars = $this->CI->{$attributes['entity_name']}->{$attributes['view_type']}($entity, $url_prefix);

                break;

            case 'crud_preview':
            case 'crud_update':
            case 'crud_delete':
            case 'crud_duplicate':
                if (! $entity = $this->CI->{$attributes['entity_name']}->entity_exists($attributes['entity_id'], false, false)) {
                    redirect(fw_variable_get('url page 404', 'dashboard/e404'));
                }
                $vars = $this->CI->{$attributes['entity_name']}->{$attributes['view_type']}($entity, $url_prefix);

                break;

            default:
                redirect(fw_variable_get('url page 404', 'dashboard/e404'));
        }

        $body_class = 'entity';
        $body_class .= $attributes['entity_name'] ? ' entity_' . $attributes['entity_name'] : '';
        $body_class .= $attributes['entity_id'] ? ' entity_' . $attributes['entity_name'] . '_' . $attributes['entity_id'] : '';
        $body_class .= $attributes['view_type'] ? ' entity_' . $attributes['view_type'] : '';
        $body_class .= $attributes['admin'] ? ' entity_admin' : '';

        $zerophp =& ZeroPHP::getInstance();
        $zerophp->response->addBodyClass($body_class);
        $zerophp->response->addContent($vars['template'], $vars['page_title'], $vars['data']);
    }

    function crud_create_form($type = 'create', $entity = null, $update = false, $url_prefix = '', $action = '') {
        $form_id = 'entity_crud_' . ($update ? 'update' : 'create') . '_' . $this->structure['#name'];
        $cache_name = "Entity-crud_create_form-$form_id";
        $cache = \Cache::get($cache_name);
        $form = array();

        if ($cache) {
            $form = $cache;
        }
        else {
            foreach ($this->structure['#fields'] as $value) {
                if (isset($value['form_hidden']) && $value['form_hidden']) {
                    continue;
                }

                if ($value['#name'] != $this->structure['#id']) {
                    $form[$value['#name']] = $this->CI->form->form_item_generate($value);
                }
            }

            $form['entity_name'] = array(
                '#name' => 'entity_name',
                '#type' => 'hidden',
                '#item' => array(
                    'entity_name' => $this->structure['#name'],
                ),
            );

            $form['submit'] = array(
                '#name' => 'submit',
                '#type' => 'submit',
                '#item' => array(
                    'name' => 'submit',
                    'value' => $type == 'update' ? zerophp_lang('Update') : zerophp_lang('Save'),
                ),
            );

            $form['#validate'][] = array(
                'class' => $this->structure['#name'],
                'method' => 'crud_create_form_validate',
            );

            $form['#submit'][] = array(
                'class' => $this->structure['#name'],
                'method' => 'crud_create_form_submit',
            );

            if ($action) {
                $form['#redirect'] = $url_prefix . $action;
            }
            else {
                $url_prefix = $url_prefix ? $url_prefix : (count(array_intersect(array_keys($this->CI->users->user_get()->roles), fw_variable_get('users roles admin', array()))) ? 'admin' : 'up');
                $action = "$url_prefix/e/index/" . $this->structure['#name'];
                if ($this->CI->roles->access_check($action)) {
                    $form['#redirect'] = $action;
                }
            }

            \Cache::forever($cache_name, $form);
        }

        if ($entity == null) {
            $entity = new stdClass();
        }

        if (isset($entity->{$this->structure['#id']})) {
            $form[$this->structure['#id']] = array(
                '#name' => $this->structure['#id'],
                '#type' => 'hidden',
                '#disabled' => 'disabled',
                '#value' => $entity->{$this->structure['#id']},
                '#item' => array(
                    $this->structure['#id'] => $entity->{$this->structure['#id']},
                ),
            );
        }

        foreach ($this->structure['#fields'] as $value) {
            if ($value['type'] == 'textarea' && !empty($value['rte_enable']) && !empty($entity->{$value['#name']})) {
                $text = new DOMDocument();
                @$text->loadHTML('<?xml encoding="UTF-8"?>' . $entity->{$value['#name']});

                $images = $text->getElementsByTagName('img');
                foreach ($images as $image) {
                    $lazyload = $text->createAttribute('src');
                    $lazyload->value = $image->getAttribute('data-original');
                    $image->appendChild($lazyload);
                }

                $body = $text->getElementsByTagName('body')->item(0);
                $entity->{$value['#name']} = str_replace("\n</body>", '', str_replace("<body>\n", '', $text->saveHTML($body)));
            }

            // Default value
            if (!isset($entity->{$value['#name']}) && isset($value['default'])) {
                if (isset($value['#reference'])) {
                    $entity = Entity::loadEntityObject($value['#reference']);
                    $value['default'] = $this->CI->{$value['#reference']}->loadEntity($value['default']);
                }

                $entity->{$value['#name']} = $value['default'];
            }
        }

        $this->CI->form->form_build($form_id, $form, $entity);

        return $form_id;
    }

    function crud_create_form_validate($form_id, $form, &$form_values) {
        $entity = Entity::loadEntityObject('form_validation');

        $validate = false;
        foreach ($this->structure['#fields'] as $key => $value) {
            if (isset($value['validate'])) {
                $this->CI->form_validation->set_rules($key, $value['title'],$value['validate']);
                $validate = true;
            }
        }

        if ($validate && $this->CI->form_validation->run() == FALSE) {
            $this->CI->theme->messages_add(validation_errors(), 'error');
            return false;
        }

        // Textarea clean
        foreach ($this->structure['#fields'] as $key => $value) {
            if ($value['type'] == 'textarea' && !empty($form_values[$key])) {
                if (!empty($value['rte_enable'])) {
                    // Make safe and standard html document
                    $entity = Entity::loadEntityObject('html');
                    $form_values[$key] = $this->CI->html->clean($form_values[$key]);

                    $text = new DOMDocument();
                    @$text->loadHTML('<?xml encoding="UTF-8"?>' . $form_values[$key]); //LIBXML_HTML_NOIMPLIED

                    $images = $text->getElementsByTagName('img');
                    foreach ($images as $image) {
                        $lazyload = $text->createAttribute('data-original');
                        $lazyload->value = $image->getAttribute('src');
                        $image->appendChild($lazyload);

                        $image->removeAttribute('src');

                        $class = $text->createAttribute('class');
                        $class->value .= 'loading lazy';
                        $image->appendChild($class);
                    }

                    $anchors = $text->getElementsByTagName('a');
                    foreach($anchors as $anchor) {
                        $nofollow = $text->createAttribute('rel');
                        $nofollow->value .= 'nofollow';
                        $anchor->appendChild($nofollow);

                        $target = $text->createAttribute('target');
                        $target->value .= '_blank';
                        $anchor->appendChild($target);
                    }

                    $body = $text->getElementsByTagName('body')->item(0);
                    $form_values[$key] = str_replace("</body>", '', str_replace("<body>", '', $text->saveHTML($body)));
                }
                else {
                    $form_values[$key] = strip_tags($form_values[$key]);
                }
            }
        }

        return true;
    }

    function crud_create_form_submit($form_id, $form, &$form_values, $message = '') {
        $entity = new stdClass();

        // Fetch via structure to skip unexpected fields (alter form another modules)
        foreach ($this->structure['#fields'] as $key => $value) {
            if ($value['type'] == 'upload'
                && file_exists($_FILES[$key]['tmp_name'])
                && is_uploaded_file($_FILES[$key]['tmp_name'])
            ) {
                $this->CI->config->load('upload');
                $upload_config = config_item('upload');
                $upload = false;
                switch ($value['widget']) {
                    case 'image':
                        $upload_config = $upload_config['image'];
                        $upload_config['upload_path'] = 'files/images/';
                        $upload = true;
                        break;

                    //@todo 9 cho phep upload file
                    /* case 'file':
                        $upload_config = $upload_config['file'];
                        $upload_config['upload_path'] = 'files/';
                        $upload = true;
                        break; */
                }

                if ($upload) {
                    $upload_config['upload_path'] .= zerophp_user_current() . '/';

                    if (!is_dir($upload_config['upload_path'])) {
                        mkdir($upload_config['upload_path'], 0777, true);
                    }

                    $entity = Entity::loadEntityObject('upload', $upload_config);
                    $this->CI->upload->initialize($upload_config);
                    $result = $this->CI->upload->do_upload($key);

                    if ($result === false) {
                        $this->CI->theme->messages_add($this->CI->upload->display_errors(), 'error');
                        unset($form_values[$key]);
                    }
                    else {
                        $file = $this->CI->upload->data();
                        $form_values[$key] = $upload_config['upload_path'] . $file['file_name'];
                    }
                }
            }

            switch ($key) {
                case 'created_by':
                    if (empty($entity->{$key})) {
                        $entity->{$key} = zerophp_user_current();
                    }
                    break;

                case 'updated_by':
                    $entity->{$key} = zerophp_user_current();
                    break;

                case 'created_at':
                    if (!isset($form_values[$this->structure['#id']])) {
                        $widget = 'entity_widget_' . $value['widget'] . '_make';
                        $entity->{$key} = $widget(time());
                    }
                    break;

                case 'updated_at':
                    $widget = 'entity_widget_' . $value['widget'] . '_make';
                    $entity->{$key} = $widget(time());
                    break;

                default:
                    if (isset($form_values[$key])) {
                        $entity->{$key} = $form_values[$key];
                    }
                    elseif (isset($value['default']) && !isset($form_values[$this->structure['#id']])) {
                        $entity->{$key} = $value['default'];
                    }
            }
        }

        $form_values[$this->structure['#id']] = $this->entity_save($entity);

        $message = $message ? $message : zerophp_lang('Your data was updated successfully.');
        $this->CI->theme->messages_add($message, 'success');

        $this->crud_create_form_submit_hook($form_values);
    }

    function crud_create_form_submit_hook(&$form_values) {
        // Hook entity_create_submit
        $entity = Entity::loadEntityObject('ZeroPHP\ZeroPHP\Hook');
        $hooks = $this->CI->hook->loadEntityAllByHookType('entity_create_submit');
        $this->CI->hook->run($hooks, $form_values);
    }

    function crud_delete_form($entity_ids, $url_prefix = '') {
        if (!is_array($entity_ids)) {
            $entity_ids = array(
                $entity_ids,
            );
        }

        $form['delete'] = array(
            '#name' => 'delete',
            '#type' => 'hidden',
            '#disabled' => 'disabled',
        );
        $i = 0;
        foreach ($entity_ids as $value) {
            $form['delete']['#item']["delete[" . $this->structure['#id'] . "_$i]"] = $value;
            $form['delete']['#value'][$this->structure['#id'] . "_$i"] = $value;
            $i++;
        }

        $form['submit'] = array(
            '#name' => 'submit',
            '#type' => 'submit',
            '#item' => array(
                'name' => 'submit',
                'value' => zerophp_lang('Delete'),
            ),
        );

        $form['#validate'][] = array(
            'class' => $this->structure['#name'],
            'method' => 'crud_delete_form_validate',
        );

        $form['#submit'][] = array(
            'class' => $this->structure['#name'],
            'method' => 'crud_delete_form_submit',
        );

        $form['#redirect'] = $url_prefix . "up/e/index/" . $this->structure['#name'];

        $form_id = "Entity-crud_delete-" . $this->structure['#name'];
        $this->CI->form->form_build($form_id, $form, array(), false);
        return $form_id;
    }

    function crud_delete_form_validate($form_id, $form, &$form_values) {
        $form_values['#delete'] = $form['delete']['#item'];
        return true;
    }

    function crud_delete_form_submit($form_id, $form, &$form_values, $message = '') {
        $this->entity_delete($form_values['#delete']);
        $this->CI->theme->messages_add($message ? $message : zerophp_lang('Your data was deleted successfully.'), 'success');
    }

    function crud_list_form($entities, $url_prefix = '', $page = 1) {
        $row = array();
        $form = array();
        $form_values = array();

        foreach ($entities as $entity) {
            foreach ($this->structure['#fields'] as $value) {
                if (isset($value['fast_edit']) && $value['fast_edit']) {
                    $field_name = $value['#name'] . '_' . $entity->{$this->structure['#id']};

                    $form_item = array(
                        'type' => $value['type'],
                        'name' => $field_name,
                        'value' => $entity->{$value['#name']},
                    );

                    if ($value['#name'] == 'weight') {
                        $form_item['options'] = form_options_make_weight();
                    }

                    $form[$field_name] = $this->CI->form->form_item_generate($form_item);
                    $form_values[$field_name] = $form_item['value'];
                }
            }

            $row[] = $entity->{$this->structure['#id']};
        }

        if (count($form)) {
            $row = implode('|', $row);
            $form['rows'] = array(
                '#name' => 'rows',
                '#type' => 'hidden',
                '#disabled' => 'disabled',
                '#value' => $row,
                '#item' => array(
                    'rows' => $row,
                ),
            );

            $form['submit'] = array(
                '#name' => 'submit',
                '#type' => 'submit',
                '#item' => array(
                    'name' => 'submit',
                    'value' => zerophp_lang('Update'),
                ),
            );

            $form['#validate'][] = array(
                'class' => $this->structure['#name'],
                'method' => 'crud_list_form_validate',
            );

            $form['#submit'][] = array(
                'class' => $this->structure['#name'],
                'method' => 'crud_list_form_submit',
            );

            $form['#redirect'] = $url_prefix . "up/e/index/" . $this->structure['#name'];
        }

        $form_id = "Entity-crud_list-" . $this->structure['#name'];
        $this->CI->form->form_build($form_id, $form, $form_values, false);
        return $form_id;
    }

    function crud_list_form_validate($form_id, $form, &$form_values) {
        $form_values['#update'] = array();

        if (isset($form_values['rows'])) {
            $entity = Entity::loadEntityObject('form_validation');
            $rows = explode('|', $form_values['rows']);

            $validate = false;
            foreach ($rows as $row) {
                foreach ($this->structure['#fields'] as $value) {
                    if (isset($value['fast_edit']) && $value['fast_edit'] && isset($form_values[$value['#name'] . '_' . $row])) {
                        $form_values['#update'][$row][$value['#name']] = $form_values[$value['#name'] . '_' . $row];
                        if (isset($value['validate'])) {
                            $this->CI->form_validation->set_rules($value['#name'] . '_' . $row, $value['title'], $value['validate']);
                            $validate = true;
                        }
                    }
                }
            }

            if ($validate && $this->CI->form_validation->run() == FALSE) {
                $this->CI->theme->messages_add(validation_errors(), 'error');
                return false;
            }
        }

        return true;
    }

    function crud_list_form_submit($form_id, $form, &$form_values) {
        $entities = array();
        foreach ($form_values['#update'] as $entity_id => $update) {
            $entities[$entity_id] = new stdClass();
            $entities[$entity_id]->{$this->structure['#id']} = $entity_id;

            foreach ($update as $key => $value) {
                $entities[$entity_id]->{$key} = $value;
            }
        }

        $this->entity_update_all($entities);
        $this->CI->theme->messages_add(lang('Your data was updated successfully.'), 'success');
    }

    function entity_delete($entity_ids) {
        
        $this->CI->entity_model->delete($entity_ids, $this->structure);
        $this->CI->cachef->clean();
    }

    function entity_update_all($entities, $where_key = null) {
        
        EntityModel::update_all($entities, $this->structure, $where_key);
    }

    function entity_name_exists($entity_name = '') {
        if (!$entity_name) {
            return false;
        }

        $entity = $this->entity_list();

        if (isset($entity[$entity_name])) {
            return true;
        }

        return false;
    }

    function entity_exists($entity_id, $active = true, $cache = true) {
        if (!$entity_id || !is_numeric($entity_id)) {
            return false;
        }

        $entity = $this->loadEntity($entity_id, array('check_active' => $active, 'cache' => $cache));

        if (isset($entity->{$this->structure['#id']}) && $entity->{$this->structure['#id']}) {
            return $entity;
        }

        return false;
    }

    function entity_list() {
        $cache = \Cache::get('Entity-entity_list');
        if ($cache) {
            return $cache;
        }

        $entity = Entity::loadEntityObject('modules');
        $entity_list = $this->CI->modules->loadEntityAll();

        $entity = array();
        foreach ($entity_list as $value) {
            if ($value->is_entity) {
                $entity[$value->class] = $value;
            }
        }

        \Cache::forever('Entity-entity_list', $entity);
        return $entity;
    }

    //@todo 9 Hard-core, tao chuc nang add icon cho tabs & link action
    function link_action($entity_id, $url_prefix = '', $type = 'list') {
        $item = array();

        $url_prefix = $url_prefix ? $url_prefix : 'up';

        if ($link = fw_anchor($url_prefix . "e/preview/" . $this->structure['#name'] . "/$entity_id", '<i class="icon_view_large"></i>' . zerophp_lang('View'))) {
            $item['preview'] = $link;
        }

        if ($type != 'list') {
            if ($link = fw_anchor($url_prefix . "e/duplicate/". $this->structure['#name'] . "/$entity_id", '<i class="icon_postsimalar"></i>' . zerophp_lang('Clone'))) {
                $item['duplicate'] = $link;
            }
        }

        if ($link = fw_anchor($url_prefix . "e/update/". $this->structure['#name'] . "/$entity_id", '<i class="icon_editlarge"></i>' . zerophp_lang('Edit'))) {
            $item['update'] = $link;
        }

        if (!in_array($entity_id, $this->structure->can_not_delete)
            && $link = fw_anchor($url_prefix . "e/delete/" . $this->structure['#name'] . "/$entity_id", zerophp_lang('Del'))
        ) {
            $item['delete'] = $link;
        }

        return $item;
    }

    function link_tab($tabs) {
        $items = array();
        foreach ($tabs as $tab) {
            $items[] = array(
                'item' => $tab,
            );
        }

        return $items;
    }

    function access($type, $entity_name = '') {
        $entity_name = strtolower($entity_name);

        $permissions = $this->CI->config->item('permissions');
        if (!isset($permissions["Entity_$entity_name"])) {
            $entity_name = 'entity';
        }

        $access = array();
        switch ($type) {
            case 'crud_list':
                if ($entity_name != 'entity' && !in_array("view_" . $entity_name . "_list", $permissions["Entity_$entity_name"])) {
                    $entity_name = 'entity';
                }
                $access[] = "view_" . $entity_name . "_list";
                break;

            case 'crud_create':
                if ($entity_name != 'entity' && !in_array("create_" . $entity_name, $permissions["Entity_$entity_name"])) {
                    $entity_name = 'entity';
                }
                $access[] = "create_" . $entity_name;
                break;

            case 'crud_read':
                if ($entity_name != 'entity' && !in_array("read_any_" . $entity_name, $permissions["Entity_$entity_name"])) {
                    $entity_name = 'entity';
                }
                $access[] = "read_any_" . $entity_name;

                if ($entity_name != 'entity' && !in_array("read_own_" . $entity_name, $permissions["Entity_$entity_name"])) {
                    $entity_name = 'entity';
                }
                $access[] = "read_own_" . $entity_name;
                break;

            case 'crud_preview':
                if ($entity_name != 'entity' && !in_array("preview_any_" . $entity_name, $permissions["Entity_$entity_name"])) {
                    $entity_name = 'entity';
                }
                $access[] = "preview_any_" . $entity_name;

                if ($entity_name != 'entity' && !in_array("preview_own_" . $entity_name, $permissions["Entity_$entity_name"])) {
                    $entity_name = 'entity';
                }
                $access[] = "preview_own_" . $entity_name;
                break;

            case 'crud_update':
                if ($entity_name != 'entity' && !in_array("update_any_" . $entity_name, $permissions["Entity_$entity_name"])) {
                    $entity_name = 'entity';
                }
                $access[] = "update_any_" . $entity_name;

                if ($entity_name != 'entity' && !in_array("update_own_" . $entity_name, $permissions["Entity_$entity_name"])) {
                    $entity_name = 'entity';
                }
                $access[] = "update_own_" . $entity_name;
                break;

            case 'crud_delete':
                if ($entity_name != 'entity' && !in_array("delete_any_" . $entity_name, $permissions["Entity_$entity_name"])) {
                    $entity_name = 'entity';
                }
                $access[] = "delete_any_" . $entity_name;

                if ($entity_name != 'entity' && !in_array("delete_own_" . $entity_name, $permissions["Entity_$entity_name"])) {
                    $entity_name = 'entity';
                }
                $access[] = "delete_own_" . $entity_name;
                break;

            case 'crud_duplicate':
                if ($entity_name != 'entity' && !in_array("duplicate_any_", $permissions["Entity_$entity_name"])) {
                    $entity_name = 'entity';
                }
                $access[] = "duplicate_any_" . $entity_name;

                if ($entity_name != 'entity' && !in_array("duplicate_own_" . $entity_name, $permissions["Entity_$entity_name"])) {
                    $entity_name = 'entity';
                }
                $access[] = "duplicate_own_" . $entity_name;
                break;
        }

        return $access;
    }

    function access_admin($type, $entity_name = '') {
        if ($this->CI->users->access_check('admin')) {
            return $this->access($type, $entity_name);
        }

        $this->CI->theme->messages_add(lang('Forbidden: You do not have permission to access.') . current_url());
        redirect();
    }

    function access_own_entity($path) {
        $entity = Entity::loadEntityObject($path[2]);
        $entity = $this->CI->{$path[2]}->loadEntity($path[3]);

        if (isset($entity->created_by) && $entity->created_by == user_current()) {
            return true;
        }

        return false;
    }
}