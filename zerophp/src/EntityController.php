<?php 
class EntityController extends Controller {
    // List all of Controller
    function index($entity_name = '', $page = 1) {
        $this->_view('crud_list', $entity_name, $page);
    }

    function create($entity_name = '') {
        $this->_view('crud_create', $entity_name);
    }

    function read($entity_name = '', $entity_id = '') {
        $this->_view('crud_read', $entity_name, $entity_id);
    }

    function preview($entity_name = '', $entity_id = '') {
        $this->_view('crud_preview', $entity_name, $entity_id);
    }

    function update($entity_name = '', $entity_id = '') {
        $this->_view('crud_update', $entity_name, $entity_id);
    }

    function delete($entity_name = '', $entity_id = '') {
        $this->_view('crud_delete', $entity_name, $entity_id);
    }

    function duplicate($entity_name = '', $entity_id = '') {
        $this->_view('crud_duplicate', $entity_name, $entity_id);
    }

    private function _view($view_type, $entity_name, $entity_id = '') {
        $attributes = array(
            'view_type' => $view_type,
            'entity_name' => $entity_name
        );

        if ($view_type == 'crud_list') {
            $attributes['page'] = $entity_id;
        }
        elseif ($entity_id) {
            $attributes['entity_id'] = $entity_id;
        }

        if (!$this->entity->entity_name_exists($attributes['entity_name'])) {
            redirect(fw_variable_get('url page 404', 'dashboard/e404'));
        }

        $this->load->library($attributes['entity_name']);
        $this->{$attributes['entity_name']}->crud_views($attributes);
    }
}