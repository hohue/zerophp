<?php 
namespace ZeroPHP\Metadata;

use ZeroPHP\ZeroPHP\Entity;
use ZeroPHP\ZeroPHP\EntityInterface;

class Metadata extends Entity implements EntityInterface {
    function __config() {
        return array(
            '#id' => 'metadata_id',
            '#name' => 'metadata',
            '#class' => '\ZeroPHP\Metadata\Metadata',
            '#title' => zerophp_lang('Metadata'),
            '#fields' => array(
                'metadata_id' => array(
                    '#name' => 'metadata_id',
                    '#title' => zerophp_lang('ID'),
                    '#type' => 'hidden',
                ),
                'path' => array(
                    '#name' => 'path',
                    '#title' => zerophp_lang('Path'),
                    '#type' => 'text',
                ),
                'path_title' => array(
                    '#name' => 'path_title',
                    '#title' => zerophp_lang('Path title'),
                    '#type' => 'text',
                ),
                'keywords' => array(
                    '#name' => 'keywords',
                    '#title' => zerophp_lang('Keywords'),
                    '#type' => 'textarea',
                    '#list_hidden' => 1,
                ),
                'description' => array(
                    '#name' => 'description',
                    '#title' => zerophp_lang('Description'),
                    '#type' => 'textarea',
                    '#list_hidden' => 1,
                ),
                'updated_at' => array(
                    '#name' => 'updated_at',
                    '#title' => zerophp_lang('Updated date'),
                    '#type' => 'text',
                    '#widget' => 'date_timestamp',
                    '#form_hidden' => 1,
                ),
            ),
            '#can_not_delete' => array(1),
        );
    }

    



    function loadEntity_from_path($path, $attributes = array()) {
        $attributes['load_all'] = false;
        $attributes['where']['path'] = $path;
        return reset($this->loadEntityExecutive(null, $attributes));
    }

    function metadata_load() {
        $header = zerophp_get_instance()->response->header_get();
        $keywords = !empty($header['keywords']) ? $header['keywords'] : '';
        $description = !empty($header['description']) ? $header['description'] : '';
        $path_title = zerophp_get_instance()->response->page_title_get();

        // Load specific metadata (for alias url)
        $alias = $this->CI->uri->alias_string();
        $metadata = $this->loadEntity_from_path($alias);

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
            $metadata = $this->loadEntity(1);

            if (!$keywords) {
                $keywords = $metadata->keywords;
            }

            if (!$description) {
                $description = $metadata->description;
            }
        }

        zerophp_get_instance()->response->page_title_set($path_title);
        zerophp_get_instance()->response->header_add('<meta name="keywords" content="' . $keywords . '" />', 'keywords');
        zerophp_get_instance()->response->header_add('<meta name="description" content="' . $description . '" />', 'description');
    }
}