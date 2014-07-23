<?php 
namespace ZeroPHP\Metadata;

use ZeroPHP\ZeroPHP\Entity;

class Metadata extends Entity {
    function __construct() {
        parent::__construct();

        

        $this->CI->lang->load('metadata', config_item('language'));

        $this->setStructure(array(
            'id' => 'metadata_id',
            'name' => 'metadata',
            'title' => zerophp_lang('Metadata'),
            'fields' => array(
                'metadata_id' => array(
                    'name' => 'metadata_id',
                    'title' => zerophp_lang('ID'),
                    'type' => 'hidden',
                ),
                'path' => array(
                    'name' => 'path',
                    'title' => zerophp_lang('Path'),
                    'type' => 'input',
                ),
                'path_title' => array(
                    'name' => 'path_title',
                    'title' => zerophp_lang('Path title'),
                    'type' => 'input',
                ),
                'keywords' => array(
                    'name' => 'keywords',
                    'title' => zerophp_lang('Keywords'),
                    'type' => 'textarea',
                    'display_hidden' => 1,
                ),
                'description' => array(
                    'name' => 'description',
                    'title' => zerophp_lang('Description'),
                    'type' => 'textarea',
                    'display_hidden' => 1,
                ),
                'updated_date' => array(
                    'name' => 'updated_date',
                    'title' => zerophp_lang('Updated date'),
                    'type' => 'input',
                    'widget' => 'date_timestamp',
                    'form_hidden' => 1,
                ),
            ),
            'can_not_delete' => array(1),
        ));
    }

    function entity_load_from_path($path, $attributes = array()) {
        $attributes['load_all'] = false;
        $attributes['where']['path'] = $path;
        return reset($this->entity_load_executive(null, $attributes));
    }

    function metadata_load() {
        $header = $this->CI->theme->header_get();
        $keywords = !empty($header['keywords']) ? $header['keywords'] : '';
        $description = !empty($header['description']) ? $header['description'] : '';
        $path_title = $this->CI->theme->page_title_get();

        // Load specific metadata (for alias url)
        $alias = $this->CI->uri->alias_string();
        $metadata = $this->entity_load_from_path($alias);

        $metadata_load = false;
        if (!empty($metadata->path_title)) {
            $path_title = $metadata->path_title;
        }

        if (!empty($metadata->keywords)) {
            $keywords = $metadata->keywords;
        }

        if (!empty($metadata->description)) {
            $description = $metadata->description;
        }

        //@todo 9 Cho phep them metadata voi path la url thuc
        //$uri = \URL::current();

        // Load metadata default
        if (!$keywords || !$description) {
            $metadata = $this->entity_load(1);

            if (!$keywords) {
                $keywords = $metadata->keywords;
            }

            if (!$description) {
                $description = $metadata->description;
            }
        }

        $this->CI->theme->page_title_set($path_title);
        $this->CI->theme->header_add('<meta name="keywords" content="' . $keywords . '" />', 'keywords');
        $this->CI->theme->header_add('<meta name="description" content="' . $description . '" />', 'description');
    }
}