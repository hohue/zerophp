<?php 
namespace ZeroPHP\Article;

use ZeroPHP\ZeroPHP\Entity;

class Article extends Entity {
    function __construct() {
        $this->setStructure(array(
            '#id' => 'article_id',
            '#name' => 'article',
            '#class' => 'ZeroPHP\Article\Article',
            '#title' => zerophp_lang('Article'),
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
                /*'image' => array(
                    '#name' => 'image',
                    '#title' => zerophp_lang('Image'),
                    '#type' => 'file',
                    '#widget' => 'image',
                    '#display_hidden' => 1,
                ),*/
                'content' => array(
                    '#name' => 'content',
                    '#title' => zerophp_lang('Content'),
                    '#type' => 'textarea',
                    //'#rte_enable' => 1,
                    '#display_hidden' => 1,
                ),
                'created_at' => array(
                    '#name' => 'created_at',
                    '#title' => zerophp_lang('Created date'),
                    '#type' => 'text',
                    '#widget' => 'date_timestamp',
                    '#form_hidden' => 1,
                ),
                'updated_at' => array(
                    '#name' => 'updated_at',
                    '#title' => zerophp_lang('Updated date'),
                    '#type' => 'text',
                    '#widget' => 'date_timestamp',
                    '#form_hidden' => 1,
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
            ),
        ));
    }
}