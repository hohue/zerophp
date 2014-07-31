<?php

namespace ZeroPHP\Article;

use ZeroPHP\ZeroPHP\Entity;
use ZeroPHP\ZeroPHP\Form;

class ArticleController {
    private function _unsetFormItem(&$form) {
        //@todo 4 Viet chuc nang image cho article
        unset($form['created_at'], $form['updated_at'], $form['active']);
    }

    function createForm($zerophp) {
        $entity = Entity::loadEntityObject('ZeroPHP\Article\Article');
        $form = $entity->crudCreateForm();
        $this->_unsetFormItem($form);

        unset($form['article_id']);

        //zerophp_devel_print($form);

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