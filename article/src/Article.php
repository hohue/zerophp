<?php 
namespace ZeroPHP\Article;

use ZeroPHP\ZeroPHP\Entity;
use ZeroPHP\ZeroPHP\Form;

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
                'image' => array(
                    '#name' => 'image',
                    '#title' => zerophp_lang('Image'),
                    '#type' => 'file',
                    '#widget' => 'image',
                    '#display_hidden' => true,
                    '#validate' => 'image|mimes:jpeg,png,gif',
                ),
                'content' => array(
                    '#name' => 'content',
                    '#title' => zerophp_lang('Content'),
                    '#type' => 'textarea',
                    '#rte_enable' => true,
                    '#display_hidden' => true,
                ),
                'created_by' => array(
                    '#name' => 'created_by',
                    '#title' => zerophp_lang('Created by'),
                    '#type' => 'text',
                    '#form_hidden' => true,
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

    private function _unsetFormItem(&$form) {
        unset($form['active']);
    }

    function createForm($zerophp) {
        $form = $this->crudCreateForm();
        $this->_unsetFormItem($form);

        unset($form['article_id']);

        $zerophp->response->addContent(Form::build($form));
    }

    function show($zerophp, $article_id){
        $entity = Entity::loadEntityObject('ZeroPHP\Article\Article');
        $article = $entity->loadEntity($article_id);

        if(!isset($article->article_id)) {
            \App::abort(404);
        }

        $zerophp->response->addContent(zerophp_view('article_read', zerophp_object_to_array($article)));
    }

    function updateForm($zerophp, $article_id) {
        $entity = Entity::loadEntityObject('ZeroPHP\Article\Article');
        $form = $entity->crudCreateForm();
        $this->_unsetFormItem($form);

        $article = $entity->loadEntity($article_id);

        $form['article_id']['#value'] = $article_id;
        $form['title']['#value'] = $article->title;
        $form['content']['#value'] = $article->content;

        //zerophp_devel_print($form, $article);

        $zerophp->response->addContent(Form::build($form));
    }

    function showList($zerophp){}

    function deleteForm($zerophp, $article_id) {}
}