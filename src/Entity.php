<?php 
namespace ZeroPHP\ZeroPHP;

use ZeroPHP\ZeroPHP\Theme;
use ZeroPHP\ZeroPHP\EntityModel;

class Entity {

    private $structure;

    public function __construct() {
        $this->buildStructure();
    }

    public function setStructure($structure) {
        $this->structure = $structure;
    }

    public function getStructure() {
        return $this->structure;
    }

    public function buildStructure() {
        $cache_name = __METHOD__ . get_called_class();
        //echo $cache_name;
        if ($cache = \Cache::get($cache_name)) {
            $this->setStructure($cache);
        }
        else {
            $structure = $this->__config();

            \Cache::forever($cache_name, $structure);
            $this->setStructure($structure);
        }
    }

    public function loadOptionsAll() {
        $entities = $this->loadEntityAll();

        $result = array();
        foreach ($entities as $value) {
            $result[$value->{$this->structure['#id']}] = isset($value->title) ? $value->title : $value->{$this->structure['#id']};
        }

        return $result;
    }

    public function loadEntityAll($attributes = array()) {
        $attributes['load_all'] = true;
        return $this->loadEntityExecutive(null, $attributes);
    }

    public function loadEntityExecutive($entity_id = 0, $attributes = array()) {
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
            $entities[0] = new \stdClass();
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

    public function loadEntity($entity_id, $attributes = array(), $check_active = false) {
        // entity load default
        $cached = false;
        if (is_numeric($entity_id) && !count($attributes)) {
            $cached = true;
            $cache_name = __CLASS__ . "-Entity-$entity_id-" . $this->structure['#name'];
            if ($cache = \Cache::get($cache_name)) {
                if(!$check_active) {
                    return $cache;
                }
                elseif (!empty($cache->active)) {
                    return $cache;
                }
                else {
                    return array();
                }
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

    public function saveEntity($entity) {
        //zerophp_devel_print($entity);
        $reference = array();
        foreach ($this->structure['#fields'] as $field) {
            // Save Reference fields to temp
            if (isset($field['#reference'])
                && isset($field['#reference']['internal'])
                && ! $field['#reference']['internal']
                && isset($entity->{$field['#name']})
            ) {
                $reference[$field['#name']] = array_filter($entity->{$field['#name']});
                unset($entity->{$field['#name']});
            }
        }

        $update = false;
        if (isset($entity->{$this->structure['#id']}) && $entity->{$this->structure['#id']}) {
            $entity_old = $this->loadEntity($entity->{$this->structure['#id']}, array(
                'check_active' => false,
                'cache' => false,
            ));

            if (!empty($entity_old->{$this->structure['#id']})) {
                $entity_id = EntityModel::updateEntity($entity, $this->structure);

                $cache_name = __CLASS__ . "-Entity-$entity_id-" . $this->structure['#name'];
                \Cache::forget($cache_name);

                $update = true;

                unset($entity->{$this->structure['#id']});
            }
        }

        if (!$update) {
            $entity_id = EntityModel::createEntity($entity, $this->structure);
        }

        // Save reference fields from temp to database
        if (count($reference)) {
            $this->saveEntityReference($reference, $entity_id);
        }

        return $entity_id;
    }

    public function saveEntityReference($reference, $entity_id) {
        EntityModel::saveReference($reference, $entity_id, $this->structure);
    }

    public function crudCreateForm() {
        $form = $this->structure['#fields'];
        $form['#form'] = array();

        foreach ($form as $key => $value) {
            if (!empty($value['#form_hidden'])) {
                unset($form[$key]);
            }
            else {
                if(!empty($value['#default'])) {
                    $form[$key]['#value'] = $value['#default'];
                }
            }

            if (isset($value['#type']) && $value['#type'] == 'file') {
                $form['#form']['files'] = true;

                if ($value['#widget'] == 'image'){
                    $rule = \Config::get('file.rule_image');

                    if (!isset($value['#validate'])) {
                        $form[$key]['#validate'] = $rule;
                    }
                    elseif(!strpos('mimes:', $value['#validate'])) {
                        $form[$key]['#validate'] .= "|$rule";
                    }
                }
            }
        }

        $form['entity'] = array(
            '#name' => 'entity',
            '#type' => 'hidden',
            '#value' => $this->structure['#class'],
            '#disabled' => true,
        );

        $form['#actions']['submit'] = array(
            '#name' => 'submit',
            '#type' => 'submit',
            '#value' => zerophp_lang('Save'),
        );

        $form['#validate'][] = array(
            'class' => $this->structure['#class'],
            'method' => 'crudCreateFormValidate',
        );

        $form['#submit'][] = array(
            'class' => $this->structure['#class'],
            'method' => 'crudCreateFormSubmit',
        );

        if (!empty($this->structure['#links']['list'])) {
            $form['#redirect'] = $this->structure['#links']['list'];
        }

        //zerophp_devel_print($form);

        return $form;
    }

    public function crudCreateFormValidate($form_id, $form, &$form_values) {
        $result = true;
        foreach ($this->structure['#fields'] as $key => $value) {
            // Textarea clean
            if ($value['#type'] == 'textarea' && !empty($form_values[$key])) {
                if (!empty($value['#rte_enable'])) {
                    // Make safe and standard html document
                    require_once ROOT . '/libraries/htmlpurifier/library/HTMLPurifier.auto.php';
                    $config = \HTMLPurifier_Config::createDefault();
                    $purifier = new \HTMLPurifier($config);
                    $form_values[$key] = $purifier->purify($form_values[$key]);

                    //@todo 9 Hack for SEO ---------------------------
                    $text = new \DOMDocument();
                    @$text->loadHTML('<?xml encoding="UTF-8"?>' . $form_values[$key]); //LIBXML_HTML_NOIMPLIED

                    if (zerophp_variable_get('image lazy load', 1)) {
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
                    //END Hack for SEO ---------------------------
                }
                else {
                    $form_values[$key] = strip_tags($form_values[$key]);
                }
            }
            //File upload validate
            elseif ($value['#type'] == 'file' && \Input::hasFile($value['#name']) && !\Input::file($value['#name'])->isValid()) {
                //@todo 1 add error message
                $result = false;
            }
        }

        return $result;
    }

    public function crudCreateFormSubmit($form_id, $form, &$form_values) {
        $entity = new \stdClass();

        // Fetch via structure to skip unexpected fields (alter form another modules)
        foreach ($this->structure['#fields'] as $key => $value) {
            if ($value['#type'] == 'file' && \Input::hasFile($value['#name'])) {
                $file = \Input::file($value['#name']);

                $upload_path = MAIN . \Config::get('file.path');
                $upload = false;
                switch ($value['#widget']) {
                    case 'image':
                        $upload_path .= '/images/';
                        $upload = true;
                        break;

                    //@todo 9 cho phep upload file
                    /*case 'file':
                        $upload_config = $upload_config['file'];
                        $upload_config['upload_path'] = 'files/';
                        $upload = true;
                        break;*/
                }

                if ($upload) {
                    $upload_path .= zerophp_userid();
                    $file_name = zerophp_file_get_filename($file, $upload_path);

                    if ($file->move($upload_path, $file_name)) {
                        $form_values[$key] = str_replace(MAIN, '', $upload_path) ."/$file_name";
                    }
                    else {
                        zerophp_get_instance()->response->addMessage(zerophp_lang('An error has occurred. Can not upload file.'), 'error');
                        unset($form_values[$key]);
                    }
                }
            }

            switch ($key) {
                case 'created_by':
                    if (empty($entity->{$key})) {
                        $entity->{$key} = zerophp_userid();
                    }
                    break;

                case 'updated_by':
                    $entity->{$key} = zerophp_userid();
                    break;

                case 'created_at':
                    if (empty($form_values[$this->structure['#id']])) {
                        $entity->{$key} = date('Y-m-d H:i:s');
                    }
                    break;

                case 'updated_at':
                    $entity->{$key} = $entity->{$key} = date('Y-m-d H:i:s');
                    break;

                default:
                    if (isset($form_values[$key])) {
                        $entity->{$key} = $form_values[$key];
                    }
                    elseif (isset($value['#default']) && !isset($form_values[$this->structure['#id']])) {
                        $entity->{$key} = $value['#default'];
                    }
                    /*elseif (empty($entity->{$this->structure['#id']})) {
                        $entity->{$key} = null;
                    }*/
            }
        }

        $form_values[$this->structure['#id']] = $this->saveEntity($entity);

        if (!isset($form['#success_message'])) {
            $form['#success_message'] = zerophp_lang('Your data was updated successfully.');
        }
    }

    public function crudDeleteForm() {
       
        $form = array();

        $form['notice'] = array(
            '#name' => 'Cancel',
            '#type' => 'markup',
            '#value' => 'do you really want to delete',
        );


        $form['#actions']['submit'] = array(
            '#name' => 'submit',
            '#type' => 'submit',
            '#value' => zerophp_lang('OK'),
        );

        $form['#actions']['Cancel'] = array(
            '#name' => 'Cancel',
            '#type' => 'markup',
            '#value' => '<a href="javascript:history.back()" class="button_gay bg_button">Cancel</a>',
        );


        return $form;
    }

    function crudListForm() {
        $entities = $this->loadEntityAll();
        $form = array();

        //zerophp_devel_print($entities);

        foreach ($entities as $key => $value) {
            foreach ($this->structure['#fields'] as $k => $v) {
                if (!isset($v['#display_hidden']) || !$v['#display_hidden']) {
                    $form['#table'][$key][$k] = array(
                        '#name' => $k,
                        '#type' => 'markup',
                        '#value' => $value->{$k},
                    );
                }
            }
        }
        
        return $form;
    }

    function crudRead($id){
        $entity = $this->loadEntity($id);

        $data = array();
        foreach ($this->structure['#fields'] as $key => $val) {
            if (!isset($val['#display_hidden']) || !$val['#display_hidden']) {
                $data['element'][$key] = array(
                    'title' => $val['#title'],
                    'value' => $entity->$key,
                );
            }
        }

        return zerophp_view('entity_read', $data);
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
}