<?php 
namespace ZeroPHP\ZeroPHP;

use ZeroPHP\ZeroPHP\Entity;
use ZeroPHP\ZeroPHP\EntityInterface;

class Block extends Entity implements  EntityInterface {
    public function __config() {
        // Get Regions of Theme_default and Theme_admin
        $regions = \Config::get('theme.regions', array());
        asort($regions);

        return array(
            '#id' => 'block_id',
            '#name' => 'block',
            '#class' => 'ZeroPHP\ZeroPHP\Block',
            '#title' => zerophp_lang('Block'),
            '#fields' => array(
                'block_id' => array(
                    '#name' => 'block_id',
                    '#title' => zerophp_lang('ID'),
                    '#type' => 'hidden',
                ),
                'title' => array(
                    '#name' => 'title',
                    '#title' => zerophp_lang('Title'),
                    '#type' => 'text',
                ),
                'cache_type' => array(
                    '#name' => 'cache_type',
                    '#title' => zerophp_lang('Cache type'),
                    '#type' => 'select',
                    '#options' => array(
                        'full' => zerophp_lang('Full cache'),
                        'page' => zerophp_lang('Page cache'),
                    ),
                    '#validate' => 'required',
                ),
                'region' => array(
                    '#name' => 'region',
                    '#title' => zerophp_lang('Region'),
                    '#type' => 'select',
                    '#options' => $regions,
                    '#validate' => 'required',
                ),
                'content' => array(
                    '#name' => 'content',
                    '#title' => zerophp_lang('Content'),
                    '#type' => 'textarea',
                    '#rte_enable' => 1,
                    '#list_hidden' => 1,
                ),
                'class' => array(
                    '#name' => 'class',
                    '#title' => zerophp_lang('Class'),
                    '#type' => 'text',
                    '#list_hidden' => 1,
                ),
                'method' => array(
                    '#name' => 'method',
                    '#title' => zerophp_lang('Method'),
                    '#type' => 'text',
                    '#list_hidden' => 1,
                ),
                'access' => array(
                    '#name' => 'access',
                    '#title' => zerophp_lang('Access'),
                    '#type' => 'text',
                    '#list_hidden' => 1,
                ),
                'weight' => array(
                    '#name' => 'weight',
                    '#title' => zerophp_lang('Weight'),
                    '#type' => 'select',
                    '#options' => form_options_make_weight(),
                    '#validate' => 'required|numeric|between:-99,99',
                    '#fast_edit' => 1,
                ),
                'active' => array(
                    '#name' => 'active',
                    '#title' => zerophp_lang('Active'),
                    '#type' => 'radios',
                    '#options' => array(
                        1 => zerophp_lang('Enable'),
                        0 => zerophp_lang('Disable'),
                    ),
                    '#validate' => 'required|numeric|between:0,1'
                ),
            ),
        );
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

            $class = new $block->class;
            $method = $block->method;
            $block_content = $class->$method($block);

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

        $class = new $block->class;
        $method = $block->access;

        return $class->$method($block);
    }
}