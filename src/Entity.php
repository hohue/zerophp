<?php 
namespace ZeroPHP\ZeroPHP;

use ZeroPHP\ZeroPHP\EntityModel;
use ZeroPHP\ZeroPHP\Form;
use ZeroPHP\ZeroPHP\Hook;

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

            // Run hook entity_structure_alter
            if ($structure['#name'] != 'hook') {
                $hook = new Hook;
                $hooks = $hook->loadEntityAllByHookType('entity_structure_alter', $structure['#name']);
                if ($hooks) {
                    $hook->run($hooks, $structure);
                }
            }

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
                elseif (!isset($this->structure['#fields']['active']) || !empty($cache->active)) {
                    return $cache;
                }
                else {
                    return array();
                }
            }
        }

        if ($check_active) {
            if (!isset($attributes['where'])) {
                $attributes['where'] = array();
            }
            $attributes['where']['active'] = 1;
        }

        $entity = $this->loadEntityExecutive($entity_id, $attributes);
        $result = reset($entity);

        if ($cached) {
            \Cache::forever($cache_name, $result);
        }
        return $result;
    }

    public function loadEntityAll($attributes = array()) {
        return $this->loadEntityExecutive(null, $attributes);
    }

    public function loadEntityExecutive($entity_id = null, $attributes = array()) {
        // Get from cache
        if (!isset($attributes['cache']) || $attributes['cache']) {
            if (!is_string($entity_id) && !is_null($entity_id)) zerophp_devel_print($entity_id, $attributes);
            $cache_name = __METHOD__ . $entity_id . $this->structure['#name'];
            $cache_name .= serialize($attributes);

            $cache_content = \Cache::get($cache_name);
            if ($cache_content) {
                return $cache_content;
            }
        }

        // Get from database
        $entities = EntityModel::loadEntity($entity_id, $this->structure, $attributes);
        foreach ($entities as $entity_key => $entity) {
            $entities[$entity_key] = $this->buildEntity($entity, $attributes);
        }

        // Set to cache
        if (!isset($attributes['cache']) || $attributes['cache']) {
            \Cache::put($cache_name, $entities, ZEROPHP_CACHE_EXPIRE_TIME);
        }

        return $entities;
    }

    public function buildEntity($entity, $attributes = array()) {
        foreach ($this->structure['#fields'] as $key => $value) {
            if ((!isset($attributes['load_hidden']) || !$attributes['load_hidden'])
                 && (isset($value['#load_hidden']) && $value['#load_hidden']) ) {
                $entity->$key = '';
                continue;
            }

            if (empty($entity->{$this->structure['#id']})) {
                if ($key == $this->structure['#id']) {
                    $entity->$key = 0;
                }
                else {
                    $entity->$key = '';
                }
            }

            if (isset($value['#reference'])
                && isset($value['#reference']['internal'])
                && !$value['#reference']['internal']
            ) {
                $ref = new $value['#reference']['class'];
                $entity->$key = EntityModel::loadReference($key, $entity->{$this->structure['#id']}, $this->structure, $ref->getStructure());
            }
        }

        return $entity;
    }

    public function saveEntity($entity) {
        //zerophp_devel_print($entity);
        $reference = array();
        foreach ($this->structure['#fields'] as $field) {
            // Save Reference fields to temp
            if (isset($field['#reference'])
                && isset($entity->{$field['#name']})
                && isset($field['#reference']['internal'])
                && ! $field['#reference']['internal']
            ) {
                $reference[$field['#name']] = is_array($entity->{$field['#name']}) ? array_filter($entity->{$field['#name']}) : array();
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

    public function deleteEntity($entity_ids) {
        $entity_ids = (array) $entity_ids;

        if (isset($this->structure['#can_not_delete'])) {
            foreach ($entity_ids as $key => $value) {
                if (in_array($value, $this->structure['#can_not_delete'])) {
                    unset($entity_ids[$key]);
                }
            }
        }

        if (count($entity_ids)) {
            EntityModel::deleteEntity($entity_ids, $this->structure);
        }
    }

    public function saveEntityReference($reference, $entity_id) {
        EntityModel::saveReference($reference, $entity_id, $this->structure);
    }

    public function showList($zerophp) {
        // Load from DB with paganition
        $entities = \DB::table($this->structure['#name']);
        EntityModel::buildLoadEntityWhere($entities, null, $this->structure, array());
        EntityModel::buildLoadEntityOrder($entities, $this->structure, array());
        $total = $entities->count();
        $pager_items_per_page = zerophp_variable_get('datatables items per page', 20);
        $pager_page = intval($zerophp->request->query('page'));
        $pager_from = $pager_page > 0 ? ($pager_page - 1) : 0;
        $pager_from = $pager_from * $pager_items_per_page;
        $entities->skip($pager_from)->take($pager_items_per_page);
        $entities->select();

        // Use in datatables callback functions
        zerophp_static('ZeroPHP-Entity-showList', isset($this->structure) ? $this->structure : array());

        // Parse data to datatables
        $data = \Datatables::of($entities);

        // Build columns
        $columns = array();
        foreach ($this->structure['#fields'] as $key => $value) {
            if (empty($value['#list_hidden'])) {
                switch ($key) {
                    case 'active':
                        $data->edit_column('active', function($entity){
                            $structure = zerophp_static('ZeroPHP-Entity-showList');

                            if (!empty($structure['#fields']['active']['#options'][$entity->active])) {
                                return $structure['#fields']['active']['#options'][$entity->active];
                            }

                            return $entity->active;
                        });
                        break;
                }

                $tmp = new \stdClass;
                $tmp->title = $value['#title'];
                $columns[] = $tmp;
            }
            else {
                $data->remove_column($key);
            }
        }

        // Add Operations column
        $tmp = new \stdClass;
        $tmp->title = zerophp_lang('Operations');
        $columns[] = $tmp;
        $data->add_column('operations', function($entity) {
            $structure = zerophp_static('ZeroPHP-Entity-showList');

            $item = array();

            if (!empty($structure['#links']['read']) 
                && (!isset($entity->active) || $entity->active == 1)
            ) {
                $item[] = zerophp_anchor(str_replace('%', $entity->{$structure['#id']}, $structure['#links']['read']), zerophp_lang('View'));
            }

            if (!empty($structure['#links']['preview']) 
                && (isset($entity->active) && $entity->active != 1)
            ) {
                $item[] = zerophp_anchor(str_replace('%', $entity->{$structure['#id']}, $structure['#links']['preview']), zerophp_lang('Preview'));
            } 

            if (!empty($structure['#links']['update'])) {
                $item[] = zerophp_anchor(str_replace('%', $entity->{$structure['#id']}, $structure['#links']['update']), zerophp_lang('Edit'));
            }

            if (!empty($structure['#links']['delete'])) {
                $item[] = zerophp_anchor(str_replace('%', $entity->{$structure['#id']}, $structure['#links']['delete']), zerophp_lang('Del'));
            }

            if (!empty($structure['#links']['clone'])) {
                $item[] = zerophp_anchor(str_replace('%', $entity->{$structure['#id']}, $structure['#links']['clone']), zerophp_lang('Clone'));
            }

            return implode(', ', $item);
        });

        // Save datatables config to JS settings
        $searching = zerophp_variable_get('datatables config searching', 1);
        $ordering = zerophp_variable_get('datatables config ordering', 0);
        $paging = zerophp_variable_get('datatables config paging', 0);
        $info = zerophp_variable_get('datatables config info', 0);
        $data = json_decode($data->make()->getContent());
        $data = array(
            'datatables' => array(
                'data' => $data->aaData,
                'columns' => $columns,
                'searching' => $searching ? true : false,
                'ordering' => $ordering ? true : false,
                'paging' => $paging ? true : false,
                'info' => $info ? true : false,
            ),
        );
        $zerophp->response->addJS($data, 'settings');

        // Return to browser
        $vars = array(
            'pager_items_per_page' => $pager_items_per_page,
            'pager_page' => $pager_page,
            'pager_total' => $total,
            'pager_from' => min($pager_from + 1, $total),
            'pager_to' => min($total, $pager_from + $pager_items_per_page),
        );
        $template = 'entity_list-' . $this->structure['#name'];
        if(!\View::exists($template)) {
            $template = 'entity_list';
        }
        return $zerophp->response->addContent(zerophp_view($template, $vars));
    }

    public function showCreate($zerophp) {
        $items = array(
            array(
                '#item' => $this->structure['#title'] . ' ' . zerophp_lang('create')
            )
        );
        $zerophp->response->setBreadcrumb($items);

        $form = array(
            'class' => $this->structure['#class'],
            'method' => 'showCreateForm',
        );

        $zerophp->response->addContent(Form::build($form));
    }
    
    public function showUpdate($zerophp, $entity_id) {
        $items = array(
            array(
                '#item' => $this->structure['#title'] . ' ' . zerophp_lang('update')
            )
        );
        $zerophp->response->setBreadcrumb($items);

        $form = array(
            'class' => $this->structure['#class'],
            'method' => 'showCreateForm',
        );

        $form_values = $this->loadEntity($entity_id);

        $zerophp->response->addContent(Form::build($form, $form_values));
    }

    public function showClone($zerophp, $entity_id) {
        $items = array(
            array(
                '#item' => $this->structure['#title'] . ' ' . zerophp_lang('clone')
            )
        );
        $zerophp->response->setBreadcrumb($items);

        $form = array(
            'class' => $this->structure['#class'],
            'method' => 'showCloneForm',
        );

        $form_values = $this->loadEntity($entity_id);

        $zerophp->response->addContent(Form::build($form, $form_values));
    }

    public function showCloneForm() {
        $form = $this->showCreateForm();

        unset($form[$this->structure['#id']]);

        return $form;
    }

    public function showCreateForm() {
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
                    $rule = zerophp_variable_get('file image rule');

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
            'method' => 'showCreateFormValidate',
        );

        $form['#submit'][] = array(
            'class' => $this->structure['#class'],
            'method' => 'showCreateFormSubmit',
        );

        if (!empty($this->structure['#links']['list'])) {
            $form['#redirect'] = $this->structure['#links']['list'];
        }

        return $form;
    }

    public function showCreateFormValidate($form_id, $form, &$form_values) {
        $result = true;
        foreach ($this->structure['#fields'] as $key => $value) {
            // Textarea clean
            if ($value['#type'] == 'textarea' && !empty($form_values[$key])) {
                if (!empty($value['#rte_enable'])) {
                    // Make safe and standard html document
                    $form_values[$key] = \Purifier::clean($form_values[$key]);

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

    public function showCreateFormSubmit($form_id, &$form, &$form_values) {
        $entity = new \stdClass();

        // Fetch via structure to skip unexpected fields (alter form another modules)
        foreach ($this->structure['#fields'] as $key => $value) {
            if ($value['#type'] == 'file' && \Input::hasFile($value['#name'])) {
                $file = \Input::file($value['#name']);

                $upload_path = MAIN . zerophp_variable_get('file path', '/files');
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
                    if (empty($entity->{$key}) && zerophp_userid()) {
                        $entity->{$key} = zerophp_userid();
                    }
                    break;

                case 'updated_by':
                    if (zerophp_userid()) {
                        $entity->{$key} = zerophp_userid();
                    }
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
            }
        }

        $form_values[$this->structure['#id']] = $this->saveEntity($entity);

        if (!isset($form['#success_message'])) {
            $form['#success_message'] = zerophp_lang('Your data was updated successfully.');
        }
    }

    public function showRead($zerophp, $id){
        $entity = $this->loadEntity($id, array(), true);

        if (!$entity) {
            \App::abort(404);
        }

        if (!empty($entity->title)) {
            $breadcrumb = array(
                array(
                    '#item' => $entity->title
                )
            );
            $zerophp->response->setBreadcrumb($breadcrumb);
        }

        $this->showReadExecutive($zerophp, $entity);
    }

    public function showPreview($zerophp, $id){
        $entity = $this->loadEntity($id);

        if (!$entity) {
            \App::abort(404);
        }

        if (!empty($entity->title)) {
            $breadcrumb = array(
                array(
                    '#item' => $entity->title
                )
            );
            $zerophp->response->setBreadcrumb($breadcrumb);
        }

        $this->showReadExecutive($zerophp, $entity);
    }

    public function showReadExecutive($zerophp, $entity){
        $data = array();
        $data['entity'] = $entity;
        foreach ($this->structure['#fields'] as $key => $val) {
            if (isset($val['#options_callback'])) {
                $val['#options_callback']['arguments'] = isset($val['#options_callback']['arguments']) ? $val['#options_callback']['arguments'] : array();
                $val['#options'] = call_user_func_array(array(new $val['#options_callback']['class'], $val['#options_callback']['method']), $val['#options_callback']['arguments']);
            }

            if (isset($val['#options'])) {
                $entity->$key = (array) $entity->$key;
                foreach ($entity->$key as $k => $v) {
                    if (isset($val['#options'][$v])) {
                        $entity->{$key}[$k] = $val['#options'][$v];
                    }
                }
                $entity->$key = implode(', ', $entity->$key);
            }

            $data['element'][$key] = array(
                'title' => $val['#title'],
                'value' => $entity->$key,
            );
        }

        $template = 'entity_read-' . $this->structure['#name'];
        if(!\View::exists($template)) {
            $template = 'entity_read';
        }
        $zerophp->response->addContent(zerophp_view($template, $data));
    }

    // Create & Update
    public function showDelete($zerophp, $entity_id) {
        if (!empty($entity->title)) {
            $breadcrumb = array(
                array(
                    '#item' => $this->structure['#title'] . ' ' . zerophp_lang('delete'),
                )
            );
            $zerophp->response->setBreadcrumb($breadcrumb);
        }

        $form = array(
            'class' => $this->structure['#class'],
            'method' => 'showDeleteForm',
        );

        $entity = $this->loadEntity($entity_id);
        $title = isset($entity->title) ? $entity->title : $entity_id;

        $form_values['notice'] = zerophp_lang('Do you really want to delete: :title?', array(':title' => $title));
        $form_values['entity_id'] = $entity_id;

        $zerophp->response->addContent(Form::build($form, $form_values));
    }

    public function showDeleteForm() {
        $form = array();

        $form['notice'] = array(
            '#name' => 'notice',
            '#type' => 'markup',
        );

        $form['entity_id'] = array(
            '#name' => 'entity_id',
            '#type' => 'hidden',
            '#disabled' => true,
        );


        $form['#actions']['submit'] = array(
            '#name' => 'submit',
            '#type' => 'submit',
            '#value' => zerophp_lang('OK'),
        );

        $form['#actions']['cancel'] = array(
            '#name' => 'cancel',
            '#type' => 'markup',
            '#value' => '<a href="javascript:history.back()">'. zerophp_lang('Cancel') .'</a>',
        );

        $form['#submit'] = array(
            array(
                'class' => $this->structure['#class'],
                'method' => 'showDeleteFormSubmit',
            ),
        );

        $form['#success_message'] = zerophp_lang('Your data was deleted successfully.');
        if (!empty($this->structure['#links']['list'])) {
            $form['#redirect'] = $this->structure['#links']['list'];
        }

        return $form;
    }

    function showDeleteFormSubmit($form_id, &$form, &$form_values) {
        $this->deleteEntity($form_values['entity_id']);
    }
}