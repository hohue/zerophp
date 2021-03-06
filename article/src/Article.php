<?php 
namespace ZeroPHP\Article;

use ZeroPHP\ZeroPHP\EntityInterface;
use ZeroPHP\ZeroPHP\Entity;

class Article extends Entity implements EntityInterface {
    function __config() {
        return array(
            '#id' => 'article_id',
            '#name' => 'article',
            '#class' => '\ZeroPHP\Article\Article',
            '#title' => zerophp_lang('Article'),
            '#links' => array(
                'list' => 'admin/article/list',
                'create' => 'admin/article/create',
                'clone' => 'admin/article/%/clone',
                'read' => 'article/%',
                'update' => 'admin/article/%/update',
                'delete' => 'admin/article/%/delete',
            ),
            '#fields' => array(
                'article_id' => array(
                    '#name' => 'article_id',
                    '#title' => zerophp_lang('ID'),
                    '#type' => 'hidden',
                ),
                'title' => array(
                    '#name' => 'title',
                    '#title' => zerophp_lang('Title'),
                    '#type' => 'text',
                ),
                'image' => array(
                    '#name' => 'image',
                    '#title' => zerophp_lang('Image'),
                    '#type' => 'file',
                    '#widget' => 'image',
                    '#list_hidden' => true,
                    '#validate' => 'image|mimes:jpeg,png,gif',
                ),
                'summary' => array(
                    '#name' => 'summary',
                    '#title' => zerophp_lang('Summary'),
                    '#type' => 'textarea',
                    '#list_hidden' => true,
                ),
                'content' => array(
                    '#name' => 'content',
                    '#title' => zerophp_lang('Content'),
                    '#type' => 'textarea',
                    '#rte_enable' => true,
                    '#list_hidden' => true,
                ),
                'active' => array(
                    '#name' => 'active',
                    '#title' => zerophp_lang('Active'),
                    '#type' => 'radios',
                    '#options' => array(
                        1 => zerophp_lang('Enable'),
                        0 => zerophp_lang('Disable'),
                    ),
                    '#validate' => 'required|numeric',
                    '#default' => 1,
                ),
                'created_at' => array(
                    '#name' => 'created_at',
                    '#title' => zerophp_lang('Created date'),
                    '#type' => 'text',
                    '#widget' => 'date_timestamp',
                    '#form_hidden' => true,
                ),
                'updated_at' => array(
                    '#name' => 'updated_at',
                    '#title' => zerophp_lang('Updated date'),
                    '#type' => 'text',
                    '#widget' => 'date_timestamp',
                    '#form_hidden' => true,
                ),
                'created_by' => array(
                    '#name' => 'created_by',
                    '#title' => zerophp_lang('Created by'),
                    '#type' => 'text',
                    '#form_hidden' => true,
                    '#list_hidden' => true,
                ),
                'updated_by' => array(
                    '#name' => 'updated_by',
                    '#title' => zerophp_lang('Updated by'),
                    '#type' => 'text',
                    '#form_hidden' => true,
                    '#list_hidden' => true,
                ),
            ),
        );
    }
}