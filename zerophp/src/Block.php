<?php 
namespace ZeroPHP\ZeroPHP;

use ZeroPHP\ZeroPHP\Entity;

class Block extends Entity {

    function __construct() {
        // Get Regions of Theme_default and Theme_admin
        $regions = \Config::get('theme.regions', array());
        asort($regions);

        $this->setStructure(array(
            'id' => 'block_id',
            'name' => 'block',
            'title' => zerophp_lang('Block'),
            'fields' => array(
                'block_id' => array(
                    'name' => 'block_id',
                    'title' => zerophp_lang('ID'),
                    'type' => 'hidden',
                ),
                'title' => array(
                    'name' => 'title',
                    'title' => zerophp_lang('Title'),
                    'type' => 'input',
                ),
                'cache_type' => array(
                    'name' => 'cache_type',
                    'title' => zerophp_lang('Cache type'),
                    'type' => 'dropdown_build',
                    'options' => array(
                        'full' => zerophp_lang('Full cache'),
                        'page' => zerophp_lang('Page cache'),
                    ),
                    'validate' => 'required',
                ),
                'region' => array(
                    'name' => 'region',
                    'title' => zerophp_lang('Region'),
                    'type' => 'dropdown_build',
                    'options' => $regions,
                    'validate' => 'required',
                ),
                'content' => array(
                    'name' => 'content',
                    'title' => zerophp_lang('Content'),
                    'type' => 'textarea',
                    'rte_enable' => 1,
                    'display_hidden' => 1,
                ),
                'class' => array(
                    'name' => 'class',
                    'title' => zerophp_lang('Class'),
                    'type' => 'input',
                    'display_hidden' => 1,
                ),
                'method' => array(
                    'name' => 'method',
                    'title' => zerophp_lang('Method'),
                    'type' => 'input',
                    'display_hidden' => 1,
                ),
                'access' => array(
                    'name' => 'access',
                    'title' => zerophp_lang('Access'),
                    'type' => 'input',
                    'display_hidden' => 1,
                ),
                'weight' => array(
                    'name' => 'weight',
                    'title' => zerophp_lang('Weight'),
                    'type' => 'dropdown_build',
                    'options' => form_options_make_weight(),
                    'validate' => 'required|numeric|greater_than[-100]|less_than[100]',
                    'fast_edit' => 1,
                ),
                'active' => array(
                    'name' => 'active',
                    'title' => zerophp_lang('Active'),
                    'type' => 'radio_build',
                    'options' => array(
                        1 => zerophp_lang('Enable'),
                        0 => zerophp_lang('Disable'),
                    ),
                    'validate' => 'required|numeric|greater_than[-1]|less_than[2]'
                ),
            ),
        ));
    }

    function loadEntityAll($attributes = array()) {
        if ($cache = \Cache::get(__METHOD__)) {
            return $cache;
        }

        $blocks_raw = parent::loadEntityAll();

        $blocks = array();
        if (count($blocks_raw)) {
            foreach ($blocks_raw as $key => $block) {
                $blocks[$block->region][$block->block_id] = $block;
            }
        }

        \Cache::forever(__METHOD__, $blocks);
        return $blocks;
    }

    public function run($block) {
        if (self::_checkBlocksVisible($block)) {
            if ($block->cache_type) {
                $cache_name = __METHOD__ . $block->block_id;
                switch ($block->cache_type) {
                    case 'full':
                        $cache_name .= "full";
                        break;

                    case 'page':
                        $cache_name .= "page-" . md5(\URL::current());
                        break;
                }

                $cache = \Cache::get($cache_name);
                if ($cache) {
                    return $cache;
                }
            }

            $class = new $block->library;
            $method = $block->function;
            $block_content = $class->$method($block);
            //$block_content = 'Hello AK';

            if ($block->cache_type) {
                \Cache::put($cache_name, $block_content, ZEROPHP_CACHE_EXPIRE_TIME);
            }

            return $block_content;
        }

        return '';
    }

    private function _checkBlocksVisible($block) {
        if (empty($block->access)) {
            return true;
        }

        $class = new $block->library;
        $method = $block->access;

        return $class->$method($block);
    }
}